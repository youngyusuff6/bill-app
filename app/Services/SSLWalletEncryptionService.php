<?php

namespace App\Services;

class SSLWalletEncryptionService
{
    protected $method = 'aes-128-ctr'; // default cipher method
    private $key;

    public function __construct($key = null)
    {
        // Initialize the key if not provided
        if (!$key) {
            $key = php_uname(); // default encryption key if none supplied
        }

        if (ctype_print($key)) {
            // convert ASCII keys to binary format
            $this->key = openssl_digest($key, 'SHA256', TRUE);
        } else {
            $this->key = $key;
        }
    }

    protected function iv_bytes()
    {
        return openssl_cipher_iv_length($this->method);
    }

    public function encrypt(string $data): string
    {
        // Generate a random IV (Initialization Vector)
        $iv = openssl_random_pseudo_bytes($this->iv_bytes());

        // Encrypt the data using OpenSSL
        $encrypted = openssl_encrypt($data, $this->method, $this->key, 0, $iv);

        // Return the IV and encrypted data concatenated together in base64
        return bin2hex($iv) . openssl_encrypt($data, $this->method, $this->key, 0, $iv);
    }

    public function decrypt(string $encryptedData): string
    {
        // Extract the IV and encrypted data from the input string
        $ivlen = 2 * $this->iv_bytes();
        if (preg_match("/^(.{" . $ivlen . "})(.+)$/", $encryptedData, $regs)) {
            list(, $iv, $crypted_string) = $regs;
            
            // Ensure the IV is valid
            if (ctype_xdigit($iv) && strlen($iv) % 2 == 0) {
                // Decrypt the data using the extracted IV
                return openssl_decrypt($crypted_string, $this->method, $this->key, 0, hex2bin($iv));
            }
        }

        // Return false if decryption failed
        return false;
    }

    public function generateKeyPair(): string
    {
        // Generate a random 64-character key
        return bin2hex(random_bytes(32));
    }
}
