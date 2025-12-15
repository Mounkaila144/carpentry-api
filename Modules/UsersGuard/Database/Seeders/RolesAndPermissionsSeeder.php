<?php

namespace Modules\UsersGuard\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\UsersGuard\Entities\Tenant;
use Modules\UsersGuard\Entities\TenantUser;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('No tenants found. Please run TenantSeeder first.');

            return;
        }

        foreach ($tenants as $tenant) {
            // Initialize tenant context
            tenancy()->initialize($tenant);

            $this->command->info("Seeding roles and permissions for tenant: {$tenant->company_name}");

            // Reset cached roles and permissions
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // Create Permissions
            $permissions = [
                // User Management
                'view users',
                'create users',
                'edit users',
                'delete users',

                // Role Management
                'view roles',
                'create roles',
                'edit roles',
                'delete roles',

                // Settings
                'view settings',
                'edit settings',

                // Reports
                'view reports',
                'export reports',

                // Dashboard
                'view dashboard',
            ];

            foreach ($permissions as $permission) {
                Permission::updateOrCreate(
                    ['name' => $permission, 'guard_name' => 'tenant'],
                    ['display_name' => ucfirst($permission)]
                );
            }

            $this->command->info('  - Permissions created');

            // Create Roles
            $adminRole = Role::updateOrCreate(
                ['name' => 'Administrator', 'guard_name' => 'tenant'],
                ['display_name' => 'Administrator', 'description' => 'Full access to all features']
            );
            $adminRole->syncPermissions(Permission::all());

            $managerRole = Role::updateOrCreate(
                ['name' => 'Manager', 'guard_name' => 'tenant'],
                ['display_name' => 'Manager', 'description' => 'Manage users and view reports']
            );
            $managerRole->syncPermissions([
                'view users',
                'create users',
                'edit users',
                'view reports',
                'view dashboard',
            ]);

            $userRole = Role::updateOrCreate(
                ['name' => 'User', 'guard_name' => 'tenant'],
                ['display_name' => 'User', 'description' => 'Basic user access']
            );
            $userRole->syncPermissions([
                'view dashboard',
            ]);

            $this->command->info('  - Roles created');

            // Assign roles to users
            $admin = TenantUser::where('username', 'admin')->first();
            if ($admin) {
                $admin->assignRole('Administrator');
                $this->command->info('  - Administrator role assigned to admin user');
            }

            $manager = TenantUser::where('username', 'manager')->first();
            if ($manager) {
                $manager->assignRole('Manager');
                $this->command->info('  - Manager role assigned to manager user');
            }

            $user1 = TenantUser::where('username', 'user1')->first();
            if ($user1) {
                $user1->assignRole('User');
                $this->command->info('  - User role assigned to user1');
            }

            $user2 = TenantUser::where('username', 'user2')->first();
            if ($user2) {
                $user2->assignRole('User');
                $this->command->info('  - User role assigned to user2');
            }

            // End tenant context
            tenancy()->end();
        }

        $this->command->info('All roles and permissions seeded successfully!');
    }
}
