<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class MenuPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Menu Permissions for Sidebar
        $menuPermissions = [
            // Dashboard
            [
                'name' => 'menu_dashboard',
                'display_name' => 'Menu Dashboard',
                'description' => 'Akses menu Dashboard di sidebar',
                'group' => 'menu'
            ],
            
            // Fixed Assets
            [
                'name' => 'menu_fixed_assets',
                'display_name' => 'Menu Fixed Assets',
                'description' => 'Akses menu Fixed Assets di sidebar',
                'group' => 'menu'
            ],
            
            // Master Data
            [
                'name' => 'menu_master_data',
                'display_name' => 'Menu Master Data',
                'description' => 'Akses menu Master Data di sidebar',
                'group' => 'menu'
            ],
            [
                'name' => 'menu_master_locations',
                'display_name' => 'Menu Lokasi',
                'description' => 'Akses submenu Lokasi di Master Data',
                'group' => 'menu'
            ],
            [
                'name' => 'menu_master_statuses',
                'display_name' => 'Menu Status',
                'description' => 'Akses submenu Status di Master Data',
                'group' => 'menu'
            ],
            [
                'name' => 'menu_master_conditions',
                'display_name' => 'Menu Kondisi',
                'description' => 'Akses submenu Kondisi di Master Data',
                'group' => 'menu'
            ],
            [
                'name' => 'menu_master_vendors',
                'display_name' => 'Menu Vendor',
                'description' => 'Akses submenu Vendor di Master Data',
                'group' => 'menu'
            ],
            [
                'name' => 'menu_master_brands',
                'display_name' => 'Menu Brand',
                'description' => 'Akses submenu Brand di Master Data',
                'group' => 'menu'
            ],
            [
                'name' => 'menu_master_types',
                'display_name' => 'Menu Tipe Asset',
                'description' => 'Akses submenu Tipe Asset di Master Data',
                'group' => 'menu'
            ],
            
            // User Management
            [
                'name' => 'menu_user_management',
                'display_name' => 'Menu User Management',
                'description' => 'Akses menu User Management di sidebar',
                'group' => 'menu'
            ],
            [
                'name' => 'menu_users',
                'display_name' => 'Menu Users',
                'description' => 'Akses submenu Users di User Management',
                'group' => 'menu'
            ],
            [
                'name' => 'menu_roles',
                'display_name' => 'Menu Roles',
                'description' => 'Akses submenu Roles di User Management',
                'group' => 'menu'
            ],
            [
                'name' => 'menu_permissions',
                'display_name' => 'Menu Permissions',
                'description' => 'Akses submenu Permissions di User Management',
                'group' => 'menu'
            ],
        ];

        // Create or update permissions
        foreach ($menuPermissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Assign menu permissions to roles
        $this->assignMenuPermissionsToRoles();
        
        $this->command->info('Menu permissions seeded successfully!');
    }

    /**
     * Assign menu permissions to roles
     */
    private function assignMenuPermissionsToRoles(): void
    {
        // Admin - All menu access
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allMenuPermissions = Permission::where('group', 'menu')->pluck('id');
            $adminRole->permissions()->syncWithoutDetaching($allMenuPermissions);
        }

        // User - Only Dashboard menu
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $userMenuPermissions = Permission::whereIn('name', [
                'menu_dashboard',
            ])->pluck('id');
            $userRole->permissions()->syncWithoutDetaching($userMenuPermissions);
        }
    }
}
