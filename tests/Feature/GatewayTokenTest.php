<?php

namespace Tests\Feature;

use App\Services\GatewayTokenService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Class GatewayTokenTest
 *
 * Tests the NHPR Gateway Token integration including routing, controller responses,
 * token generation HTTP client mocking, caching behavior, and error mapping.
 */
class GatewayTokenTest extends TestCase
{
    /**
     * Set up configuration for tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.nhpr.base_url' => 'https://mock.abdm.gov.in',
            'services.nhpr.client_id' => 'mock-client-id',
            'services.nhpr.client_secret' => 'mock-client-secret',
            'services.nhpr.x_cm_id' => 'sbx',
        ]);

        // Clear cache before each test
        Cache::forget(GatewayTokenService::CACHE_KEY_TOKEN);
        Cache::forget(GatewayTokenService::CACHE_KEY_METADATA);
    }

    /**
     * Test the token management GET route renders correctly.
     */
    public function test_token_page_renders_successfully(): void
    {
        $response = $this->get(route('nhpr.token.show'));

        $response->assertStatus(200);
        $response->assertViewIs('nhpr.token');
        $response->assertSee('NHPR Portal');
        $response->assertSee('Generate Gateway Token');
    }

    /**
     * Test successful token generation API call.
     */
    public function test_token_generation_success(): void
    {
        Http::fake([
            'https://mock.abdm.gov.in/api/hiecm/gateway/v3/sessions' => Http::response([
                'accessToken' => 'mock-generated-access-token-1234567890-xyz',
                'expiresIn' => 3600,
                'refreshToken' => 'mock-refresh-token',
                'tokenType' => 'bearer',
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.token.generate'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'NHPR Gateway Access Token generated successfully!',
                'data' => [
                    'accessToken' => 'mock-generated-access-token-1234567890-xyz',
                    'expiresIn' => 3600,
                    'tokenType' => 'bearer',
                ],
            ]);

        // Assert token was cached
        $this->assertEquals('mock-generated-access-token-1234567890-xyz', Cache::get(GatewayTokenService::CACHE_KEY_TOKEN));
        $this->assertNotNull(Cache::get(GatewayTokenService::CACHE_KEY_METADATA));
    }

    /**
     * Test token service error response handling (401 Unauthorized).
     */
    public function test_token_generation_unauthorized_error(): void
    {
        Http::fake([
            'https://mock.abdm.gov.in/api/hiecm/gateway/v3/sessions' => Http::response([
                'error' => [
                    'code' => '900901',
                    'message' => 'Invalid credentials',
                ],
            ], 401),
        ]);

        $response = $this->postJson(route('nhpr.token.generate'));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonPath('message', 'NHPR Gateway (HTTP 401): Unauthorized. Please check that your Client ID and Client Secret are correct - Invalid credentials.');
    }

    /**
     * Test token caching reuse behavior.
     */
    public function test_token_caching_and_reuse(): void
    {
        Http::fake([
            'https://mock.abdm.gov.in/api/hiecm/gateway/v3/sessions' => Http::response([
                'accessToken' => 'token-cached-once',
                'expiresIn' => 3600,
                'refreshToken' => 'refresh-cached-once',
                'tokenType' => 'bearer',
            ], 200),
        ]);

        $service = app(GatewayTokenService::class);

        // First call should trigger HTTP client request
        $token1 = $service->getValidToken();
        $this->assertEquals('token-cached-once', $token1);

        // Clear HTTP fakes or set them to throw exception to prove HTTP is not hit again
        Http::fake([
            'https://mock.abdm.gov.in/api/hiecm/gateway/v3/sessions' => Http::response([], 500),
        ]);

        // Second call should return cached token without HTTP request
        $token2 = $service->getValidToken();
        $this->assertEquals('token-cached-once', $token2);
    }

    /**
     * Test saving custom gateway credentials to session.
     */
    public function test_save_credentials_success(): void
    {
        $response = $this->postJson(route('nhpr.token.credentials.save'), [
            'client_id' => 'custom-id-999',
            'client_secret' => 'custom-secret-999',
            'base_url' => 'https://custom-base.gov.in',
            'api_url' => 'https://custom-api.gov.in',
            'x_cm_id' => 'custom-sbx',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Credentials saved to session and active token cache cleared.',
            ]);

        $this->assertEquals('custom-id-999', session('nhpr_credential_client_id'));
        $this->assertEquals('custom-secret-999', session('nhpr_credential_client_secret'));
        $this->assertEquals('https://custom-base.gov.in', session('nhpr_credential_base_url'));
        $this->assertEquals('https://custom-api.gov.in', session('nhpr_credential_api_url'));
        $this->assertEquals('custom-sbx', session('nhpr_credential_x_cm_id'));
    }

    /**
     * Test clearing dynamic session credentials.
     */
    public function test_clear_credentials_success(): void
    {
        session([
            'nhpr_credential_client_id' => 'to-be-cleared',
            'nhpr_credential_client_secret' => 'to-be-cleared',
        ]);

        $response = $this->postJson(route('nhpr.token.credentials.clear'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Custom credentials cleared and token cache reset.',
            ]);

        $this->assertNull(session('nhpr_credential_client_id'));
        $this->assertNull(session('nhpr_credential_client_secret'));
    }

    /**
     * Test that token generation uses credentials from session if they are set.
     */
    public function test_token_generation_uses_session_credentials(): void
    {
        session([
            'nhpr_credential_client_id' => 'session-client-id',
            'nhpr_credential_client_secret' => 'session-client-secret',
            'nhpr_credential_base_url' => 'https://session-gateway.abdm.gov.in',
            'nhpr_credential_api_url' => 'https://session-api.abdm.gov.in',
            'nhpr_credential_x_cm_id' => 'session-cm-id',
        ]);

        // Faking the session-based URL endpoint
        Http::fake([
            'https://session-gateway.abdm.gov.in/api/hiecm/gateway/v3/sessions' => Http::response([
                'accessToken' => 'session-generated-token',
                'expiresIn' => 1800,
                'refreshToken' => 'session-refresh-token',
                'tokenType' => 'bearer',
            ], 200),
        ]);

        $response = $this->postJson(route('nhpr.token.generate'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'accessToken' => 'session-generated-token',
                    'expiresIn' => 1800,
                    'tokenType' => 'bearer',
                ],
            ]);

        // Verify HTTP call was made to the session-specified URL with session-specified client ID
        Http::assertSent(function ($request) {
            return $request->url() === 'https://session-gateway.abdm.gov.in/api/hiecm/gateway/v3/sessions'
                && $request['clientId'] === 'session-client-id'
                && $request['clientSecret'] === 'session-client-secret';
        });
    }
}
