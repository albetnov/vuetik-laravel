<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vuetik_images', function (Blueprint $table) {
            $table->ulid('id');
            $table->text('file_name');
            $table->enum('status', ['A', 'P'])->default('P');
            $table->json('props')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vuetik_images');
    }
};
