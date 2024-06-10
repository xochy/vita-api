<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateUsersWithOptionalValuesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'users';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_EMAIL = 'email';
    const MODEL_ATTRIBUTE_PASSWORD = 'password';
    const MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION = 'password_confirmation';
    const MODEL_ATTRIBUTE_NAME_VALUE = 'John Doe';
    const MODEL_ATTRIBUTE_EMAIL_VALUE = 'test.email@mail.com';

    const MODEL_POINTER_FOR_AGE = '/data/attributes/age';
    const MODEL_POINTER_FOR_GENDER = '/data/attributes/gender';
    const MODEL_POINTER_FOR_SYSTEM = '/data/attributes/system';
    const MODEL_POINTER_FOR_WEIGHT = '/data/attributes/weight';
    const MODEL_POINTER_FOR_HEIGHT = '/data/attributes/height';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(UsersPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function user_age_must_be_an_integer_value()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'age' => '15',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'es')
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_AGE],
                'detail' => 'El campo edad debe ser de tipo entero.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_age_must_be_between_10_and_80()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'age' => 9,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_AGE],
                'detail' => 'The age field must be between 10 and 80.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_gender_must_be_a_string()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'gender' => 1,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertErrors(
            422,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_GENDER],
                    'detail' => 'The gender field must be a string.',
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_GENDER],
                    'detail' => 'The selected gender is invalid.',
                ]
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_gender_must_be_a_gender_enum_type()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'gender' => 'invalid',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_GENDER],
                'detail' => 'The selected gender is invalid.',
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_measurement_system_must_be_a_string()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'system' => 1,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertErrors(
            422,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_SYSTEM],
                    'detail' => 'The measurement system field must be a string.',
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_SYSTEM],
                    'detail' => 'The selected measurement system is invalid.',
                ]
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_measurement_system_must_be_a_measurement_system_enum_type()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'system' => 'invalid',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_SYSTEM],
                'detail' => 'The selected measurement system is invalid.',
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_weight_must_be_a_double_value()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'weight' => '15.58',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'es')
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_WEIGHT],
                'detail' => 'El campo peso debe ser de tipo doble.',
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_weight_must_be_between_10_and_200_when_system_is_metric()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'system' => 'metric',
                'weight' => 9.99,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'es')
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_WEIGHT],
                'detail' => 'El campo peso debe estar entre 10 y 200.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_weight_must_be_between_50_and_400_when_system_is_imperial()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'system' => 'imperial',
                'weight' => 49.99,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'en')
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_WEIGHT],
                'detail' => 'The weight field must be between 50 and 400.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_weight_must_have_exactly_two_decimals()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'weight' => 15.185,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'es')
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_WEIGHT],
                'detail' => 'El campo peso debe tener exactamente 2 decimales.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_weight_can_be_an_integer_value()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'weight' => 65,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        $response->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME => $user[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_EMAIL => $user[self::MODEL_ATTRIBUTE_EMAIL],
                'weight' => 65,
            ]
        );
    }

    /** @test */
    public function user_height_must_be_a_double_value()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'height' => '1.75',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'es')
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_HEIGHT],
                'detail' => 'El campo altura debe ser de tipo doble.',
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_height_must_be_between_0_5_and_2_5_when_system_is_metric()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'system' => 'metric',
                'height' => 0.49,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'es')
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_HEIGHT],
                'detail' => 'El campo altura debe estar entre 0.5 y 2.5.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_height_must_be_between_1_5_and_8_5_when_system_is_imperial()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'system' => 'imperial',
                'height' => 1.49,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'en')
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_HEIGHT],
                'detail' => 'The height field must be between 1.5 and 8.5.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_height_must_have_exactly_two_decimals_when_is_float()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'system' => 'metric',
                'height' => 1.758,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'es')
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_HEIGHT],
                'detail' => 'El campo altura debe tener exactamente 2 decimales.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_height_can_be_an_integer_value()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
                'height' => 5,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        $response->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME => $user[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_EMAIL => $user[self::MODEL_ATTRIBUTE_EMAIL],
                'height' => 5,
            ]
        );
    }
}
