<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\AirtimeService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\WalletRepository;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    protected $walletRepository;
    protected $airtimeService;

    public function __construct(WalletRepository $walletRepository, AirtimeService $airtimeService)
    {
        $this->walletRepository = $walletRepository;
        $this->airtimeService = $airtimeService;
    }

    /**
     * Check wallet balance
     */
    public function checkBalance()
    {
        try {
            $user = Auth::user();
            $wallet = $this->walletRepository->getBalance($user);
            return $this->success('Wallet balance retrieved successfully', $wallet);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Fund wallet
     */

    public function fundWallet(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100|max:9999',
        ], [
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 100.',
            'amount.max' => 'Deposit amount can not exceed 9999, please try again!',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors());
        }

        try {
            /** @var User $user */
            $user = Auth::user(); // Ensure this is a User instance
            $wallet = $this->walletRepository->fundWallet(
                $user,
                $request->input('amount')
            );
            return $this->success('Wallet funded successfully', $wallet, 200);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

        /**
     * Fetch all supported and availabl service providers
     */
    public function getSupportedProviders()
    {
        try {
            $providers = $this->airtimeService->getSupportedProviders();
            return $this->success('Supported Providers Retrieved', $providers);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Purchase airtime
     */

    public function airtimePurchase(Request $request)
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:50|max:5000',
            'phone_number' => 'required|string',
            'provider' => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->fail('Validation failed', $validator->errors());
        }

        try {
            /** @var User $user */
            // User instance
            $user = Auth::user();
            // Tokenize the user ID to generate user_codec
            $userCodec = tokenize($user->id);

            $transaction = $this->walletRepository->purchaseAirtime(
                $user,
                $request->input('amount'),
                $request->input('phone_number'),
                $request->input('provider')
            );

            // Transform transaction data to include user_codec
            $transformedTransaction = $this->transformTransaction($transaction, $userCodec);


            return $this->success('Airtime purchased successfully', $transformedTransaction);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    /**
     * Transform Transaction Data
     */
    private function transformTransaction($transaction, $userCodec)
    {
        return [
            'transaction_id' => $transaction->transaction_id,
            'amount' => $transaction->amount,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'description' => $transaction->description,
            'metadata' => json_decode($transaction->metadata, true),
            'created_at' => MSQL_Timestamp_Pretifier($transaction->created_at),
            'updated_at' => $transaction->updated_at,
            'user_codec' => $userCodec,
        ];
    }
}
