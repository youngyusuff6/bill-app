<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Services\AirtimeService;
use App\Services\SSLWalletEncryptionService; // Ensure this service is correctly implemented
use Illuminate\Support\Facades\DB;
use Exception;

class WalletRepository
{
    protected $airtimeService;
    protected $encryptionService;

    public function __construct(AirtimeService $airtimeService, SSLWalletEncryptionService $encryptionService)
    {
        $this->airtimeService = $airtimeService;
        $this->encryptionService = $encryptionService;
    }

    public function getBalance(User $user)
    {
        // Retrieve wallet
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // Decrypt the wallet's balance using the user's encrypted wallet key
        $wallet->balance = $this->encryptionService->decrypt($wallet->balance);
        // Tokenize the user ID
        $tokenizedUserId = tokenize($user->id);
        $transformedWallet = [
            'user_codec' => $tokenizedUserId,
            'balance' => $wallet->balance,
            'last_updated_at' => MSQL_Timestamp_Pretifier($wallet->updated_at),
        ];

        // Return the transformed response including wallet data, tokenized user ID
        return [
            'wallet' => $transformedWallet
        ];
    }

    public function fundWallet(User $user, float $amount)
    {
        return DB::transaction(function () use ($user, $amount) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            if (!$wallet) {
                // Create a new wallet with initial balance and encrypted wallet key
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'encrypted_wallet_key' => $user->encrypted_wallet_key,
                ]);
            }

            // Decrypt the balance before modifying it
            $wallet->balance = $this->encryptionService->decrypt($wallet->balance);

            // Update the balance
            $wallet->balance += $amount;

            // Re-encrypt the balance before saving
            $wallet->balance = $this->encryptionService->encrypt($wallet->balance);

            $wallet->save();

            // Generate unique transaction ID with prefix "BA" and numbers
            $transactionId = 'BA' . strtoupper(uniqid());
            // Log transaction for funding the wallet
            Transaction::create([
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
                'type' => 'FUND',
                'amount' => $amount,
                'description' => 'Wallet Funding',
                'status' => 'SUCCESS',
            ]);
            // Tokenize the user ID
            $tokenizedUserId = tokenize($user->id);
            //Transform data
            $transformedWallet = [
                'user_codec' => $tokenizedUserId,
                'balance' => $this->encryptionService->decrypt($wallet->balance),
                'transaction_datetime' => MSQL_Timestamp_Pretifier($wallet->created_at),
            ];

            // Return the transformed response including wallet data, tokenized user ID, and transaction details
            return [
                'wallet' => $transformedWallet,
                'txn_id' => $transactionId
            ];
        });
    }

    public function purchaseAirtime(User $user, float $amount, string $phoneNumber, string $provider)
    {
        // Validate provider and phone number
        $supportedProviders = $this->airtimeService->getSupportedProviders();
        if (!in_array(strtoupper($provider), $supportedProviders)) {
            throw new Exception('Invalid provider. Supported providers: ' . implode(', ', $supportedProviders));
        }

        if (!$this->airtimeService->validatePhoneNumberFormat($phoneNumber)) {
            throw new Exception('Invalid phone number format');
        }

        if (!$this->airtimeService->validatePhoneNumberProvider($phoneNumber, $provider)) {
            throw new Exception("Phone number does not match the selected {$provider} provider");
        }

        return DB::transaction(function () use ($user, $amount, $phoneNumber, $provider) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();

            // Decrypt wallet balance before proceeding
            $wallet->balance = $this->encryptionService->decrypt($wallet->balance);

            if ($wallet->balance < $amount) {
                throw new Exception('Insufficient wallet balance');
            }

            $wallet->balance -= $amount;

            // Re-encrypt the wallet balance after the transaction
            $wallet->balance = $this->encryptionService->encrypt($wallet->balance);

            $wallet->save();

            // Generate unique transaction ID with prefix "BA" and numbers
            $transactionId = 'BA' . strtoupper(uniqid());

            // Log transaction for airtime purchase
            return Transaction::create([
                'user_id' => $user->id,
                'type' => 'AIRTIME',
                'amount' => $amount,
                'transaction_id' => $transactionId,
                'description' => 'Airtime Purchase',
                'status' => 'SUCCESS',
                'metadata' => json_encode([
                    'service' => 'airtime',
                    'provider' => strtoupper($provider),
                    'phone_number' => $phoneNumber,
                ])
            ]);
        });
    }
}
