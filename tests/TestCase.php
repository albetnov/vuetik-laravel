<?php

namespace Vuetik\VuetikLaravel\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Attributes\WithConfig;
use Orchestra\Testbench\Attributes\WithEnv;
use Orchestra\Testbench\TestCase as Orchestra;
use Vuetik\VuetikLaravel\Facades\VuetikLaravel;
use Vuetik\VuetikLaravel\VuetikLaravelServiceProvider;

use function Orchestra\Testbench\artisan;

#[WithEnv('DB_CONNECTION', 'testing')]
// #[WithConfig('database.default', 'testing')]
class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Vuetik\\VuetikLaravel\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            VuetikLaravelServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'VuetikLaravel' => VuetikLaravel::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.debug', true);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // $migration = include __DIR__ . '/../database/migrations/create_vuetik_images_table.php';
        //
        // $migration->up();

        // artisan($this, 'migrate', ['--database' => 'testing']);
        //
        // $this->beforeApplicationDestroyed(
        //     fn () => artisan($this, 'migrate:rollback', ['--database' => 'testing'])
        // );
    }
}
