<?php

namespace Vuetik\VuetikLaravel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Glide\Urls\UrlBuilderFactory;
use Symfony\Component\HttpFoundation\Response;
use Vuetik\VuetikLaravel\Models\VuetikImages;

class UploadImageController extends Controller
{
    private function parseStoragePath(): string {
        $path = config('vuetik-laravel.storage.path');

        if(Str::charAt($path, Str::length($path)) !== "/") {
            $path .= "/";
        }

        return $path;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:'. config('vuetik-laravel.max_upload_size')]
        ]);

        $result = DB::transaction(function () use ($validated) {
            $fileName = $validated['image']->hashName();

            Storage::disk(config('vuetik-laravel.storage.disk'))->put(
                $this->parseStoragePath() . $fileName,
                $validated['image']->getContent()
            );

            return VuetikImages::create([
                'file_name' => $fileName
            ]);
        });

        $imgVendorUrl = config('vuetik-laravel.image_vendor_route', "/img");

        if (config('vuetik-laravel.glide.enable')) {
            $urlBuilder = UrlBuilderFactory::create($imgVendorUrl, config('vuetik-laravel.glide.sign_key'));
            $url = $urlBuilder->getUrl($result->file_name, config('vuetik-laravel.glide.img_modifiers'));
        } else {
            $url = url($imgVendorUrl, $result->file_name);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'error' => false,
            'image' => [
                "url" => $url,
                "id" => $result->id,
            ]
        ]);
    }
}
