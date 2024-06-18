<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\Roles;
use App\Enums\Permissions;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->syncRoles();
        $this->syncPermissions();
        $this->syncRolePermissions();
    }

    private function syncRoles(): void
    {
        // Loop through all the role values of enum Role. And do firstOrCreate
        foreach (Roles::cases() as $role) {
            Role::firstOrCreate([
                'name' => $role->value,
            ], [
                'display_name' => $role->value,
                'description' => $role->value,
            ]);
        }
    }

    private function syncPermissions(): void
    {
        // Loop through all the permission values of enum Permission. And do firstOrCreate
        foreach (Permissions::cases() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
            ], [
                'display_name' => $permission->value,
                'description' => $permission->value
            ]);
        }
    }

    private function syncRolePermissions(): void
    {
        $rolePermissions = Role::$rolePermissions;

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::whereName($roleName)->first();
            $role->syncPermissions(Permission::wherePermissionNameIn($permissions)->get());
        }

    }
}
