<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_formatted_amount_and_discount_are_saved_as_net_amount_and_edit_without_double_discount(): void
    {
        $user = User::factory()->create();
        $user->role = 'pengelola';
        $user->save();
        $data = ['date' => now()->format('Y-m-d'), 'type' => 'pemasukan', 'category' => 'Penjualan cetak 3D',
            'description' => 'Cetak model', 'amount' => '250.000', 'discount' => '25.000'];
        $this->actingAs($user)->post('/transaksi', $data)->assertRedirect('/transaksi');
        $transaction = Transaction::sole();
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'amount' => 225000, 'discount' => 25000]);
        $this->get('/dashboard')->assertOk()->assertSee('Rp 225.000');
        $this->get('/transaksi')->assertOk()->assertSee('Diskon Rp 25.000')->assertSee('data-money', false);
        $this->put('/transaksi/'.$transaction->id, $data)->assertRedirect('/transaksi');
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'amount' => 225000, 'discount' => 25000]);
        $data['discount'] = '';
        $this->put('/transaksi/'.$transaction->id, $data)->assertRedirect('/transaksi');
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'amount' => 250000, 'discount' => 0]);
    }

    public function test_invalid_money_and_excessive_discounts_are_rejected(): void
    {
        $user = User::factory()->create();
        $user->role = 'pengelola';
        $user->save();
        $this->actingAs($user);
        $data = ['date' => now()->format('Y-m-d'), 'type' => 'pemasukan', 'category' => 'Penjualan cetak 3D',
            'description' => 'Cetak model', 'amount' => '100.000', 'discount' => '100.001'];
        $this->post('/transaksi', $data)->assertSessionHasErrors('discount');
        foreach (['abc', '1e5', '-100', '12.34', '100,50'] as $amount) {
            $this->post('/transaksi', array_replace($data, ['amount' => $amount, 'discount' => '0']))->assertSessionHasErrors('amount');
        }
        $this->post('/transaksi', array_replace($data, ['discount' => '-1']))->assertSessionHasErrors('discount');
        $this->assertDatabaseCount('transactions', 0);
    }
}
