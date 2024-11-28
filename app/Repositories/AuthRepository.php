<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Services\SSLWalletEncryptionService;
use Exception;

class AuthRepository
{
    protected $encryptionService;

    public function __construct(SSLWalletEncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function validateRegistration(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
    }


    public function register(array $data)
    {
        $validator = $this->validateRegistration($data);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        // Generate a secure wallet encryption key pair
        $encryptedWalletKey = $this->encryptionService->generateKeyPair();

        // Create the user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            // Store encrypted wallet key
            'encrypted_wallet_key' => $encryptedWalletKey, 
        ]);

        // Encrypt the initial wallet balance (0) using the generated key
        $encryptedBalance = $this->encryptionService->encrypt('0');

        // Create a new wallet for the user with the encrypted balance
        $user->wallet()->create([
            // Encrypted balance (0)
            'balance' => $encryptedBalance
        ]);


        return $user;
    }
    public function login(array $credentials)
    {
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new Exception('Invalid credentials');
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;


        return [
            'user' => [
                'user_codec' => tokenize($user->id),
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token
        ];
    }

    public function logout($user)
    {
        $user->tokens()->delete();
    }
}
