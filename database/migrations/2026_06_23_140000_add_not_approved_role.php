<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the `not_approved` role to the catalog. This is the base role for new
 * sign-ups during the alpha — holders are bounced to a holding page by the
 * EnsureApproved middleware until an admin removes the role.
 *
 * IMPORTANT: this migration only inserts the catalog row. It deliberately does
 * NOT write to role_user, so existing users are completely unaffected — only
 * accounts created after this ships receive the role (assigned at signup).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        Role::updateOrCreate(
            ['key' => Role::NOT_APPROVED],
            [
                'label' => 'Not Approved',
                'description' => 'New sign-up awaiting alpha-tester approval — gated out of the dashboard until removed.',
                'color' => '#9ca3af',
                'is_system' => false,
                'is_assignable' => true,
                'sort_order' => 5,
            ]
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        Role::where('key', Role::NOT_APPROVED)->delete();
    }
};
