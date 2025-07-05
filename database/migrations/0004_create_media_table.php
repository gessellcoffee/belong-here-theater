<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable'); // Creates mediable_id and mediable_type columns
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->string('disk')->default('public');
            $table->unsignedBigInteger('file_size');
            $table->string('collection_name')->nullable();
            $table->json('custom_properties')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
