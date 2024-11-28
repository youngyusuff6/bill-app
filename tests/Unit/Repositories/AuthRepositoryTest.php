<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\AuthRepository;
use App\Services\SSLWalletEncryptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $authRepository;
    protected $encryptionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->encryptionService = $this->createMock(SSLWalletEncryptionService::class);
        $this->encryptionService->method('generateKeyPair')
            ->willReturn('mock_encrypted_wallet_key');
        $this->encryptionService->method('encrypt')
            ->willReturn('encrypted_balance');

        $this->authRepository = new AuthRepository($this->encryptionService);
    }

    /** @test */
    public function it_validates_registration_data_correctly()
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $validator = $this->authRepository->validateRegistration($validData);
        $this->assertFalse($validator->fails());

        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short'
        ];

        $validator = $this->authRepository->validateRegistration($invalidData);
        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function it_can_register_a_new_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $user = $this->authRepository->register($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($userData['name'], $user->name);
        $this->assertEquals($userData['email'], $user->email);
        $this->assertTrue(Hash::check($userData['password'], $user->password));
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email']
        ]);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 'encrypted_balance'
        ]);
    }

    /** @test */
    public function it_throws_exception_for_invalid_registration_data()
    {
        $this->expectException(\Exception::class);

        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different'
        ];

        $this->authRepository->register($invalidData);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $loginResult = $this->authRepository->login($credentials);

        $this->assertArrayHasKey('user', $loginResult);
        $this->assertArrayHasKey('token', $loginResult);
        $this->assertEquals($user->email, $loginResult['user']['email']);
    }

    /** @test */
    public function it_throws_exception_for_invalid_login_credentials()
    {
        $this->expectException(\Exception::class);

        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ];

        $this->authRepository->login($credentials);
    }

    /** @test */
    public function it_can_logout_user()
    {
        $user = User::factory()->create();
        $user->createToken('test_token');

        $this->assertGreaterThan(0, $user->tokens()->count());

        $this->authRepository->logout($user);

        $this->assertEquals(0, $user->tokens()->count());
    }
}