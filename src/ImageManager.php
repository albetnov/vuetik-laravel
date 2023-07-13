<?php

namespace Vuetik\VuetikLaravel;

use Illuminate\Support\Facades\Storage;
use League\Glide\Urls\UrlBuilderFactory;
use Vuetik\VuetikLaravel\Factories\ContentFactory;
use Vuetik\VuetikLaravel\Models\VuetikImages;

class ImageManager
{
    /**
     * storePreuploads
     * allows you to store images with pre-upload id only.
     * Using storePreuploads is faster as you don't need to wait for the content to be parsed and just use
     * given id from payload immediately.
     * Note: It came with limitation of no support over props modifiers
     */
    public static function storePreuploads(array $bindedIds): void
    {
        foreach($bindedIds as $bindedId) {
            $img = VuetikImages::find($bindedId);

            if(!$img) continue;

            $img->status = VuetikImages::ACTIVE;
            $img->save();
        }
    }

    /**
     * store
     * Allows you to store images generated from parsed content.
     * This function can both store binaries and pre-upload and respect given attributes which later can be served
     * via Glide for maximum optimization
     * @see getGlideUrl for serving the image with props
     */
    public static function store(ContentFactory $contentFactory, ?\Closure $binaryStorage = null): void
    {
        $uploadedIds = $contentFactory->image->ids;
        $binaries = $contentFactory->image->binaries;

        foreach ($uploadedIds as $uploadedId) {
            $img = VuetikImages::find($uploadedId->id);

            $img->update([
                'status' => VuetikImages::ACTIVE,
                'props' => $uploadedId->hasProps ? [
                    'width' => $uploadedId->width ?? 0,
                    'height' => $uploadedId->height ?? 0
                ] : null
            ]);
        }

        foreach ($binaries as $binary) {
            VuetikImages::create([
                'file_name' => $binary->uniqidName,
                'status' => VuetikImages::ACTIVE,
                'props' => $binary->hasProps ? [
                    'width' => $binary->width ?? 0,
                    'height' => $binary->height ?? 0
                ] : null
            ]);

            if ($binaryStorage) {
                $binaryStorage($binary->uniqidName, $binary->content);
            } else {
                Storage::disk(config('vuetik-laravel.storage.disk'))->put(
                    Utils::parseStoragePath().$binary->uniqidName,
                    $binary->content
                );
            }
        }
    }

    /**
     * getGlideUrl
     * Return the url of a Glide Instance which have the image with modifiers applied.
     * Currently only supports: width, height.
     */
    public static function getGlideUrl(VuetikImages $image, ?string $vendorUrl = null, ?array $additionalProps = []): string
    {
        $urlBuilder = UrlBuilderFactory::create($vendorUrl ?? config('vuetik-laravel.image_vendor_route'),
            config('vuetik-laravel.glide.sign_key'));

        $props = [];

        if($image->props) {
            if(isset($image->props['width'])) {
                $props['w'] = $image->props['width'];
            }

            if(isset($image->props['height'])) {
                $props['h'] = $image->props['height'];
            }
        }

        $props = array_merge($props, $additionalProps);

        return $urlBuilder->getUrl($image->file_name, $props);
    }
}
