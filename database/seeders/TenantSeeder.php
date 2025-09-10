<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $cashierRole = Role::create(['name' => 'cashier']);
        $managerRole = Role::create(['name' => 'manager']);

        // Create permissions
        $permissions = [
            'pos.sell',
            'pos.void',
            'inventory.view',
            'inventory.edit',
            'customers.view',
            'customers.edit',
            'cash.open',
            'cash.close',
            'reports.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());
        $managerRole->givePermissionTo([
            'pos.sell',
            'pos.void',
            'inventory.view',
            'inventory.edit',
            'customers.view',
            'customers.edit',
            'cash.open',
            'cash.close',
            'reports.view',
        ]);
        $cashierRole->givePermissionTo([
            'pos.sell',
            'customers.view',
            'cash.open',
            'cash.close',
        ]);

        // Create admin user
        $adminUser = \App\Models\User::create([
            'name' => 'Administrator',
            'email' => tenant('admin_email'),
            'password' => Hash::make(tenant('admin_password')),
            'email_verified_at' => now(),
        ]);

        $adminUser->assignRole($adminRole);

        // Create default categories
        $categories = [
            ['name' => 'General', 'description' => 'Productos generales'],
            ['name' => 'Bebidas', 'description' => 'Bebidas y refrescos'],
            ['name' => 'Snacks', 'description' => 'Snacks y golosinas'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }

        // Create default tax
        \App\Models\Tax::create([
            'name' => 'IVA',
            'rate' => 19.0,
            'is_default' => true,
        ]);

        // Create default warehouse
        \App\Models\Warehouse::create([
            'name' => 'Almacén Principal',
            'code' => 'MAIN',
            'is_default' => true,
        ]);
    }
}
