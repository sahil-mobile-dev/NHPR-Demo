<?php

namespace App\Services;

use App\Helpers\AbdmCryptEngine;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class FideliusService
 *
 * Wraps the Fidelius CLI Java utility for ABDM ECDH (Curve25519) and AES-GCM-256 decryption.
 * Gracefully falls back to PHP native AbdmCryptEngine when the Java CLI is unavailable.
 */
class FideliusService
{
    /**
     * Generate key material required for HIU request.
     *
     * @return array Contains 'privateKey', 'publicKey', and 'nonce' (base64 encoded).
     */
    public function generateKeys(): array
    {
        $jarPath = $this->getJarPath();

        if ($jarPath && file_exists($jarPath)) {
            try {
                $output = [];
                $returnVar = 0;
                $command = 'java -jar '.escapeshellarg($jarPath).' generate';
                exec($command, $output, $returnVar);

                if ($returnVar === 0 && ! empty($output)) {
                    $json = json_decode(implode('', $output), true);
                    if (isset($json['privateKey'], $json['publicKey'], $json['nonce'])) {
                        Log::info('Fidelius CLI: Generated keys using JAR.');

                        return [
                            'privateKey' => $json['privateKey'],
                            'publicKey' => $json['publicKey'],
                            'nonce' => $json['nonce'],
                            'engine' => 'fidelius-cli',
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::warning('Fidelius CLI generate failed: '.$e->getMessage().'. Falling back to native CryptEngine.');
            }
        }

        // Fallback to PHP CryptEngine
        Log::info('Fidelius CLI: Falling back to PHP CryptEngine for key generation.');
        $keypair = AbdmCryptEngine::generateKeypair();

        return [
            'privateKey' => $keypair['privateKey'],
            'publicKey' => $keypair['publicKey'],
            'nonce' => base64_encode(random_bytes(12)),
            'engine' => 'crypt-engine',
        ];
    }

    /**
     * Decrypt the encrypted health record package.
     */
    public function decrypt(
        string $ciphertext,
        string $senderPublicKey,
        string $senderNonce,
        string $receiverPrivateKey,
        string $receiverNonce
    ): string {
        $jarPath = $this->getJarPath();

        if ($jarPath && file_exists($jarPath)) {
            try {
                // Fidelius CLI syntax for decryption:
                // java -jar fidelius-cli.jar decrypt <ciphertext> <receiverPrivateKey> <senderPublicKey> <receiverNonce> <senderNonce>
                $output = [];
                $returnVar = 0;
                $command = 'java -jar '.escapeshellarg($jarPath).' decrypt '.
                    escapeshellarg($ciphertext).' '.
                    escapeshellarg($receiverPrivateKey).' '.
                    escapeshellarg($senderPublicKey).' '.
                    escapeshellarg($receiverNonce).' '.
                    escapeshellarg($senderNonce);

                exec($command, $output, $returnVar);

                if ($returnVar === 0 && ! empty($output)) {
                    $result = implode('', $output);
                    // Check if it is valid JSON
                    if (json_decode($result) !== null || ! empty($result)) {
                        Log::info('Fidelius CLI: Decrypted successfully using JAR.');

                        return $result;
                    }
                }
            } catch (Exception $e) {
                Log::warning('Fidelius CLI decrypt failed: '.$e->getMessage().'. Falling back to native CryptEngine.');
            }
        }

        // Fallback to PHP CryptEngine
        Log::info('Fidelius CLI: Falling back to PHP CryptEngine for decryption.');

        try {
            // Derive shared secret
            $sharedSecret = AbdmCryptEngine::deriveSharedSecret($senderPublicKey, $receiverPrivateKey);

            // Decrypt AES-GCM
            // If the sender sent the data using AbdmCryptEngine, it will be encrypted using standard AES-GCM.
            // receiverNonce corresponds to the IV.
            return AbdmCryptEngine::decryptPayload(
                $ciphertext,
                $sharedSecret,
                $receiverNonce,
                $senderNonce // tag
            );
        } catch (Exception $e) {
            // In case of IV/tag parameter differences (Fidelius sometimes uses nonce xor or custom KDF),
            // let's handle decryption with a fallback or throw.
            Log::error('Fidelius PHP CryptEngine fallback failed: '.$e->getMessage());
            throw new Exception('Decryption failed. Ensure valid key materials and nonces. Error: '.$e->getMessage());
        }
    }

    /**
     * Get path to Fidelius JAR file from config or standard storage location.
     */
    protected function getJarPath(): ?string
    {
        $path = config('services.nhpr.fidelius_jar_path');
        if ($path) {
            return $path;
        }

        $defaultPath = storage_path('bin/fidelius-cli.jar');
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }

        return null;
    }
}
