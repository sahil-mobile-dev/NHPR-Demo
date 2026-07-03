<?php

namespace App\Helpers;

use Exception;

/**
 * Class AbdmCryptEngine
 *
 * Implements native Diffie-Hellman (ECDH) key exchange and AES-GCM-256
 * symmetric encryption/decryption as required by the ABDM Milestone 2 standards.
 */
class AbdmCryptEngine
{
    /**
     * Generate an ECDH (prime256v1) Private and Public keypair.
     *
     * @return array Contains 'privateKey' and 'publicKey' PEM strings.
     *
     * @throws Exception If generation fails.
     */
    public static function generateKeypair(): array
    {
        $configFilePath = function_exists('config_path') ? config_path('openssl.cnf') : __DIR__.'/../../config/openssl.cnf';

        $config = [
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ];

        if (file_exists($configFilePath)) {
            $config['config'] = $configFilePath;
        }

        $pkey = openssl_pkey_new($config);
        if ($pkey === false) {
            throw new Exception('OpenSSL ECDH key generation failed: '.openssl_error_string());
        }

        // Export Private Key
        $privateKeyExport = '';
        $exportOpts = [];
        if (file_exists($configFilePath)) {
            $exportOpts['config'] = $configFilePath;
        }

        if (! openssl_pkey_export($pkey, $privateKeyExport, null, $exportOpts)) {
            throw new Exception('Failed to export Private Key: '.openssl_error_string());
        }

        // Export Public Key
        $details = openssl_pkey_get_details($pkey);
        if ($details === false || ! isset($details['key'])) {
            throw new Exception('Failed to retrieve Public Key details.');
        }
        $publicKeyExport = $details['key'];

        return [
            'privateKey' => $privateKeyExport,
            'publicKey' => $publicKeyExport,
        ];
    }

    /**
     * Derive a shared secret between a remote Public key and local Private key.
     *
     * @param  string  $remotePublicKeyPem  The public key PEM of the counterparty.
     * @param  string  $localPrivateKeyPem  The private key PEM of this party.
     * @return string Raw shared secret bytes.
     *
     * @throws Exception If derivation fails.
     */
    public static function deriveSharedSecret(string $remotePublicKeyPem, string $localPrivateKeyPem): string
    {
        $remoteKey = openssl_pkey_get_public($remotePublicKeyPem);
        if ($remoteKey === false) {
            throw new Exception('Invalid Remote Public Key PEM: '.openssl_error_string());
        }

        $localKey = openssl_pkey_get_private($localPrivateKeyPem);
        if ($localKey === false) {
            throw new Exception('Invalid Local Private Key PEM: '.openssl_error_string());
        }

        $sharedSecret = openssl_pkey_derive($remoteKey, $localKey);
        if ($sharedSecret === false) {
            throw new Exception('ECDH secret key derivation failed: '.openssl_error_string());
        }

        return $sharedSecret;
    }

    /**
     * Encrypt plaintext data using AES-GCM-256 with the derived secret.
     *
     * @param  string  $plaintext  The raw data to encrypt.
     * @param  string  $sharedSecret  The derived shared secret bytes.
     * @return array Contains base64 encoded 'ciphertext', 'iv', and 'tag'.
     *
     * @throws Exception If encryption fails.
     */
    public static function encryptPayload(string $plaintext, string $sharedSecret): array
    {
        // Compute 32-byte key from the shared secret (using SHA-256 for KDF)
        $key = hash('sha256', $sharedSecret, true);

        // ABDM requires a 12-byte IV (nonce)
        $iv = openssl_random_pseudo_bytes(12);

        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($ciphertext === false) {
            throw new Exception('AES-GCM encryption failed: '.openssl_error_string());
        }

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
        ];
    }

    /**
     * Decrypt encrypted ciphertext using AES-GCM-256.
     *
     * @param  string  $ciphertextBase64  Base64 encoded ciphertext.
     * @param  string  $sharedSecret  The derived shared secret bytes.
     * @param  string  $ivBase64  Base64 encoded IV (12 bytes).
     * @param  string  $tagBase64  Base64 encoded authentication tag (16 bytes).
     * @return string Decrypted plaintext data.
     *
     * @throws Exception If decryption fails.
     */
    public static function decryptPayload(
        string $ciphertextBase64,
        string $sharedSecret,
        string $ivBase64,
        string $tagBase64
    ): string {
        $key = hash('sha256', $sharedSecret, true);
        $ciphertext = base64_decode($ciphertextBase64);
        $iv = base64_decode($ivBase64);
        $tag = base64_decode($tagBase64);

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new Exception('AES-GCM decryption failed: Authentication tag mismatch or invalid key/IV.');
        }

        return $plaintext;
    }
}
