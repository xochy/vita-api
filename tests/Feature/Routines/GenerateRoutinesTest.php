<?php

namespace Tests\Feature\Routines;

use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\EquipmentSeeder;
use Database\Seeders\MuscleSeeder;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\VariationSeeder;
use Database\Seeders\WorkoutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GenerateRoutinesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_PROPOSE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.propose';
    const MODEL_GENERATE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.generate';

    const MODEL_POINTER_FOR_GENDER = '/data/attributes/gender';
    const MODEL_POINTER_FOR_AGE = '/data/attributes/age';
    const MODEL_POINTER_FOR_GOAL = '/data/attributes/goal';
    const MODEL_POINTER_FOR_LEVEL = '/data/attributes/level';
    const MODEL_POINTER_FOR_EQUIPMENT_IDS = '/data/attributes/equipment_ids';
    const MODEL_POINTER_FOR_MUSCLE_IDS = '/data/attributes/muscle_ids';
    const MODEL_POINTER_FOR_WORKOUT_IDS = '/data/attributes/workout_ids';
    const MODEL_POINTER_FOR_NAME = '/data/attributes/name';

    const MODEL_ROUTINE_NAME = 'Proposed Routine';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(RoutinesPermissionsSeeder::class);

            $this->seed(MuscleSeeder::class);
            $this->seed(CategorySeeder::class);
            $this->seed(EquipmentSeeder::class);

            $this->seed(WorkoutSeeder::class);
            $this->seed(VariationSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();
    }

    /** @test */
    public function can_propose_routines()
    {
        $data = $this->getRoutineProposalData();

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $workoutIds = $response->json('data.*.id');

        $this->assertNotEmpty($workoutIds);
    }

    /** @test */
    public function level_is_required_for_proposing_routines()
    {
        $data = $this->getRoutineProposalData();
        unset($data['level']);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_LEVEL],
                'detail' => __(
                    'validation.required',
                    [
                        'attribute' => __('validation.attributes.level')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function level_must_be_valid_for_proposing_routines()
    {
        $data = $this->getRoutineProposalData([
            'level' => 123,
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_LEVEL],
                    'detail' => __(
                        'validation.string',
                        [
                            'attribute' => __('validation.attributes.level')
                        ]
                    ),
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_LEVEL],
                    'detail' => __(
                        'validation.in',
                        [
                            'attribute' => __('validation.attributes.level')
                        ]
                    ),
                ]
            ]
        );
    }

    /** @test */
    public function equipment_ids_are_required_for_proposing_routines()
    {
        $data = $this->getRoutineProposalData();
        unset($data['equipment_ids']);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertHasError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_EQUIPMENT_IDS],
                'detail' => __(
                    'validation.required',
                    [
                        'attribute' => __('validation.attributes.equipment_ids')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function equipment_ids_count_must_be_less_than_or_equal_to_20()
    {
        $data = $this->getRoutineProposalData([
            'equipment_ids' => range(1, 21),
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertHasError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_EQUIPMENT_IDS],
                'detail' => __(
                    'validation.max.array',
                    [
                        'attribute' => __('validation.attributes.equipment_ids'),
                        'max' => 20
                    ]
                )
            ]
        );
    }

    /** @test */
    public function equipment_ids_must_be_valid_for_proposing_routines()
    {
        $data = $this->getRoutineProposalData([
            'equipment_ids' => [999, 1000],
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_EQUIPMENT_IDS . '/0'],
                    'detail' => __(
                        'validation.exists',
                        [
                            'attribute' => __('validation.attributes.equipment_id')
                        ]
                    ),
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_EQUIPMENT_IDS . '/1'],
                    'detail' => __(
                        'validation.exists',
                        [
                            'attribute' => __('validation.attributes.equipment_id')
                        ]
                    ),
                ]
            ]
        );
    }

    /** @test */
    public function equipment_ids_must_not_be_equals_for_proposing_routines()
    {
        $data = $this->getRoutineProposalData([
            'equipment_ids' => [1, 1],
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_EQUIPMENT_IDS . '/0'],
                    'detail' => __(
                        'validation.distinct',
                        [
                            'attribute' => __('validation.attributes.equipment_id')
                        ]
                    ),
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_EQUIPMENT_IDS . '/1'],
                    'detail' => __(
                        'validation.distinct',
                        [
                            'attribute' => __('validation.attributes.equipment_id')
                        ]
                    ),
                ]
            ]
        );
    }

    /** @test */
    public function muscle_ids_are_required_for_proposing_routines()
    {
        $data = $this->getRoutineProposalData();
        unset($data['muscle_ids']);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_MUSCLE_IDS],
                'detail' => __(
                    'validation.required',
                    [
                        'attribute' => __('validation.attributes.muscle_ids')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function muscle_ids_count_must_be_less_than_or_equal_to_15()
    {
        $data = $this->getRoutineProposalData([
            'muscle_ids' => range(1, 16),
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_MUSCLE_IDS],
                    'detail' => __(
                        'validation.max.array',
                        [
                            'attribute' => __('validation.attributes.muscle_ids'),
                            'max' => 15
                        ]
                    )
                ]
            ]
        );
    }

    /** @test */
    public function muscle_ids_must_be_valid_for_proposing_routines()
    {
        $data = $this->getRoutineProposalData([
            'muscle_ids' => [999, 1000],
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_MUSCLE_IDS . '/0'],
                    'detail' => __(
                        'validation.exists',
                        [
                            'attribute' => __('validation.attributes.muscle_id')
                        ]
                    ),
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_MUSCLE_IDS . '/1'],
                    'detail' => __(
                        'validation.exists',
                        [
                            'attribute' => __('validation.attributes.muscle_id')
                        ]
                    ),
                ]
            ]
        );
    }

    /** @test */
    public function muscle_ids_must_not_be_equals_for_proposing_routines()
    {
        $data = $this->getRoutineProposalData([
            'muscle_ids' => [1, 1],
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_MUSCLE_IDS . '/0'],
                    'detail' => __(
                        'validation.distinct',
                        [
                            'attribute' => __('validation.attributes.muscle_id')
                        ]
                    ),
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_MUSCLE_IDS . '/1'],
                    'detail' => __(
                        'validation.distinct',
                        [
                            'attribute' => __('validation.attributes.muscle_id')
                        ]
                    ),
                ]
            ]
        );
    }

    /** @test */
    public function can_generate_routines()
    {
        $data = $this->getRoutineProposalData();

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $workoutIds = $response->json('data.*.id');

        $data = [
            'name' => 'Generated Routine 1',
            'user_id' => $this->user->id,
            'gender' => 'male',
            'age' => 25,
            'goal' => 'lose weight',
            'workout_ids' => $workoutIds,
        ];

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $routine = $response->json('data');
        $this->assertNotEmpty($routine);

        $this->assertDatabaseHas('routines', [
            'name' => 'Generated Routine 1',
        ]);

        foreach ($workoutIds as $workoutId) {
            $this->assertDatabaseHas('routine_workout', [
                'routine_id' => $routine['id'],
                'workout_id' => $workoutId,
            ]);
        }
    }

    /** @test */
    public function name_is_required_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData();
        unset($data['name']);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_NAME],
                'detail' => __(
                    'validation.required',
                    [
                        'attribute' => __('validation.attributes.name')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function name_must_be_string_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'name' => 123,
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_NAME],
                'detail' => __(
                    'validation.string',
                    [
                        'attribute' => __('validation.attributes.name')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function name_must_not_exceed_100_characters_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'name' => str_repeat('a', 101),
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_NAME],
                'detail' => __(
                    'validation.max.string',
                    [
                        'attribute' => __('validation.attributes.name'),
                        'max' => 100
                    ]
                )
            ]
        );
    }

    /** @test */
    public function user_id_is_required_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData();
        unset($data['user_id']);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => '/data/attributes/user_id'],
                'detail' => __(
                    'validation.required',
                    [
                        'attribute' => __('validation.attributes.user_id')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function user_id_must_exist_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'user_id' => 9999, // Non-existent user
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => '/data/attributes/user_id'],
                'detail' => __(
                    'validation.exists',
                    [
                        'attribute' => __('validation.attributes.user_id')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function gender_is_required_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData();
        unset($data['gender']);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_GENDER],
                'detail' => __(
                    'validation.required',
                    [
                        'attribute' => __('validation.attributes.gender')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function gender_must_be_valid_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'gender' => 123
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_GENDER],
                    'detail' => __(
                        'validation.string',
                        [
                            'attribute' => __('validation.attributes.gender')
                        ]
                    ),
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_GENDER],
                    'detail' => __(
                        'validation.in',
                        [
                            'attribute' => __('validation.attributes.gender')
                        ]
                    ),
                ]
            ]
        );
    }

    /** @test */
    public function age_is_required_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData();
        unset($data['age']);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_AGE],
                'detail' => __(
                    'validation.required',
                    [
                        'attribute' => __('validation.attributes.age')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function age_must_be_valid_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'age' => 'invalid_age',
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_AGE],
                    'detail' => __(
                        'validation.integer',
                        [
                            'attribute' => __('validation.attributes.age')
                        ]
                    )
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_AGE],
                    'detail' => __(
                        'validation.min.numeric',
                        [
                            'attribute' => __('validation.attributes.age'),
                            'min' => 13
                        ]
                    )
                ]
            ]
        );
    }

    /** @test */
    public function age_must_be_greater_than_or_equal_to_13_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'age' => 12,
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_AGE],
                'detail' => __('validation.min.numeric', [
                    'attribute' => __(
                        'validation.attributes.age'
                    ),
                    'min' => 13
                ])
            ]
        );
    }

    /** @test */
    public function age_must_be_less_than_or_equal_to_100_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'age' => 101,
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_AGE],
                'detail' => __('validation.max.numeric', [
                    'attribute' => __(
                        'validation.attributes.age'
                    ),
                    'max' => 100
                ])
            ]
        );
    }

    /** @test */
    public function goal_is_required_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData();
        unset($data['goal']);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_GOAL],
                'detail' => __(
                    'validation.required',
                    [
                        'attribute' => __('validation.attributes.goal')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function goal_must_be_valid_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'goal' => 123,
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_GOAL],
                    'detail' => __(
                        'validation.string',
                        [
                            'attribute' => __('validation.attributes.goal')
                        ]
                    ),
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_GOAL],
                    'detail' => __(
                        'validation.in',
                        [
                            'attribute' => __('validation.attributes.goal')
                        ]
                    ),
                ]
            ]
        );
    }

    /** @test */
    public function workout_ids_are_required_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData();
        unset($data['workout_ids']);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_WORKOUT_IDS],
                'detail' => __(
                    'validation.required',
                    [
                        'attribute' => __('validation.attributes.workout_ids')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function workout_ids_must_be_an_array_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'workout_ids' => 'not_an_array', // Invalid type
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertHasError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_WORKOUT_IDS],
                'detail' => __(
                    'validation.array',
                    [
                        'attribute' => __('validation.attributes.workout_ids')
                    ]
                )
            ]
        );
    }

    /** @test */
    public function workout_ids_count_must_be_less_than_or_equal_to_10_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'workout_ids' => range(1, 11), // More than 10 IDs
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertHasError(
            400,
            [
                'source' => ['pointer' => self::MODEL_POINTER_FOR_WORKOUT_IDS],
                'detail' => __(
                    'validation.max.array',
                    [
                        'attribute' => __('validation.attributes.workout_ids'),
                        'max' => 10
                    ]
                )
            ]
        );
    }

    /** @test */
    public function workout_ids_must_be_valid_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'workout_ids' => [999, 1000], // Non-existent workout IDs
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_WORKOUT_IDS . '/0'],
                    'detail' => __(
                        'validation.exists',
                        [
                            'attribute' => __('validation.attributes.workout_id')
                        ]
                    ),
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_WORKOUT_IDS . '/1'],
                    'detail' => __(
                        'validation.exists',
                        [
                            'attribute' => __('validation.attributes.workout_id')
                        ]
                    ),
                ]
            ]
        );
    }

    /** @test */
    public function workout_ids_must_not_be_equals_for_generating_routines()
    {
        $data = $this->getRoutineGenerationData([
            'workout_ids' => [1, 1], // Duplicate workout IDs
        ]);

        $response = $this->jsonApi()
            ->withHeaders([
                'locale' => 'es',
                'Authorization' => $this->token
            ])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $response->assertErrors(
            400,
            [
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_WORKOUT_IDS . '/0'],
                    'detail' => __(
                        'validation.distinct',
                        [
                            'attribute' => __('validation.attributes.workout_id')
                        ]
                    ),
                ],
                [
                    'source' => ['pointer' => self::MODEL_POINTER_FOR_WORKOUT_IDS . '/1'],
                    'detail' => __(
                        'validation.distinct',
                        [
                            'attribute' => __('validation.attributes.workout_id')
                        ]
                    ),
                ]
            ]
        );
    }

    /**
     * Get default routine proposal data with optional overrides.
     *
     *  @param array $overrides
     *  @return array
     */
    protected function getRoutineProposalData(array $overrides = []): array
    {
        $default = [
            'level' => 'beginner',
            'equipment_ids' => [1, 2],
            'muscle_ids' => [1, 2, 3],
        ];

        return array_merge($default, $overrides);
    }

    protected function getRoutineGenerationData(array $overrides = []): array
    {
        $default = [
            'name' => self::MODEL_ROUTINE_NAME,
            'user_id' => $this->user->id,
            'gender' => 'male',
            'age' => 25,
            'goal' => 'lose weight',
            'workout_ids' => [1, 2],
        ];

        return array_merge($default, $overrides);
    }
}
