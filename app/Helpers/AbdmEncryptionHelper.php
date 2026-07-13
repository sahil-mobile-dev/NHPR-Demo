<?php

namespace App\Helpers;

use App\Services\GatewayTokenService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class AbdmEncryptionHelper
 *
 * Provides cryptographic utilities for encrypting sensitive user data (Aadhaar, Mobile, etc.)
 * using the ABDM Gateway Public Certificate under the RSA/ECB/PKCS1Padding scheme.
 */
class AbdmEncryptionHelper
{
    /**
     * Cache key for storing the retrieved public certificate.
     */
    public const CERTIFICATE_CACHE_KEY = 'abdm_public_cert';

    /**
     * Fetch the ABDM Public Certificate, caching it for 24 hours.
     *
     * @return string Public certificate in PEM format.
     *
     * @throws Exception If certificate retrieval fails.
     */
    public static function getPublicCertificate(): string
    {
        $cachedCert = Cache::get(self::CERTIFICATE_CACHE_KEY);
        if ($cachedCert) {
            return $cachedCert;
        }

        $apiUrl = config('services.nhpr.api_url');
        if (empty($apiUrl)) {
            throw new Exception('ABDM Encryption: api_url is not configured in config/services.php.');
        }

        $endpoint = rtrim($apiUrl, '/').'/v4/int/api/v1/auth/cert';

        try {
            Log::info('ABDM Encryption: Fetching public certificate from gateway', ['url' => $endpoint]);

            $tokenService = app(GatewayTokenService::class);
            $token = $tokenService->getValidToken();

            $headers = [];
            if ($token) {
                $headers['Authorization'] = 'Bearer '.$token;
            }
            $headers['REQUEST-ID'] = (string) Str::uuid();
            $headers['TIMESTAMP'] = now()->toIso8601String();
            $headers['X-CM-ID'] = config('services.nhpr.x_cm_id', 'sbx');

            $response = Http::when(! config('services.nhpr.verify_ssl'), fn ($q) => $q->withoutVerifying())
                ->withHeaders($headers)
                ->timeout(10)
                ->retry(3, 100)
                ->get($endpoint);

            if ($response->successful()) {
                $certContent = $response->body();

                // Formulate PEM certificate format if not already present
                if (! str_contains($certContent, '-----BEGIN CERTIFICATE-----')) {
                    $certContent = "-----BEGIN CERTIFICATE-----\n".wordwrap(trim($certContent), 64, "\n", true)."\n-----END CERTIFICATE-----";
                }

                // Cache for 24 hours (86400 seconds)
                Cache::put(self::CERTIFICATE_CACHE_KEY, $certContent, 86400);

                return $certContent;
            }

            throw new Exception("ABDM Encryption cert fetch returned status {$response->status()}.");
        } catch (Exception $e) {
            Log::error('ABDM Encryption: Failed to fetch public certificate: '.$e->getMessage());

            // For local development fallback if the endpoint is unreachable
            $fallbackCertPath = storage_path('certs/abdm_sandbox.pem');
            if (file_exists($fallbackCertPath)) {
                return file_get_contents($fallbackCertPath);
            }

            throw new Exception('ABDM Encryption Failure: Unable to fetch gateway encryption certificate: '.$e->getMessage());
        }
    }

    /**
     * Encrypt sensitive data using the ABDM Public Certificate.
     *
     * @param  string  $data  The raw string data to encrypt.
     * @return string Base64 encoded encrypted cipher.
     *
     * @throws Exception If RSA public encryption fails.
     */
    public static function encrypt(string $data): string
    {
        try {
            $pemCert = self::getPublicCertificate();

            $publicKey = openssl_pkey_get_public($pemCert);
            if ($publicKey === false) {
                throw new Exception('ABDM Encryption: Invalid public key structure.');
            }

            $encryptedData = '';
            // Encrypt using RSA with PKCS1 Padding (corresponds to RSA/ECB/PKCS1Padding)
            $success = openssl_public_encrypt($data, $encryptedData, $publicKey, OPENSSL_PKCS1_PADDING);

            if (! $success) {
                throw new Exception('ABDM Encryption: OpenSSL encryption failed.');
            }

            return base64_encode($encryptedData);

        } catch (Exception $e) {
            Log::error('ABDM Encryption failed: '.$e->getMessage());
            throw new Exception('ABDM Encryption: Data encryption failed: '.$e->getMessage());
        }
    }

    /**
     * Encrypt sensitive data using RSA with OAEP padding (corresponds to RSA/ECB/OAEPWithSHA-1AndMGF1Padding).
     * Required for ABHA v3 APIs.
     *
     * @param  string  $data  The raw string data to encrypt.
     * @return string Base64 encoded encrypted cipher.
     *
     * @throws Exception If RSA public encryption fails.
     */
    public static function encryptOaep(string $data): string
    {
        try {
            $pemCert = self::getPublicCertificate();

            $publicKey = openssl_pkey_get_public($pemCert);
            if ($publicKey === false) {
                throw new Exception('ABDM Encryption: Invalid public key structure.');
            }

            $encryptedData = '';
            // Encrypt using RSA with OAEP Padding (corresponds to RSA/ECB/OAEPWithSHA-1AndMGF1Padding)
            $success = openssl_public_encrypt($data, $encryptedData, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);

            if (! $success) {
                throw new Exception('ABDM Encryption: OpenSSL OAEP encryption failed.');
            }

            return base64_encode($encryptedData);

        } catch (Exception $e) {
            Log::error('ABDM OAEP Encryption failed: '.$e->getMessage());
            throw new Exception('ABDM Encryption: OAEP Data encryption failed: '.$e->getMessage());
        }
    }
}
