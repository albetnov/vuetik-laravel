<?php

namespace Vuetik\VuetikLaravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vuetik\VuetikLaravel\Commands\VuetikLaravelCommand;

class VuetikLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('vuetik-laravel')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_vuetik-laravel_table')
            ->hasCommand(VuetikLaravelCommand::class);
    }
}
