<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the role catalog. Idempotent: re-running updates labels/metadata
 * without disturbing existing role_user grants. Add future roles here.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // key, label, description, color, is_system, is_assignable
            [Role::ADMIN,           'Admin',           'Full platform administrator.',                                 '#ef4444', true,  true,  10],
            [Role::PRO,             'Pro User',        'Active Livelatch Pro plan (auto-synced from Stripe billing).',  '#22c55e', true,  false, 20],
            [Role::FREE,            'Free User',       'Free Livelatch plan (auto-synced from Stripe billing).',       '#64748b', true,  false, 30],
            [Role::PREVIEW,         'Preview',         'Early access to certain extras ahead of general release.',      '#a855f7', false, true,  40],
            [Role::ADMIN_READ_ONLY, 'Admin Read Only', 'Read-only admin view for platform demos.',                     '#f59e0b', false, true,  50],
            [Role::LATCHOPS,        'Latchops',        'Latchops control-plane operator.',                             '#0ea5e9', false, true,  60],
            [Role::ARTIST,          'Artist',          'Verified artist / creator.',                                   '#ec4899', false, true,  70],
            [Role::SDK,             'SDK',             'SDK / API integration access.',                                '#14b8a6', false, true,  80],
            [Role::STAFF,           'Staff',           'Livelatch staff member.',                                      '#6366f1', false, true,  90],
        ];

        foreach ($roles as [$key, $label, $description, $color, $isSystem, $isAssignable, $sort]) {
            Role::updateOrCreate(
                ['key' => $key],
                [
                    'label' => $label,
                    'description' => $description,
                    'color' => $color,
                    'is_system' => $isSystem,
                    'is_assignable' => $isAssignable,
                    'sort_order' => $sort,
                ]
            );
        }
    }
}
