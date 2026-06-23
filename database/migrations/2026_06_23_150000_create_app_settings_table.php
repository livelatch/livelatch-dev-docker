<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A minimal key/value store for runtime-toggleable platform settings that admins
 * flip from the UI. Lives in MySQL (not .env) because the container runs with a
 * cached config and Railway-injected env, so runtime .env edits don't take
 * effect — the database is the reliable shared state.
 *
 * First consumer: `alpha_gate_enabled` (the new-user alpha gate kill-switch).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
