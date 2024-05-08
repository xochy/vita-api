<?php

namespace Tests\Feature\Frequencies;

use App\Models\Frequency;
use App\Models\User;
use Database\Seeders\permissionsSeeders\FrequenciesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateFrequenciesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'frequencies';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DESCRIPTION = 'description';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(FrequenciesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_create_frequencies()
    {
        $frequency = array_filter(Frequency::factory()->raw());

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $frequency
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $frequency);
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_frequencies()
    {
        $frequency = array_filter(Frequency::factory()->raw());

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $frequency
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME        => $frequency[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_DESCRIPTION => $frequency[self::MODEL_ATTRIBUTE_DESCRIPTION],
            ]
        );
    }

    /** @test */
    public function frequency_name_is_required()
    {
        $frequency = Frequency::factory()->raw(
            [
                'name' => ''
            ]
        );

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $frequency
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/name'],
                'detail' => 'The name field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $frequency);
    }

    /** @test */
    public function frequency_name_must_be_unique()
    {
        $frequency = Frequency::factory()->create();

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => array_filter(Frequency::factory()->raw(
                [
                    'name' => $frequency->name
                ]
            ))
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/name'],
                'detail' => 'The name has already been taken.'
            ]
        );

        $this->assertDatabaseCount(self::MODEL_PLURAL_NAME, 1);
    }

    /** @test */
    public function frequency_description_is_required()
    {
        $frequency = Frequency::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_DESCRIPTION => ''
            ]
        );

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $frequency
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/description'],
                'detail' => 'The description field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $frequency);
    }

    /** @test */
    public function frequency_description_must_be_a_string()
    {
        $frequency = Frequency::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_DESCRIPTION => 123
            ]
        );

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $frequency
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/description'],
                'detail' => 'The description field must be a string.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $frequency);
    }
}
