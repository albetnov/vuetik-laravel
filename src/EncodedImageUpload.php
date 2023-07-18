<?php

namespace Vuetik\VuetikLaravel;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use Intervention\Image\ImageManagerStatic;
use Vuetik\VuetikLaravel\Models\VuetikImages;

class EncodedImageUpload
{
    public function __construct(private readonly string $content)
    {

    }

    private function validate(Image $image, string $format, string $fileName): bool
    {
        $path = tempnam(sys_get_temp_dir(), 'vuetik_images').$format;
        $image->save($path);

        $payload = new UploadedFile(
            path: $path,
            originalName: $fileName,
            mimeType: $image->mime(),
            test: true // test has to be set to true to avoid is_uploaded_file validation.
        );

        $isValid = Validator::make([
            'image' => $payload,
        ], [
            'image' => Utils::getImageValidationRules(),
        ])->fails();

        unlink($path);

        return $isValid;
    }

    private function store(string $format, string $path, string $disk, $imageQuality): Image
    {
        $decodedImage = Utils::getBase64Image($this->content);

        $imageFile = ImageManagerStatic::make($decodedImage)
            ->encode($format, $imageQuality);

        Storage::disk($disk)
            ->put($path, $imageFile->stream());

        return $imageFile;
    }

    /**
     * @throws Exception
     */
    public function save(bool $throwOnFail, string $saveFormat, string $disk, int $quality): bool|VuetikImages
    {
        try {
            DB::beginTransaction();
            $fileName = Str::ulid()->toRfc4122().".$saveFormat";
            $path = Utils::parseStoragePath().$fileName;

            $imageFile = $this->store(
                format: $saveFormat,
                path: $path,
                disk: $disk,
                imageQuality: $quality
            );

            if ($this->validate(
                image: $imageFile,
                format: $saveFormat,
                fileName: $fileName
            )) {
                return false;
            }

            $image = VuetikImages::create([
                'file_name' => $fileName,
                'status' => VuetikImages::PENDING, // should stay pending.
            ]);

            DB::commit();

            return $image;
        } catch (\Exception $e) {
            DB::rollBack();
            if ($throwOnFail) {
                throw $e;
            }

            return false;
        }
    }
}
