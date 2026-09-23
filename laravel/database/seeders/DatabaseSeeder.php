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
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $scaleOperatorRole = Role::firstOrCreate(['name' => 'Scale Operator']);
        $inboundGatekeeperRole = Role::firstOrCreate(['name' => 'Inbound Gatekeeper']);
        $outboundAuditorRole = Role::firstOrCreate(['name' => 'Outbound Auditor']);

        // 2. Create Default Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@weighbridge.com'],
            [
                'name' => 'Super Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($superAdminRole);

        $operator = User::firstOrCreate(
            ['email' => 'operator@weighbridge.com'],
            [
                'name' => 'John Scale Operator',
                'password' => Hash::make('password'),
            ]
        );
        $operator->assignRole($scaleOperatorRole);

        $gatekeeper = User::firstOrCreate(
            ['email' => 'gatekeeper@weighbridge.com'],
            [
                'name' => 'Gatekeeper User',
                'password' => Hash::make('password'),
            ]
        );
        $gatekeeper->assignRole($inboundGatekeeperRole);

        // 3. Create Default Dynamic Forms
        // Form 1: Waste Collection Form
        $form1 = Form::firstOrCreate(
            ['slug' => 'waste-collection-form'],
            [
                'name' => 'Waste Collection Form',
                'description' => 'Dynamic form for capturing incoming municipal and industrial waste shipments.',
                'is_active' => true,
            ]
        );
        $form1->roles()->sync([$superAdminRole->id, $scaleOperatorRole->id, $inboundGatekeeperRole->id]);

        $fieldsForm1 = [
            [
                'label' => 'Customer / Supplier Name',
                'field_name' => 'customer_name',
                'field_type' => 'text',
                'is_required' => true,
                'options' => null,
                'sort_order' => 1,
            ],
            [
                'label' => 'Waste Material Type',
                'field_name' => 'waste_material_type',
                'field_type' => 'select',
                'is_required' => true,
                'options' => ['Municipal Solid Waste', 'Hazardous Industrial', 'Recyclable Plastics', 'Organic Compost'],
                'sort_order' => 2,
            ],
            [
                'label' => 'Driver License No.',
                'field_name' => 'driver_license',
                'field_type' => 'text',
                'is_required' => false,
                'options' => null,
                'sort_order' => 3,
            ],
            [
                'label' => 'Hazardous Waste Flag',
                'field_name' => 'is_hazardous',
                'field_type' => 'checkbox',
                'is_required' => false,
                'options' => null,
                'sort_order' => 4,
            ]
        ];

        foreach ($fieldsForm1 as $f) {
            FormField::firstOrCreate(
                ['form_id' => $form1->id, 'field_name' => $f['field_name']],
                $f
            );
        }

        // Form 2: Raw Material Inbound Form
        $form2 = Form::firstOrCreate(
            ['slug' => 'raw-material-inbound-form'],
            [
                'name' => 'Raw Material Inbound Form',
                'description' => 'Form for raw material intake (Aggregates, Ore, Gravel).',
                'is_active' => true,
            ]
        );
        $form2->roles()->sync([$superAdminRole->id, $scaleOperatorRole->id]);

        $fieldsForm2 = [
            [
                'label' => 'Supplier Code',
                'field_name' => 'supplier_code',
                'field_type' => 'text',
                'is_required' => true,
                'options' => null,
                'sort_order' => 1,
            ],
            [
                'label' => 'Material Grade',
                'field_name' => 'material_grade',
                'field_type' => 'select',
                'is_required' => true,
                'options' => ['Grade A Gravel', 'Crushed Limestone', 'Silica Sand', 'Iron Ore Class I'],
                'sort_order' => 2,
            ],
            [
                'label' => 'Moisture Content (%)',
                'field_name' => 'moisture_percentage',
                'field_type' => 'number',
                'is_required' => false,
                'options' => null,
                'sort_order' => 3,
            ],
            [
                'label' => 'Expected Delivery Date',
                'field_name' => 'expected_delivery',
                'field_type' => 'datetime',
                'is_required' => false,
                'options' => null,
                'sort_order' => 4,
            ]
        ];

        foreach ($fieldsForm2 as $f) {
            FormField::firstOrCreate(
                ['form_id' => $form2->id, 'field_name' => $f['field_name']],
                $f
            );
        }

        // Form 3: Scrap Metal Export Form
        $form3 = Form::firstOrCreate(
            ['slug' => 'scrap-metal-export-form'],
            [
                'name' => 'Scrap Metal Export Form',
                'description' => 'Outbound shipment tracking for scrap metals.',
                'is_active' => true,
            ]
        );
        $form3->roles()->sync([$superAdminRole->id, $outboundAuditorRole->id]);

        $fieldsForm3 = [
            [
                'label' => 'Buyer Name',
                'field_name' => 'buyer_name',
                'field_type' => 'text',
                'is_required' => true,
                'options' => null,
                'sort_order' => 1,
            ],
            [
                'label' => 'Metal Category',
                'field_name' => 'metal_category',
                'field_type' => 'select',
                'is_required' => true,
                'options' => ['Heavy Melting Steel', 'Aluminum Scrap', 'Copper Wire', 'Brass Turnings'],
                'sort_order' => 2,
            ],
            [
                'label' => 'Quality Inspector',
                'field_name' => 'inspector_name',
                'field_type' => 'text',
                'is_required' => false,
                'options' => null,
                'sort_order' => 3,
            ]
        ];

        foreach ($fieldsForm3 as $f) {
            FormField::firstOrCreate(
                ['form_id' => $form3->id, 'field_name' => $f['field_name']],
                $f
            );
        }

        // 4. Sample Transactions
        $tx1 = Transaction::firstOrCreate(
            ['transaction_code' => 'WB-20260923-0001'],
            [
                'form_id' => $form1->id,
                'status' => 'completed',
                'gross_weight' => 28450.00,
                'tare_weight' => 12100.00,
                'net_weight' => 16350.00,
                'plate_number' => 'ABC-1234',
                'created_by' => $operator->id,
                'created_at' => now()->subHours(3),
            ]
        );
        TransactionMeta::firstOrCreate(['transaction_id' => $tx1->id, 'field_name' => 'customer_name'], ['field_value' => 'Apex Environmental Solutions']);
        TransactionMeta::firstOrCreate(['transaction_id' => $tx1->id, 'field_name' => 'waste_material_type'], ['field_value' => 'Municipal Solid Waste']);
        TransactionMeta::firstOrCreate(['transaction_id' => $tx1->id, 'field_name' => 'driver_license'], ['field_value' => 'DL-98765432']);
        TransactionMeta::firstOrCreate(['transaction_id' => $tx1->id, 'field_name' => 'is_hazardous'], ['field_value' => '0']);

        $tx2 = Transaction::firstOrCreate(
            ['transaction_code' => 'WB-20260923-0002'],
            [
                'form_id' => $form2->id,
                'status' => 'completed',
                'gross_weight' => 42100.00,
                'tare_weight' => 14500.00,
                'net_weight' => 27600.00,
                'plate_number' => 'XYZ-9876',
                'created_by' => $operator->id,
                'created_at' => now()->subHours(1),
            ]
        );
        TransactionMeta::firstOrCreate(['transaction_id' => $tx2->id, 'field_name' => 'supplier_code'], ['field_value' => 'SUP-5501']);
        TransactionMeta::firstOrCreate(['transaction_id' => $tx2->id, 'field_name' => 'material_grade'], ['field_value' => 'Crushed Limestone']);
        TransactionMeta::firstOrCreate(['transaction_id' => $tx2->id, 'field_name' => 'moisture_percentage'], ['field_value' => '4.2']);

        $tx3 = Transaction::firstOrCreate(
            ['transaction_code' => 'WB-20260923-0003'],
            [
                'form_id' => $form1->id,
                'status' => 'in_progress',
                'gross_weight' => 19800.00,
                'tare_weight' => 0.00,
                'net_weight' => 19800.00,
                'plate_number' => 'KGL-4412',
                'created_by' => $operator->id,
                'created_at' => now()->subMinutes(15),
            ]
        );
        TransactionMeta::firstOrCreate(['transaction_id' => $tx3->id, 'field_name' => 'customer_name'], ['field_value' => 'Green Energy Bio-Fuels']);
        TransactionMeta::firstOrCreate(['transaction_id' => $tx3->id, 'field_name' => 'waste_material_type'], ['field_value' => 'Organic Compost']);
    }
}
