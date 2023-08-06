# VueTik Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vuetik/vuetik-laravel.svg?style=flat-square)](https://packagist.org/packages/vuetik/vuetik-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/albetnov/vuetik-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/albetnov/vuetik-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/albetnov/vuetik-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/albetnov/vuetik-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vuetik/vuetik-laravel.svg?style=flat-square)](https://packagist.org/packages/vuetik/vuetik-laravel)

Server Side Integration and Transformers of [Vue-Tik](https://github.com/albetnov/vue-tik) for Laravel.

## Migration Guide

The migration from 1.x to 2.x is documented in [Migration Guide Wiki](https://github.com/albetnov/vuetik-laravel/wiki/Migrating-from-1.x)

## Features

- Image Upload Routing
- Image Cleanup Cron Job
- Automatic base64 image separation and validation to desired object storage
- Twitter Content Pre-Hydration
- Glide Integration
- HTML Sanitation

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
[
    'max_upload_size' => 2048,
    'storage' => [
        'disk' => 'local',
        'path' => 'images/',
    ],
    'table' => 'vuetik_images',
    'image_vendor_route' => '/img',
    'glide' => [
        'enable' => true,
        'sign_key' => env('APP_KEY'),
        'img_modifiers' => [],
    ],
    'purge_after' => '-2 days',
    'base64_to_storage' => [
        'enable' => true,
        'save_format' => 'png',
        'quality' => 100
    ],
];
```

## Usage

Vuetik Laravel provides integration for the image upload routes and content parsing.
For registering the image upload
routes with VueTik requirement, you can easily call:

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

> Even though the HTML Parser is working, it is possible the end result is kind of inaccurate.
> Therefore, using JSON approach is advised.

### Available Options

Both the `parse` and `parseJson` API accepts array `$options` which have the following content:

| Key                    	 | Description                                                                                                                                          	 | Type     	 | Default Value                                            	 |
|--------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------|------------|------------------------------------------------------------|
| `twitter.throwOnFail`  	 | Set whatever twitter content parsing should throw an exception when fail or just  return `Failed Fetching Twitter` paragraph.                        	 | `bool`   	 | `true`                                                  	  |
| `image.base64_to_disk` 	 | Determine if `base64` based image should be moved to disk or not (allow cron cleanup to work either)                                                 	 | `bool`   	 | `config('vuetik-laravel.base64_to_storage.enable')`      	 |
| `image.throwOnFail`    	 | Set whatever `base64` based image parsing should throw an exception when Vuetik Laravel failed validating it. (ignored if `base64_to_disk` is false) 	 | `bool`   	 | `true`                                                  	  |
| `image.saveFormat`     	 | Set which format to encode for `base64` image when saved to the disk  (ignored if `base64_to_disk` is false)                                         	 | `string` 	 | `config('vuetik-laravel.base64_to_storage.save_format')` 	 |
| `image.disk`           	 | Set which driver to use for Vuetik Laravel to save image into.                                                                                       	 | `string` 	 | `config('vuetik-laravel.storage.disk)`                   	 |
| `image.quality`        	 | Set image quality for `base64` decoding                                                                                                              	 | `int`    	 | `config('vuetik-laravel.base64_to_storage.quality')`     	 |
| `image.autoSave`         | Set the parsed image status to `A`                                                                                                                     | `bool`     | `true`                                                     |

Example Passing:

```php
use Vuetik\VuetikLaravel\VuetikLaravel;

VuetikLaravel::parseJson($json, [
    'twitter' => [
        'throwOnFail' => true
    ],
    'image' => [
        'throwOnFail' => true
    ]
])
```

## Image Management

Each uploaded image is stored with `temporary` state and by default Vuetik Laravel has predefined image management. 
This behavior can be turned off by setting the `image.autoSave` to `false` (`true` by default).

With that option enabled, Vuetik Laravel will take care of the entire process of image management without needing of
manual interactions.

In case you want to perform manual saving, Vuetik Laravel already provided you with helper functions

```php
use Vuetik\VuetikLaravel\ImageManager;

// quick store preuploads
// if you have a payload containing predefined id (which usally comes from editor.storage.ids)
// you can use storePreuploads to store the images without Vuetik Laravel to parse it. This could be  
// faster, but it's currently unable to save extra attributes (width, height, etc)
ImageManager::storePreuploads(["id1", "id2"]);

// Store all the images parsed from Vuetik Laravel
// This method returns an array containing all the data of the stored image.
// It's expected ContentFactory as the argument (return value of VuetikLaravel::parse() or VuetikLaravel::parseJson())
ImageManager::store($content);

// This method will return a glide url
// based on VuetikImages model with defined base url and additional attributes.
ImageManager::getGlideUrl($imgModel, $vendorUrl, $props);
```

> You usually will the use `Store` API only though.

### Managing failed image parsing

It is possible; for the image parsing fails to occur, for example invalid string `base64` and so on.
Vuetik Laravel already provided you with `image.throwOnFail` which will throw an exception and interrupt
the entire parsing process.

This guide will cover if the options are set to false.

- for Base64 images, Vuetik simply ignored them and the resulting image stays as it is.
- for Preupload Images, Vuetik will add a class `vuetik__failed__img` to the parsed content and leave at is. 
You are encouraged to add a style to highlight the failed images.

> Ultimately, I think it best to leave throwOnFail as true though.

## Cleaning up unused image

To clean up an unused image, Vuetik Laravel has provided you with `purge:unused-images` commands which allow you to
delete an image created after `vuetik-laravel.purge_after` with `status` of `P`.

Simply schedule the command in `Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('purge:unused-images')->daily();
}
```

The cleanup command also supports the following arguments:

- `--after` override `purge_after` config for the task
- `--disk` override `storage.disk` config for the task
- `--path` override `storage.path` config for the task
- `--show-log` enable verbosity over what going on with the task.

### How is It Work?

Everytime you upload an image or using base64 images, Vuetik Laravel will always keep track of these images and put them
into a database, these records have their `created_at` time and a status of either 'P' or 'A.'

And as you already aware Vuetik will only delete images with status of 'P'
and `created_at` matches `purge_after` criteria.

## Rendering Twitter

Vuetik Laravel only pre-hydrated your twitter content, so later it can be used to be parsed and hydrated
by the Twitter WidgetJS hence the name `pre-hydrated`.

In order to render twitter in your view, you need to use Twitter JS Dependency to hydrate the content:

```html

<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
```

Alternatively if you're using Blade, Vuetik Laravel also provide a helper diretive:

```blade
@twitterScript
```

Which under the hood renders the script mentioned earlier.

### Managing Failed Twitter Parsing

Twitter parsing may fail due to invalid id. Therefore, the option `twitter.failOnThrow` is existed and `true` by default.
In case you set it to `false`, Vuetik Laravel will render: Failed Fetching Twitter paragraph

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
