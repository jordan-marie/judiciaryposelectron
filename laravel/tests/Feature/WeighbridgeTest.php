<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Form;
use App\Models\Transaction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class WeighbridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Permissions and Roles
        $permissions = ['manage-users', 'manage-roles', 'manage-forms', 'view-transactions', 'create-transactions', 'export-transactions'];
        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        $adminRole = Role::findOrCreate('Super Admin');
        $adminRole->givePermissionTo(Permission::all());

        $operatorRole = Role::findOrCreate('Scale Operator');
        $operatorRole->givePermissionTo(['view-transactions', 'create-transactions']);

        $this->seed();
    }

    public function test_login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('WeighSys Login');
    }

    public function test_user_can_login_and_access_dashboard()
    {
        $admin = User::where('email', 'admin@weighbridge.com')->first();

        $response = $this->post('/login', [
            'email' => 'admin@weighbridge.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);

        $dashResponse = $this->get('/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee("Today's Weighments", false);
    }

    public function test_operator_can_create_transaction_with_dynamic_metadata()
    {
        $operator = User::where('email', 'operator@weighbridge.com')->first();
        $this->actingAs($operator);

        $response = $this->get('/transactions/create');
        $response->assertStatus(200);
        $response->assertSee('Digital Scale Indicator Readout');

        $txData = [
            'transaction_code' => 'WB-2026-TEST',
            'status' => 'completed',
            'gross_weight' => 30000.00,
            'tare_weight' => 10000.00,
            'net_weight' => 20000.00,
            'plate_number' => 'B 9999 TEST',
            'meta' => [
                'driver_name' => 'John Doe',
                'transporter' => 'Fast Logistics',
                'material_type' => 'Coal',
            ]
        ];

        $storeResponse = $this->post('/transactions', $txData);
        $storeResponse->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'transaction_code' => 'WB-2026-TEST',
            'gross_weight' => 30000.00,
            'tare_weight' => 10000.00,
            'net_weight' => 20000.00,
            'plate_number' => 'B 9999 TEST',
        ]);

        $this->assertDatabaseHas('transaction_meta', [
            'field_name' => 'driver_name',
            'field_value' => 'John Doe',
        ]);
    }

    public function test_dynamic_query_engine_filters_transactions_by_custom_metadata()
    {
        $admin = User::where('email', 'admin@weighbridge.com')->first();
        $this->actingAs($admin);

        // Query by dynamic driver_name = Michael Scott
        $response = $this->get('/transactions?meta_field=driver_name&meta_value=Michael');
        $response->assertStatus(200);
        $response->assertSee('WB-2026-0001');

        // Query by material_type = Gravel
        $gravelResponse = $this->get('/transactions?meta_field=material_type&meta_value=Gravel');
        $gravelResponse->assertStatus(200);
        $gravelResponse->assertSee('WB-2026-0002');
    }

    public function test_csv_export_endpoint()
    {
        $admin = User::where('email', 'admin@weighbridge.com')->first();
        $this->actingAs($admin);

        $response = $this->get('/transactions/export/csv');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    public function test_form_builder_crud()
    {
        $admin = User::where('email', 'admin@weighbridge.com')->first();
        $this->actingAs($admin);

        $formData = [
            'name' => 'Custom Steel Scale Form',
            'is_active' => '1',
            'fields' => [
                [
                    'label' => 'Inspector Name',
                    'field_name' => 'inspector_name',
                    'field_type' => 'text',
                    'is_required' => '1',
                ],
                [
                    'label' => 'Steel Grade',
                    'field_name' => 'steel_grade',
                    'field_type' => 'select',
                    'options' => 'Grade A, Grade B, Stainless',
                ]
            ]
        ];

        $response = $this->post('/admin/form-builder', $formData);
        $response->assertRedirect(route('form-builder.index'));

        $this->assertDatabaseHas('forms', [
            'name' => 'Custom Steel Scale Form',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('form_fields', [
            'label' => 'Inspector Name',
            'field_name' => 'inspector_name',
        ]);
    }
}
