<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_authenticated_user_can_access_qr_code_generator()
    {
        $user = User::where('email', 'admin@weighbridge.com')->first();

        $response = $this->actingAs($user)->get(route('qrcode.generator'));

        $response->assertStatus(200);
        $response->assertSee('Transaction QR Code Generator');
    }

    public function test_scale_console_renders_qr_code_reader_card()
    {
        $user = User::where('email', 'operator@weighbridge.com')->first();

        $response = $this->actingAs($user)->get(route('scale.index'));

        $response->assertStatus(200);
        $response->assertSee('QR Code Reader');
    }
}
