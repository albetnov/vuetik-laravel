<?php

namespace Vuetik\VuetikLaravel;

use Illuminate\Support\Facades\Blade;
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

    public function boot()
    {
        parent::boot();

        Blade::directive('twitterScript', function () {
            return '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';
        });
    }
}
