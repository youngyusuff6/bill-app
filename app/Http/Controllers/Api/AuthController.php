<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\AuthRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(Request $request)
    {
        try {
            $userData = $request->only(['name', 'email', 'password', 'password_confirmation']);

            $user = $this->authRepository->register($userData);

            // Transform the user response
            $responseData = [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                // Tokenize the user ID
                'user_codec' => tokenize($user->id)
            ];
            return $this->success('User registered successfully', $responseData);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);

            $authResult = $this->authRepository->login($credentials);

            return $this->success('Login successful', $authResult);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user();

            // Call the repository method to log out
            $this->authRepository->logout($user);

            return $this->success('Successfully logged out.');
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
