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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('extension', 10)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('description', 65535)->nullable(); 
            $table->text('vision', 65535)->nullable();
            $table->text('mission', 65535)->nullable();
            $table->text('values', 65535)->nullable();
            $table->foreignId('locations_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
