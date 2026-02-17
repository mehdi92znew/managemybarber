<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Permissions
        $permissions = [
            // Super Admin
            'manage-shops',
            'manage-subscriptions',
            'view-platform-analytics',

            // Shop Owner
            'manage-barbers',
            'manage-services',
            'manage-customers',
            'manage-appointments',
            'view-shop-analytics',

            // Barber
            'view-own-appointments',
            'create-appointment',
            'complete-appointment',
            'view-own-earnings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Roles and Assign Permissions

        // Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo([
            'manage-shops',
            'manage-subscriptions',
            'view-platform-analytics',
        ]);

        // Shop Owner
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->givePermissionTo([
            'manage-barbers',
            'manage-services',
            'manage-customers',
            'manage-appointments',
            'view-shop-analytics',
        ]);

        // Barber
        $barber = Role::firstOrCreate(['name' => 'barber']);
        $barber->givePermissionTo([
            'view-own-appointments',
            'create-appointment',
            'complete-appointment',
            'view-own-earnings',
        ]);
    }
}
