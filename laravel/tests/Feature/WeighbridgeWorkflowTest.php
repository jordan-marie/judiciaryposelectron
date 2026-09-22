<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeighbridgeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_and_dashboard_access()
    {
        $admin = User::where('email', 'admin@weighbridge.com')->first();

        $response = $this->post('/login', [
            'email' => 'admin@weighbridge.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_inbound_and_outbound_weighment_workflow()
    {
        $operator = User::where('email', 'operator@weighbridge.com')->first();
        $this->actingAs($operator);

        // 1. Submit Inbound Transaction
        $inboundResponse = $this->post('/transactions/inbound', [
            'license_plate' => 'TEST-1234',
            'initial_weight' => 15000,
            'fields' => [
                1 => 'Driver John',
                2 => 'Gravel',
            ]
        ]);

        $inboundResponse->assertRedirect('/dashboard');
        $this->assertDatabaseHas('transactions', [
            'license_plate' => 'TEST-1234',
            'initial_weight' => 15000,
            'status' => 'PENDING',
        ]);

        $transaction = \App\Models\Transaction::where('license_plate', 'TEST-1234')->first();

        // 2. Submit Outbound Transaction
        $outboundResponse = $this->post('/transactions/outbound/' . $transaction->id, [
            'final_weight' => 5000,
        ]);

        $outboundResponse->assertRedirect('/dashboard');
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'final_weight' => 5000,
            'net_weight' => 10000,
            'status' => 'COMPLETED',
        ]);
    }
}
