<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Roles catalog (Railway MySQL is the source of truth).
     *
     * This is the LuckPerms/Discord-style additive role catalog. A user may
     * hold any number of these via the role_user pivot. Two axes that predate
     * this system are reflected in here for a single, unified view:
     *   - 'admin'        mirrors the legacy users.role enum
     *   - 'pro' / 'free' mirror user_billing.plan_key (Stripe-derived)
     * Those three are flagged is_system + (pro/free) not assignable by hand.
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();           // stable machine key, e.g. 'admin_read_only'
            $table->string('label');                    // human label, e.g. 'Admin Read Only'
            $table->string('description')->nullable();
            $table->string('color', 32)->nullable();    // badge colour (hex or token)
            $table->boolean('is_system')->default(false);     // managed by the platform, not free-form
            $table->boolean('is_assignable')->default(true);  // can an admin grant/revoke it by hand?
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
