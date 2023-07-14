<?php

namespace Vuetik\VuetikLaravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Vuetik\VuetikLaravel\Commands\PurgeUnusedImages;

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
            ->hasMigration('create_vuetik_images_table')
            ->hasCommand(PurgeUnusedImages::class);
    }
}
