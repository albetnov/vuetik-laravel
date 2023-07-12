# VueTik Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vuetik/vuetik-laravel.svg?style=flat-square)](https://packagist.org/packages/vuetik/vuetik-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/albetnov/vuetik-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/albetnov/vuetik-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/albetnov/vuetik-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/albetnov/vuetik-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vuetik/vuetik-laravel.svg?style=flat-square)](https://packagist.org/packages/vuetik/vuetik-laravel)

Server Side Integration and Transformers of [Vue-Tik](https://github.com/albetnov/vue-tik) for Laravel.

> Still work in progress (DEVELOPMENT)

## Installation

You can install the package via composer:

```bash
composer require vuetik/vuetik-laravel
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="vuetik-laravel-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="vuetik-laravel-config"
```

This is the contents of the published config file:

```php
return [
    'max_upload_size' => 2048,
    'storage' => [
        'disk' => 'local',
        'path' => storage_path('public/vuetik-laravel'),
    ],
    'table' => 'vuetik_images',
    'image_vendor_route' => '/img',
    'glide' => [
        'enable' => true,
        'sign_key' => env('APP_KEY'),
        'img_modifiers' => [],
    ],
];
```

## Usage

Vuetik Laravel provide integration for the image upload routes and content parsing. For registering the image upload
routes with VueTik requirement you can easily call:

```php
public function boot() {
    Vuetik\VuetikLaravel\VuetikLaravel::routes();
}
```

to any Service Provider.

For parsing content, it can easily be done via:

```php
use Vuetik\VuetikLaravel\Facades\VuetikLaravel;

VuetikLaravel::parse($htmlOrDecodedJson);

// or if you use JSON

VuetikLaravel::parseJson($jsonString);
```

> Even though the HTML Parser is working, it possible the end result is kind of inaccurate. Therefore using
> JSON approach is advised.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Albet Novendo](https://github.com/albetnov)
- [Tiptap PHP](https://github.com/ueberdosis/tiptap-php)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
