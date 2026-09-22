<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Transaction;
use App\Models\TransactionMeta;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'manage-users',
            'manage-roles',
            'manage-forms',
            'view-transactions',
            'create-transactions',
            'export-transactions',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        // Roles
        $adminRole = Role::findOrCreate('Super Admin');
        $adminRole->givePermissionTo(Permission::all());

        $operatorRole = Role::findOrCreate('Scale Operator');
        $operatorRole->givePermissionTo(['view-transactions', 'create-transactions']);

        $auditorRole = Role::findOrCreate('Auditor');
        $auditorRole->givePermissionTo(['view-transactions', 'export-transactions']);

        $managerRole = Role::findOrCreate('Manager');
        $managerRole->givePermissionTo(['view-transactions', 'export-transactions', 'manage-forms']);

        // Demo Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@weighbridge.com'],
            ['name' => 'System Administrator', 'password' => Hash::make('password')]
        );
        $admin->syncRoles([$adminRole]);

        $operator = User::firstOrCreate(
            ['email' => 'operator@weighbridge.com'],
            ['name' => 'Scale Operator John', 'password' => Hash::make('password')]
        );
        $operator->syncRoles([$operatorRole]);

        $auditor = User::firstOrCreate(
            ['email' => 'auditor@weighbridge.com'],
            ['name' => 'Auditor Sarah', 'password' => Hash::make('password')]
        );
        $auditor->syncRoles([$auditorRole]);

        $manager = User::firstOrCreate(
            ['email' => 'manager@weighbridge.com'],
            ['name' => 'Logistics Manager', 'password' => Hash::make('password')]
        );
        $manager->syncRoles([$managerRole]);

        // Default Weighbridge Form
        $form = Form::create([
            'name' => 'Standard Weighbridge Form',
            'slug' => 'weighbridge-default',
            'is_active' => true,
        ]);

        $fields = [
            [
                'label' => 'Driver Name',
                'field_name' => 'driver_name',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 1,
            ],
            [
                'label' => 'Transporter',
                'field_name' => 'transporter',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 2,
            ],
            [
                'label' => 'Material Type',
                'field_name' => 'material_type',
                'field_type' => 'select',
                'is_required' => true,
                'options' => ['Coal', 'Gravel', 'Sand', 'Steel', 'Scrap Metal', 'Grain'],
                'sort_order' => 3,
            ],
            [
                'label' => 'Supplier',
                'field_name' => 'supplier',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 4,
            ],
            [
                'label' => 'Delivery Date & Time',
                'field_name' => 'delivery_datetime',
                'field_type' => 'datetime',
                'is_required' => false,
                'sort_order' => 5,
            ],
            [
                'label' => 'Quality Check Passed',
                'field_name' => 'quality_check',
                'field_type' => 'checkbox',
                'is_required' => false,
                'sort_order' => 6,
            ],
            [
                'label' => 'Remarks',
                'field_name' => 'remarks',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 7,
            ],
        ];

        foreach ($fields as $fieldData) {
            $form->fields()->create($fieldData);
        }

        // Seed Sample Transactions
        $sampleTransactions = [
            [
                'transaction_code' => 'WB-2026-0001',
                'status' => 'completed',
                'gross_weight' => 28500.00,
                'tare_weight' => 12100.00,
                'net_weight' => 16400.00,
                'plate_number' => 'B 1234 ABC',
                'created_by' => $operator->id,
                'meta' => [
                    'driver_name' => 'Michael Scott',
                    'transporter' => 'Apex Logistics Ltd',
                    'material_type' => 'Coal',
                    'supplier' => 'Midwest Mining Co.',
                    'delivery_datetime' => '2026-09-22 09:30',
                    'quality_check' => '1',
                    'remarks' => 'First shift delivery - clean load'
                ]
            ],
            [
                'transaction_code' => 'WB-2026-0002',
                'status' => 'completed',
                'gross_weight' => 32100.00,
                'tare_weight' => 13500.00,
                'net_weight' => 18600.00,
                'plate_number' => 'B 5678 DEF',
                'created_by' => $operator->id,
                'meta' => [
                    'driver_name' => 'Dwight Schrute',
                    'transporter' => 'Schrute Transport',
                    'material_type' => 'Gravel',
                    'supplier' => 'Quarry Operations Inc.',
                    'delivery_datetime' => '2026-09-22 10:15',
                    'quality_check' => '1',
                    'remarks' => 'Standard aggregate batch'
                ]
            ],
            [
                'transaction_code' => 'WB-2026-0003',
                'status' => 'in_progress',
                'gross_weight' => 24000.00,
                'tare_weight' => 0.00,
                'net_weight' => 24000.00,
                'plate_number' => 'B 9012 GHI',
                'created_by' => $operator->id,
                'meta' => [
                    'driver_name' => 'Jim Halpert',
                    'transporter' => 'Blue Line Freight',
                    'material_type' => 'Steel',
                    'supplier' => 'Industrial Steels',
                    'delivery_datetime' => '2026-09-22 11:00',
                    'quality_check' => '0',
                    'remarks' => 'Awaiting tare weight on exit'
                ]
            ],
        ];

        foreach ($sampleTransactions as $txData) {
            $metaData = $txData['meta'];
            unset($txData['meta']);

            $tx = Transaction::create($txData);
            foreach ($metaData as $key => $val) {
                TransactionMeta::create([
                    'transaction_id' => $tx->id,
                    'field_name' => $key,
                    'field_value' => $val,
                ]);
            }
        }
    }
}
