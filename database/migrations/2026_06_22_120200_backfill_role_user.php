<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seed the role catalog, then backfill role_user from the two pre-existing
     * access axes so every user keeps exactly the access they have today:
     *   - users.role  -> admin (and vip, where present)
     *   - plan_key    -> pro / free
     */
    public function up()
    {
        (new RoleSeeder())->run();

        $vip = Role::where('key', 'vip')->first(); // may not exist; vip stays legacy-only

        User::with('billing')->chunkById(200, function ($users) use ($vip) {
            foreach ($users as $user) {
                if ($user->role === 'admin') {
                    $user->assignRole(Role::ADMIN);
                }
                if ($vip && $user->role === 'vip') {
                    $user->roles()->syncWithoutDetaching([$vip->id]);
                }

                // Plan roles straight from billing (defaults to free).
                $user->syncPlanRoles();
            }
        });
    }

    public function down()
    {
        // Non-destructive: leave catalog + grants in place.
    }
};
