<?php

namespace Vuetik\VuetikLaravel;

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
        foreach ($bindedIds as $bindedId) {
            $img = VuetikImages::find($bindedId);

            if (! $img) {
                continue;
            }

            $img->status = VuetikImages::ACTIVE;
            $img->save();
        }
    }

    /**
     * store
     * Allows you to store images generated from parsed content.
     * This function allows you to mark images as being used and update the props (if exist) which later can be
     * parsed by glide for maximum optimization.
     *
     * @see getGlideUrl for serving the image with props
     */
    public static function store(ContentFactory $contentFactory): array
    {
        $images = $contentFactory->images;
        $storage = [];

        foreach ($images as $image) {
            $storage[] = VuetikImages::updateOrCreate([
                'id' => $image->id,
            ], [
                'props' => [
                    'width' => $image->width,
                    'height' => $image->height,
                ],
                'status' => VuetikImages::ACTIVE,
            ]);
        }

        return $storage;
    }

    /**
     * getGlideUrl
     * Return the url of a Glide Instance which has the image with modifiers applied.
     * Currently only supports: width, height.
     */
    public static function getGlideUrl(VuetikImages $image, string $vendorUrl = null, ?array $additionalProps = []): string
    {
        $urlBuilder = UrlBuilderFactory::create($vendorUrl ?? config('vuetik-laravel.image_vendor_route'),
            config('vuetik-laravel.glide.sign_key'));

        $props = [];

        if ($image->props) {
            if (isset($image->props['width'])) {
                $props['w'] = $image->props['width'];
            }

            if (isset($image->props['height'])) {
                $props['h'] = $image->props['height'];
            }
        }

        $props = array_merge($props, $additionalProps);

        return $urlBuilder->getUrl($image->file_name, $props);
    }
}
