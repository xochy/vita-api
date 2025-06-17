<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    use RefreshDatabase;

    const DEVICE_NAME = 'Test Device';
    const EXPIRED_DEVICE_NAME = 'Expired Device';

    protected User $user;
    protected TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = new TokenService();
        $this->user = User::factory()->create();

        // Set default token expiration for tests
        Config::set('sanctum.expiration_days', 7);
    }

    /** @test */
    public function it_can_create_a_token_for_user()
    {
        $deviceName = self::DEVICE_NAME;
        $abilities = ['read', 'write'];

        $token = $this->tokenService->createToken(
            $this->user,
            $deviceName,
            $abilities
        );

        $this->assertNotNull($token->plainTextToken);

        $this->assertDatabaseHas(
            'personal_access_tokens',
            [
                'tokenable_id' => $this->user->id,
                'name' => $deviceName,
            ]
        );

        $tokenModel = PersonalAccessToken::where(
            'tokenable_id',
            $this->user->id
        )->first();

        $this->assertEquals($abilities, $tokenModel->abilities);
        $this->assertNotNull($tokenModel->expires_at);
        $this->assertTrue($tokenModel->expires_at->isAfter(now()));
    }

    /** @test */
    public function it_revokes_existing_tokens_when_creating_new_token_for_same_device()
    {
        $deviceName = self::DEVICE_NAME;

        // Create first token
        $firstToken = $this->tokenService->createToken(
            $this->user,
            $deviceName
        );

        $this->assertDatabaseCount(
            'personal_access_tokens',
            1
        );

        // Create second token for same device
        $secondToken = $this->tokenService->createToken(
            $this->user,
            $deviceName
        );

        // Should still have only one token (old one deleted)
        $this->assertDatabaseCount(
            'personal_access_tokens',
            1
        );
        $this->assertNotEquals(
            $firstToken->plainTextToken,
            $secondToken->plainTextToken
        );
    }

    /** @test */
    public function it_can_refresh_an_existing_token()
    {
        $token = $this->tokenService->createToken(
            $this->user,
            self::DEVICE_NAME
        );
        $tokenModel = PersonalAccessToken::where(
            'tokenable_id',
            $this->user->id
        )->first();

        $originalExpiresAt = $tokenModel->expires_at;

        // Wait a moment to ensure different timestamps
        sleep(1);

        $result = $this->tokenService->refreshToken($token->plainTextToken);

        $this->assertTrue($result);
        $tokenModel->refresh();

        $this->assertTrue($tokenModel->expires_at->isAfter($originalExpiresAt));
        $this->assertNotNull($tokenModel->last_used_at);
    }

    /** @test */
    public function it_cannot_refresh_expired_token()
    {
        $token = $this->tokenService->createToken(
            $this->user,
            self::DEVICE_NAME
        );

        $tokenModel = PersonalAccessToken::where(
            'tokenable_id',
            $this->user->id
        )->first();

        // Manually expire the token
        $tokenModel->update(['expires_at' => now()->subDay()]);

        $result = $this->tokenService->refreshToken($token->plainTextToken);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_cannot_refresh_non_existent_token()
    {
        $result = $this->tokenService->refreshToken('non-existent-token');
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_revoke_a_specific_token()
    {
        $token = $this->tokenService->createToken(
            $this->user,
            self::DEVICE_NAME
        );

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $result = $this->tokenService->revokeToken($token->plainTextToken);

        $this->assertTrue($result);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /** @test */
    public function it_returns_false_when_revoking_non_existent_token()
    {
        $result = $this->tokenService->revokeToken('non-existent-token');
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_revoke_all_user_tokens()
    {
        // Create multiple tokens
        $this->tokenService->createToken($this->user, 'Device 1');
        $this->tokenService->createToken($this->user, 'Device 2');
        $this->tokenService->createToken($this->user, 'Device 3');

        $this->assertDatabaseCount('personal_access_tokens', 3);

        $this->tokenService->revokeAllUserTokens($this->user);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /** @test */
    public function it_can_cleanup_expired_tokens()
    {
        // Create some tokens
        $this->tokenService->createToken(
            $this->user,
            'Valid Device'
        );

        $this->tokenService->createToken(
            $this->user,
            self::EXPIRED_DEVICE_NAME
        );

        // Manually expire one token
        PersonalAccessToken::where('name', self::EXPIRED_DEVICE_NAME)
            ->update(['expires_at' => now()->subDay()]);

        $this->assertDatabaseCount('personal_access_tokens', 2);

        $deletedCount = $this->tokenService->cleanupExpiredTokens();

        $this->assertEquals(1, $deletedCount);

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->assertDatabaseHas(
            'personal_access_tokens',
            [
                'name' => 'Valid Device'
            ]
        );

        $this->assertDatabaseMissing(
            'personal_access_tokens',
            [
                'name' => self::EXPIRED_DEVICE_NAME
            ]
        );
    }

    /** @test */
    public function it_can_count_expired_tokens()
    {
        // Create tokens
        $this->tokenService->createToken(
            $this->user,
            'Valid Device 1'
        );

        $this->tokenService->createToken(
            $this->user,
            'Valid Device 2'
        );

        $this->tokenService->createToken(
            $this->user,
            'Expired Device 1'
        );

        $this->tokenService->createToken(
            $this->user,
            'Expired Device 2'
        );

        // Expire two tokens
        PersonalAccessToken::whereIn(
            'name',
            [
                'Expired Device 1',
                'Expired Device 2'
            ]
        )->update([
                    'expires_at' => now()->subDay()
                ]);

        $expiredCount = $this->tokenService->countExpiredTokens();

        $this->assertEquals(2, $expiredCount);
    }

    /** @test */
    public function it_can_get_token_info()
    {
        $deviceName = 'Test Device';
        $abilities = ['read', 'write'];
        $token = $this->tokenService->createToken(
            $this->user,
            $deviceName,
            $abilities
        );

        $tokenInfo = $this->tokenService->getTokenInfo($token->plainTextToken);

        $this->assertIsArray($tokenInfo);
        $this->assertEquals($deviceName, $tokenInfo['name']);
        $this->assertEquals($abilities, $tokenInfo['abilities']);
        $this->assertFalse($tokenInfo['is_expired']);
        $this->assertArrayHasKey('id', $tokenInfo);
        $this->assertArrayHasKey('last_used_at', $tokenInfo);
        $this->assertArrayHasKey('expires_at', $tokenInfo);
        $this->assertArrayHasKey('created_at', $tokenInfo);
    }

    /** @test */
    public function it_returns_null_for_non_existent_token_info()
    {
        $tokenInfo = $this->tokenService->getTokenInfo('non-existent-token');
        $this->assertNull($tokenInfo);
    }

    /** @test */
    public function it_correctly_identifies_expired_tokens_in_token_info()
    {
        $token = $this->tokenService->createToken(
            $this->user,
            self::DEVICE_NAME
        );

        $tokenModel = PersonalAccessToken::where(
            'tokenable_id',
            $this->user->id
        )->first();

        $tokenModel->update(['expires_at' => now()->subDay()]);

        $tokenInfo = $this->tokenService->getTokenInfo($token->plainTextToken);

        $this->assertTrue($tokenInfo['is_expired']);
    }

    /** @test */
    public function it_respects_custom_expiration_days_configuration()
    {
        Config::set('sanctum.expiration_days', 14);

        $this->tokenService->createToken(
            $this->user,
            self::DEVICE_NAME
        );

        $tokenModel = PersonalAccessToken::where(
            'tokenable_id',
            $this->user->id
        )->first();

        $expectedExpiration = now()->addDays(14);
        $this->assertTrue($tokenModel->expires_at
            ->isSameDay($expectedExpiration));
    }

    /** @test */
    public function it_handles_string_expiration_days_configuration()
    {
        // Simulate environment variable (which comes as string)
        Config::set('sanctum.expiration_days', '10');

        $this->tokenService->createToken(
            $this->user,
            self::DEVICE_NAME
        );

        $tokenModel = PersonalAccessToken::where(
            'tokenable_id',
            $this->user->id
        )->first();

        $expectedExpiration = now()->addDays(10);

        $this->assertTrue($tokenModel->expires_at
            ->isSameDay($expectedExpiration));
    }
}
