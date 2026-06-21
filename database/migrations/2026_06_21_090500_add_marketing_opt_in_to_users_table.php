<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Local mirror of the user's marketing opt-in choice. The canonical source of
 * truth for LatchID users is Supabase (public.user_email_preferences); this
 * column keeps the choice for users captured at signup (including legacy
 * email/password registrations that have no Supabase auth user yet) and gives a
 * cheap local read without a Supabase round-trip. Defaults to opted-in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'marketing_opt_in')) {
                $table->boolean('marketing_opt_in')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'marketing_opt_in')) {
                $table->dropColumn('marketing_opt_in');
            }
        });
    }
};
