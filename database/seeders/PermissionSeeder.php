<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * إنشاء جميع الصلاحيات مع الأسماء العربية والإنجليزية
     */
    public function run(): void
    {
        // Reset cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions with English name and Arabic translation
        $permissions = [
            // Dashboard permissions - صلاحيات لوحة التحكم
            ['name' => 'dashboard.view', 'name_ar' => 'عرض لوحة التحكم'],
            ['name' => 'dashboard.statistics', 'name_ar' => 'عرض الإحصائيات'],

            // User Management permissions - صلاحيات إدارة المستخدمين
            ['name' => 'users.view-any', 'name_ar' => 'عرض جميع المستخدمين'],
            ['name' => 'users.view', 'name_ar' => 'عرض مستخدم'],
            ['name' => 'users.create', 'name_ar' => 'إنشاء مستخدم'],
            ['name' => 'users.update', 'name_ar' => 'تعديل مستخدم'],
            ['name' => 'users.toggle-active', 'name_ar' => 'تفعيل/تعطيل مستخدم'],
            ['name' => 'users.delete', 'name_ar' => 'حذف مستخدم'],




            // RBAC permissions (Super Admin only) - صلاحيات الأدوار والصلاحيات
            ['name' => 'roles.view-any', 'name_ar' => 'عرض جميع الأدوار'],
            ['name' => 'roles.view', 'name_ar' => 'عرض دور'],
            ['name' => 'roles.create', 'name_ar' => 'إنشاء دور'],
            ['name' => 'roles.update', 'name_ar' => 'تعديل دور'],
            ['name' => 'roles.delete', 'name_ar' => 'حذف دور'],
            ['name' => 'roles.sync-permissions', 'name_ar' => 'مزامنة صلاحيات الدور'],
            ['name' => 'permissions.view-any', 'name_ar' => 'عرض جميع الصلاحيات'],
            ['name' => 'permissions.view', 'name_ar' => 'عرض صلاحية'],
            ['name' => 'permissions.create', 'name_ar' => 'إنشاء صلاحية'],
            ['name' => 'permissions.update', 'name_ar' => 'تعديل صلاحية'],
            ['name' => 'permissions.delete', 'name_ar' => 'حذف صلاحية'],
        ];

        // Create all permissions with 'api' guard
        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                [
                    'name' => $permissionData['name'],
                    'guard_name' => 'api'
                ],
                [
                    'name_ar' => $permissionData['name_ar']
                ]
            );
        }

        $this->command->info('✓ Created/Updated ' . count($permissions) . ' permissions with Arabic names');
    }

    /**
     * Get all permission names (for use in RoleSeeder)
     */
    public static function getAllPermissionNames(): array
    {
        return [
            'dashboard.view',
            'dashboard.statistics',
            'users.view-any',
            'users.view',
            'users.create',
            'users.update',
            'users.toggle-active',
            'roles.view-any',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.sync-permissions',
            'permissions.view-any',
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
        ];
    }
}
