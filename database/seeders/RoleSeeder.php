<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * إنشاء الأدوار وربطها بالصلاحيات الموجودة مسبقاً
     * Roles: 'مدير النظام', 'مشرف', 'عميل'
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get all permissions created by PermissionSeeder
        // We can fetch from DB to be sure
        $permissions = Permission::all();

        // Create Roles with Arabic names
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);

        // Super Admin (مدير النظام) gets ALL permissions
        $superAdminRole->syncPermissions($permissions);
        $this->command->info('✓ Super Admin (مدير النظام): assigned all permissions');

        // Admin (مشرف) gets all except RBAC management
        // Filter permissions where name starts with 'roles.' or 'permissions.'
        $adminPermissions = $permissions->filter(function ($permission) {
            return !str_starts_with($permission->name, 'roles.') &&
                !str_starts_with($permission->name, 'permissions.');
        });

        $adminRole->syncPermissions($adminPermissions);
        $this->command->info('✓ Admin (مشرف): assigned ' . $adminPermissions->count() . ' permissions');
    }
}
