<?php

namespace Vuetik\VuetikLaravel\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\Attributes\WithEnv;
use Orchestra\Testbench\TestCase as Orchestra;
use Vuetik\VuetikLaravel\Facades\VuetikLaravel;
use Vuetik\VuetikLaravel\VuetikLaravelServiceProvider;

#[WithEnv('DB_CONNECTION', 'testing')]
class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Vuetik\\VuetikLaravel\\Database\\Factories\\'.class_basename($modelName).'Factory'
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
    }
}
