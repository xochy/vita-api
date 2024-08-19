<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Packages for microservices

Laravel JSON:API [Docs](https://laraveljsonapi.io/docs/3.0/getting-started/).

### JSON:API settings

#### To start, we'll need to install the Laravel JSON:API package into our application, via Composer. Run the following commands:

```
composer require laravel-json-api/laravel
composer require --dev laravel-json-api/testing
php artisan install:api
```

1. Creating models.
```
php artisan make:model Post -a
```

2. Define fields in migration file and fillable values to the model. After this, make sure to specify the defined values in factory file and seeder configuration.

3. Run the following command to create the server.
```
php artisan jsonapi:server v1
```
- This creates a new file in your application: app/JsonApi/V1/Server.php
- It's worth noting at this point that the $baseUri property is set to /api/v1. This means all the HTTP requests we send to our API will start with http://localhost/api/v1/.

4. There's one thing we need to do at this point: we need to tell Laravel JSON:API that we have a v1 server. To do that, we need to edit our config/jsonapi.php. so that this configuration looks like this:
```
'servers' => [
-//  'v1' => \App\JsonApi\V1\Server::class,
+    'v1' => \App\JsonApi\V1\Server::class,
 ],
```

#### Creating the Schema

5. To create the schema, run the following command.
```
php artisan jsonapi:schema posts
```
- This creates a new file, app/JsonApi/V1/Posts/PostSchema.php.
- Our new PostSchema class defines the posts resource, which is the JSON:API representation of the Post model - notice how that is defined on the the static $model property of the class.

6. Tell our JSON:API server that the schema exists. To do this, we update the allSchemas() method in our app/JsonApi/Server.php file. Update that to look like this:
```
 protected function allSchemas(): array
 {
     return [
-        // @TODO
+        Posts\PostSchema::class,
     ];
 }
```

#### Disabling Authorization

- If you do not want a specific JSON:API resource to be authorized, then you can override the authorizable method on the JSON:API schema.
```
namespace App\JsonApi\V1\Posts;

use LaravelJsonApi\Eloquent\Schema;

class PostSchema extends Schema
{

    // ...

    /**
     * Determine if the resource is authorizable.
     *
     * @return bool
     */
    public function authorizable(): bool
    {
        return false;
    }
}
```

- If you do not want our authorization logic to run for an entire server, then you can override the authorizable method on the server.
```
namespace App\JsonApi\V1;

use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{
    // ...

    /**
     * Determine if the server is authorizable.
     *
     * @return bool
     */
    public function authorizable(): bool
    {
        return false;
    }
}
```

#### Schema fields

The fields() method on the schema defines the attributes and relationships that our resource has. Notice the created file has a few standard fields in it already: the ID field for the resource, and the createdAt and updatedAt dates that are standard on an Eloquent model.

7. Add the fields to the schema fields function.
```
public function fields(): array
{
    return [
        ID::make(),
        Str::make('content'),
        DateTime::make('createdAt')->sortable()->readOnly(),
+       DateTime::make('publishedAt')->sortable(),
+       Str::make('slug'),
+       Str::make('title')->sortable(),
        DateTime::make('updatedAt')->sortable()->readOnly(),
    ];
}
```

#### Routing

After continue, make sure to add <code>api: __DIR__ . '/../routes/api.php',</code> to the <code>bootstrap/app.php</code> file in <code>withRouting</code> method.

8. To add our JSON:API server's routes, we will use the JsonApiRoute facade. Update the routes/api.php file to look like this.
```
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Route;
+use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
+use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
+use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;

 Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
     return $request->user();
 });
+
+JsonApiRoute::server('v1')->prefix('v1')->resources(function (ResourceRegistrar $server) {
+    $server->resource('posts', JsonApiController::class)->readOnly();
+});
```

#### Validation

9. When receiving the request to create a resource, one thing our server will need to do is validate the JSON that the client has sent. Generate the request class by running the following command.

```
php artisan jsonapi:request posts
```

#### Authentication (optional)

10. Laravel JSON:API uses Laravel's policy implementation to authorise requests to the API. This means for our posts resource we need to create a PostPolicy. You can do this using the following Laravel command.
```
php artisan make:policy PostPolicy --model Post
```

## Testing

If the purpose is using TDD for develop, is recommended to make this configuration.

1. Publish stubs.
```
php artisan stub:publish
```

2. Add this code to the test.stub file.
```
<?php

namespace {{ namespace }};

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class {{ class }} extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            // TODO: add seed permissions model class here
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    #[Test]
    public function it_can_test()
    {
        //
    }
}
```

3. Edit TestCase.php file adding MakesJsonApiRequests trait.
```
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LaravelJsonApi\Testing\MakesJsonApiRequests;

abstract class TestCase extends BaseTestCase
{
    use MakesJsonApiRequests;
}

```
