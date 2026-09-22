<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\FormField;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage settings',
            'manage form builder',
            'manage users',
            'edit transaction',
            'delete transaction',
            'perform live transaction',
            'view transaction',
            'override weight',
            'manual plate input',
            'export reporting',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles & Assign Permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions($permissions);

        $operatorRole = Role::firstOrCreate(['name' => 'Operator']);
        $operatorRole->syncPermissions([
            'perform live transaction',
            'view transaction',
        ]);

        $supervisorRole = Role::firstOrCreate(['name' => 'Supervisor']);
        $supervisorRole->syncPermissions([
            'perform live transaction',
            'view transaction',
            'override weight',
            'manual plate input',
            'export reporting',
        ]);

        // Seed Default Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@weighbridge.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
            ]
        );
        $admin->assignRole('Admin');

        $operator = User::firstOrCreate(
            ['email' => 'operator@weighbridge.com'],
            [
                'name' => 'Weighbridge Operator',
                'password' => Hash::make('operator123'),
            ]
        );
        $operator->assignRole('Operator');

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@weighbridge.com'],
            [
                'name' => 'Weighbridge Supervisor',
                'password' => Hash::make('supervisor123'),
            ]
        );
        $supervisor->assignRole('Supervisor');

        // Seed Default Transaction Form & Custom Form Fields
        $defaultForm = Form::firstOrCreate(
            ['title' => 'Default Weighbridge Form'],
            ['is_active' => true]
        );

        $fields = [
            [
                'field_name' => 'driver_name',
                'field_type' => 'text',
                'label' => 'Driver Name',
                'is_required' => true,
                'options_json' => null,
                'sort_order' => 1,
            ],
            [
                'field_name' => 'material_type',
                'field_type' => 'select',
                'label' => 'Material Type',
                'is_required' => true,
                'options_json' => ['Gravel', 'Sand', 'Cement', 'Coal', 'Steel', 'Scrap'],
                'sort_order' => 2,
            ],
            [
                'field_name' => 'supplier',
                'field_type' => 'text',
                'label' => 'Supplier',
                'is_required' => false,
                'options_json' => null,
                'sort_order' => 3,
            ],
            [
                'field_name' => 'destination',
                'field_type' => 'text',
                'label' => 'Destination',
                'is_required' => false,
                'options_json' => null,
                'sort_order' => 4,
            ],
            [
                'field_name' => 'container_no',
                'field_type' => 'text',
                'label' => 'Container No',
                'is_required' => false,
                'options_json' => null,
                'sort_order' => 5,
            ],
        ];

        foreach ($fields as $fieldData) {
            FormField::firstOrCreate(
                [
                    'form_id' => $defaultForm->id,
                    'field_name' => $fieldData['field_name'],
                ],
                $fieldData
            );
        }
    }
}
