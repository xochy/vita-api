<?php

namespace Tests\Feature\Auth;

use App\Http\Responses\TokenResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TokenResponseTest extends TestCase
{
    use RefreshDatabase;

    const URL = '/test';
    const DEVICE_NAME = 'Test Device';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Config::set('sanctum.expiration_days', 7);

        // Create some permissions for testing
        Permission::create([
            'name'         => 'read-posts',
            'display_name' => 'Read Posts',
            'action'       => 'read',
            'subject'      => 'posts'
        ]);

        Permission::create([
            'name'         => 'write-posts',
            'display_name' => 'Write Posts',
            'action'       => 'create',
            'subject'      => 'posts'
        ]);

        Permission::create([
            'name'         => 'delete-posts',
            'display_name' => 'Delete Posts',
            'action'       => 'delete',
            'subject'      => 'posts'
        ]);

        $this->user->givePermissionTo(['read-posts', 'write-posts']);
    }

    /** @test */
    public function it_creates_new_token_when_no_existing_token_provided()
    {
        $request = Request::create(self::URL, 'POST', [
            'data' => [
                'attributes' => [
                    'device_name' => self::DEVICE_NAME
                ]
            ]
        ]);

        $response = new TokenResponse($this->user);
        $jsonResponse = $response->toResponse($request);

        $responseData = $jsonResponse->getData(true);

        $this->assertEquals(200, $responseData['status']);
        $this->assertNotNull($responseData['token']);
        $this->assertEquals($this->user->name, $responseData['name']);
        $this->assertEquals($this->user->email, $responseData['email']);
        $this->assertNotNull($responseData['permissions']);
        $this->assertNotNull($responseData['expires_at']);

        // Verify token was created in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'name' => 'Test Device'
        ]);
    }

    /** @test */
    public function it_uses_unknown_device_when_device_name_not_provided()
    {
        $request = Request::create(self::URL, 'POST');

        $response = new TokenResponse($this->user);
        $response->toResponse($request);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'name' => 'Unknown Device'
        ]);
    }

    /** @test */
    public function it_refreshes_existing_token_when_token_provided()
    {
        // Create an existing token
        $existingToken = $this->user->createToken(
            self::DEVICE_NAME,
            ['read-posts'],
            now()->addDays(3)
        );

        $tokenModel = PersonalAccessToken::where(
            'tokenable_id',
            $this->user->id
        )->first();

        $originalExpiresAt = $tokenModel->expires_at;
        $originalLastUsedAt = $tokenModel->last_used_at;

        $request = Request::create(self::URL, 'POST');
        sleep(1); // Ensure different timestamps

        $response = new TokenResponse($this->user, $existingToken->plainTextToken);
        $jsonResponse = $response->toResponse($request);

        $responseData = $jsonResponse->getData(true);

        $this->assertEquals($existingToken->plainTextToken, $responseData['token']);

        // Verify token was refreshed
        $tokenModel->refresh();

        $this->assertTrue(
            $tokenModel->expires_at->isAfter($originalExpiresAt)
        );

        $this->assertTrue(
            $tokenModel->last_used_at->isAfter($originalLastUsedAt ?? now()->subMinute())
        );
    }

    /** @test */
    public function it_includes_user_permissions_in_response()
    {
        $request = Request::create(self::URL, 'POST', [
            'data' => ['attributes' => ['device_name' => self::DEVICE_NAME]]
        ]);

        $response = new TokenResponse($this->user);
        $jsonResponse = $response->toResponse($request);
        $responseData = $jsonResponse->getData(true);

        $this->assertNotNull($responseData['permissions']);

        // Decrypt and verify permissions
        $decryptedPermissions = json_decode(
            decryptPayload(
                $responseData['permissions']
            ),
            true
        );
        $this->assertContains('read-posts', $decryptedPermissions);
        $this->assertContains('write-posts', $decryptedPermissions);
        $this->assertNotContains('delete-posts', $decryptedPermissions);
    }

    /** @test */
    public function it_creates_token_with_correct_expiration()
    {
        Config::set('sanctum.expiration_days', 10);

        $request = Request::create(self::URL, 'POST', [
            'data' => ['attributes' => ['device_name' => self::DEVICE_NAME]]
        ]);

        $response = new TokenResponse($this->user);
        $response->toResponse($request);

        $tokenModel = PersonalAccessToken::where(
            'tokenable_id',
            $this->user->id
        )->first();

        $expectedExpiration = now()->addDays(10);

        $this->assertTrue($tokenModel->expires_at
            ->isSameDay($expectedExpiration));
    }

    /** @test */
    public function it_handles_string_expiration_configuration()
    {
        // Simulate environment variable
        Config::set('sanctum.expiration_days', '5');

        $request = Request::create(self::URL, 'POST', [
            'data' => ['attributes' => ['device_name' => self::DEVICE_NAME]]
        ]);

        $response = new TokenResponse($this->user);
        $response->toResponse($request);

        $tokenModel = PersonalAccessToken::where(
            'tokenable_id',
            $this->user->id
        )->first();

        $expectedExpiration = now()->addDays(5);

        $this->assertTrue($tokenModel->expires_at->isSameDay($expectedExpiration));
    }

    /** @test */
    public function it_includes_expires_at_in_response()
    {
        $request = Request::create(self::URL, 'POST', [
            'data' => ['attributes' => ['device_name' => self::DEVICE_NAME]]
        ]);

        $response = new TokenResponse($this->user);
        $jsonResponse = $response->toResponse($request);
        $responseData = $jsonResponse->getData(true);

        $this->assertArrayHasKey('expires_at', $responseData);
        $this->assertNotNull($responseData['expires_at']);

        // Verify it's a valid ISO string
        $expiresAt = \Carbon\Carbon::parse($responseData['expires_at']);
        $this->assertTrue($expiresAt->isAfter(now()));
    }

    /** @test */
    public function it_does_not_refresh_non_existent_token()
    {
        $request = Request::create(self::URL, 'POST');

        $response = new TokenResponse($this->user, 'non-existent-token');
        $jsonResponse = $response->toResponse($request);

        // Should create a new token instead
        $responseData = $jsonResponse->getData(true);
        $this->assertNotEquals('non-existent-token', $responseData['token']);
        $this->assertNotNull($responseData['token']);
    }

     /** @test */
    public function it_includes_all_required_response_fields()
    {
        $request = Request::create(self::URL, 'POST', [
            'data' => ['attributes' => ['device_name' => self::DEVICE_NAME]]
        ]);

        $response = new TokenResponse($this->user);
        $jsonResponse = $response->toResponse($request);
        $responseData = $jsonResponse->getData(true);

        $expectedFields = ['status', 'token', 'name', 'email', 'permissions', 'expires_at'];

        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $responseData, "Missing field: {$field}");
        }

        $this->assertEquals(200, $responseData['status']);
        $this->assertEquals($this->user->name, $responseData['name']);
        $this->assertEquals($this->user->email, $responseData['email']);
    }
}
