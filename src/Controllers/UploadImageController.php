<?php

namespace Vuetik\VuetikLaravel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Vuetik\VuetikLaravel\ImageManager;
use Vuetik\VuetikLaravel\Models\VuetikImages;
use Vuetik\VuetikLaravel\Utils;

class UploadImageController extends Controller
{


    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:'.config('vuetik-laravel.max_upload_size')],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $fileName = $validated['image']->hashName();

            Storage::disk(config('vuetik-laravel.storage.disk'))->put(
                Utils::parseStoragePath().$fileName,
                $validated['image']->getContent()
            );

            return VuetikImages::create([
                'file_name' => $fileName,
            ]);
        });

        $imgVendorUrl = config('vuetik-laravel.image_vendor_route', '/img');

        if (config('vuetik-laravel.glide.enable')) {
            $url = ImageManager::getGlideUrl(
                image: $result,
                additionalProps: config('vuetik-laravel.glide.img_modifiers')
            );
        } else {
            $url = url($imgVendorUrl, $result->file_name);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'error' => false,
            'image' => [
                'url' => $url,
                'id' => $result->id,
            ],
        ]);
    }
}
