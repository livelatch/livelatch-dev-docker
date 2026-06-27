<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin-controlled catalog state for blade themes.
 *
 * A theme's *content* (manifest, view, assets) lives in code (baked) or on S3.
 * This table only holds the admin's live overrides — whether a theme is
 * enabled and whether it is Free or Pro — so they can be changed from the
 * Theme Manager without a redeploy or editing manifest files.
 *
 * A MISSING row means "enabled, tier from manifest (free by default)". So we
 * never need to seed; every existing theme is on out of the box.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blade_theme_catalog', function (Blueprint $table) {
            $table->string('slug', 64)->primary();
            $table->boolean('enabled')->default(true);
            // null = fall back to the manifest's tier (which defaults to 'free').
            $table->string('tier', 16)->nullable();
            // 'baked' (shipped in the image) or 's3' (uploaded to the bucket).
            $table->string('source', 16)->default('baked');
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blade_theme_catalog');
    }
};
