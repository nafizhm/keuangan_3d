<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_dashboard(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);
        $this->get('/dashboard')->assertRedirect('/login');
        $this->post('/login', ['username' => $user->username, 'password' => 'wrong'])->assertSessionHasErrors('username');
        $this->post('/login', ['username' => $user->username, 'password' => 'secret123'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertOk()->assertSee('Saldo usaha');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_expense_requires_receipt_and_receipt_is_private(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $user->role = 'pengelola';
        $user->save();
        $data = ['date' => now()->format('Y-m-d'), 'type' => 'pengeluaran', 'category' => 'Filamen & resin', 'description' => 'Filamen PLA', 'amount' => 250000];
        $this->actingAs($user)->post('/transaksi', $data)->assertSessionHasErrors('attachment');
        $data['attachment'] = UploadedFile::fake()->create('bukti.pdf', 20, 'application/pdf');
        $this->post('/transaksi', $data)->assertRedirect('/transaksi');
        $transaction = Transaction::firstOrFail();
        Storage::disk('local')->assertExists($transaction->attachment);
        $this->get('/transaksi')->assertOk()->assertSee('View lampiran');
        $this->get('/transaksi/'.$transaction->id.'/lampiran')->assertOk();
        $this->post('/logout');
        $this->get('/transaksi/'.$transaction->id.'/lampiran')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/transaksi/'.$transaction->id.'/lampiran')->assertOk();
    }

    public function test_investor_cannot_record_transactions(): void
    {
        $this->actingAs(User::factory()->create())->post('/transaksi', [])->assertForbidden();
        $this->get('/transaksi')->assertOk()->assertDontSee('Simpan transaksi');
    }

    public function test_manager_can_update_and_delete_transaction_with_receipt(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $user->role = 'pengelola';
        $user->save();
        Storage::disk('local')->put('receipts/old.pdf', 'old receipt');
        $transaction = Transaction::create([
            'user_id' => $user->id, 'date' => now()->format('Y-m-d'),
            'type' => 'pengeluaran', 'category' => 'Filamen & resin',
            'description' => 'Filamen lama', 'amount' => 100000,
            'attachment' => 'receipts/old.pdf',
        ]);
        $data = ['date' => now()->format('Y-m-d'), 'type' => 'pengeluaran',
            'category' => 'Filamen & resin', 'description' => 'Filamen baru', 'amount' => 200000];
        $this->actingAs($user)->put('/transaksi/'.$transaction->id, $data)->assertRedirect('/transaksi');
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'description' => 'Filamen baru', 'amount' => 200000, 'attachment' => 'receipts/old.pdf']);
        Storage::disk('local')->assertExists('receipts/old.pdf');
        $data['attachment'] = UploadedFile::fake()->create('baru.pdf', 20, 'application/pdf');
        $this->put('/transaksi/'.$transaction->id, $data)->assertRedirect('/transaksi');
        $newPath = $transaction->fresh()->attachment;
        Storage::disk('local')->assertMissing('receipts/old.pdf');
        Storage::disk('local')->assertExists($newPath);
        $this->get('/transaksi')->assertOk()->assertSee('data-edit', false)->assertSee('delete-modal');
        $this->delete('/transaksi/'.$transaction->id)->assertRedirect('/transaksi');
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
        Storage::disk('local')->assertMissing($newPath);
        $this->get('/dashboard')->assertOk()->assertSee('Rp 0');
    }

    public function test_investor_cannot_update_or_delete_and_expense_still_requires_receipt(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::create(['user_id' => $user->id, 'date' => now()->format('Y-m-d'),
            'type' => 'pemasukan', 'category' => 'Modal usaha', 'description' => 'Modal', 'amount' => 100000]);
        $this->actingAs($user)->put('/transaksi/'.$transaction->id, [])->assertForbidden();
        $this->delete('/transaksi/'.$transaction->id)->assertForbidden();
        $this->get('/transaksi')->assertOk()->assertDontSee('data-edit', false)->assertDontSee('delete-modal');
    }

    public function test_failed_edit_reopens_modal_with_previous_input(): void
    {
        $user = User::factory()->create();
        $user->role = 'pengelola';
        $user->save();
        $transaction = Transaction::create(['user_id' => $user->id, 'date' => now()->format('Y-m-d'),
            'type' => 'pemasukan', 'category' => 'Modal usaha', 'description' => 'Modal', 'amount' => 100000]);
        $this->actingAs($user)->from('/transaksi')->put('/transaksi/'.$transaction->id, [
            'date' => now()->format('Y-m-d'), 'type' => 'pengeluaran', 'category' => 'Lainnya',
            'description' => 'Biaya', 'amount' => 50000, '_form' => 'edit', '_transaction_id' => $transaction->id,
        ])->assertSessionHasErrors('attachment')->assertSessionHas('_old_input._form', 'edit');
        $response = $this->withCookie(config('session.cookie'), session()->getId())->get('/transaksi');
        $response->assertOk()->assertSee('const previous=');
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'type' => 'pemasukan']);
    }
}
