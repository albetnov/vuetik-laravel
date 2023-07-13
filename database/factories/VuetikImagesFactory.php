<?php

namespace Vuetik\VuetikLaravel\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Vuetik\VuetikLaravel\Models\VuetikImages;

class VuetikImagesFactory extends Factory
{
    protected $model = VuetikImages::class;

    public function definition(): array
    {
        return [
            'file_name' => fake()->word(),
            'status' => VuetikImages::PENDING,
        ];
    }
}
