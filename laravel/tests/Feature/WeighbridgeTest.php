<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Transaction;
use App\Models\TransactionMeta;
use Spatie\Permission\Models\Role;

class WeighbridgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & admin
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $admin = User::where('email', 'admin@weighbridge.com')->first();

        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Operational Dashboard');
    }

    public function test_scale_operator_only_sees_assigned_active_forms(): void
    {
        $operator = User::where('email', 'operator@weighbridge.com')->first();
        $scaleRole = Role::where('name', 'Scale Operator')->first();

        // Create a form not assigned to Scale Operator
        $restrictedForm = Form::create([
            'name' => 'Restricted Auditor Form',
            'slug' => 'restricted-auditor-form',
            'is_active' => true,
        ]);
        $auditorRole = Role::where('name', 'Outbound Auditor')->first();
        $restrictedForm->roles()->sync([$auditorRole->id]);

        $response = $this->actingAs($operator)->get('/scale');
        $response->assertStatus(200);
        $response->assertSee('Waste Collection Form');
        $response->assertDontSee('Restricted Auditor Form');
    }

    public function test_super_admin_can_create_dynamic_form_with_roles_and_fields(): void
    {
        $admin = User::where('email', 'admin@weighbridge.com')->first();
        $scaleRole = Role::where('name', 'Scale Operator')->first();

        $postData = [
            'name' => 'Custom Quarry Shipment Form',
            'description' => 'Quarry materials extraction form',
            'is_active' => '1',
            'roles' => [$scaleRole->id],
            'fields' => [
                [
                    'label' => 'Quarry Location',
                    'field_type' => 'select',
                    'is_required' => '1',
                    'options' => 'North Pit, South Quarry, East Extraction'
                ],
                [
                    'label' => 'Aggregate Size (mm)',
                    'field_type' => 'number',
                    'is_required' => '0',
                    'options' => null
                ]
            ]
        ];

        $response = $this->actingAs($admin)->post('/admin/forms', $postData);
        $response->assertRedirect('/admin/forms');

        $this->assertDatabaseHas('forms', [
            'name' => 'Custom Quarry Shipment Form',
            'slug' => 'custom-quarry-shipment-form',
            'is_active' => true
        ]);

        $form = Form::where('slug', 'custom-quarry-shipment-form')->first();
        $this->assertCount(2, $form->fields);
        $this->assertTrue($form->roles->contains($scaleRole->id));
    }

    public function test_ajax_get_form_fields_returns_json_html(): void
    {
        $operator = User::where('email', 'operator@weighbridge.com')->first();
        $form = Form::where('slug', 'waste-collection-form')->first();

        $response = $this->actingAs($operator)->getJson("/scale/forms/{$form->id}/fields");
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'form_id', 'form_name', 'html', 'fields']);
    }

    public function test_operator_can_submit_transaction_with_meta_values(): void
    {
        $operator = User::where('email', 'operator@weighbridge.com')->first();
        $form = Form::where('slug', 'waste-collection-form')->first();

        $payload = [
            'form_id' => $form->id,
            'gross_weight' => 31200,
            'tare_weight' => 11000,
            'net_weight' => 20200,
            'plate_number' => 'TEST-9999',
            'status' => 'completed',
            'meta' => [
                'customer_name' => 'Acme Recycling Corp',
                'waste_material_type' => 'Recyclable Plastics',
                'is_hazardous' => '0'
            ]
        ];

        $response = $this->actingAs($operator)->postJson('/scale/transactions', $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'plate_number' => 'TEST-9999',
            'gross_weight' => 31200,
            'net_weight' => 20200,
            'status' => 'completed'
        ]);

        $tx = Transaction::where('plate_number', 'TEST-9999')->first();
        $this->assertDatabaseHas('transaction_meta', [
            'transaction_id' => $tx->id,
            'field_name' => 'customer_name',
            'field_value' => 'Acme Recycling Corp'
        ]);
    }

    public function test_transaction_query_engine_filters_by_meta_key_value(): void
    {
        $admin = User::where('email', 'admin@weighbridge.com')->first();

        $response = $this->actingAs($admin)->get('/transactions?meta_field=customer_name&meta_value=Apex');
        $response->assertStatus(200);
        $response->assertSee('WB-20260923-0001');
    }

    public function test_export_csv_downloads_file(): void
    {
        $admin = User::where('email', 'admin@weighbridge.com')->first();

        $response = $this->actingAs($admin)->get('/transactions/export-csv');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
