<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_theme_settings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('theme_id')->nullable();

            $table->unsignedBigInteger('theme_version_id')->nullable();

            $table->string('preset')->default('default');

            $table->json('custom_settings')->nullable();

            $table->timestamps();

            $table->unique('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('theme_id')
                ->references('id')
                ->on('themes')
                ->nullOnDelete();

            $table->foreign('theme_version_id')
                ->references('id')
                ->on('theme_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_theme_settings');
    }
};