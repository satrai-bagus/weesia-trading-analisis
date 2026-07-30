<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_lists_subscription_then_token_packs(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('checkout'))->assertOk();

        $response->assertSee('Subscription 1 Bulan')
            ->assertSee('Rp 25.000')
            ->assertSee('Beli Token')
            ->assertSee('Total pesanan')
            // Paket termurah terpilih duluan, jadi ringkasan totalnya ikut paket itu.
            ->assertSee('data-token-picker', false)
            ->assertSee('data-token-price="Rp 80.000"', false)
            ->assertSee('Hemat 20%')
            ->assertSee('Analisa Harian');

        // Judul halaman juga memuat "Beli Token", jadi urutan dicek lewat heading section.
        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'Beli Token</h2>'),
            strpos($html, 'Subscription 1 Bulan</h2>'),
            'Kartu subscription harus berada di atas section beli token.'
        );
    }
}
