<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicReceiptsTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_receipts_are_copied_and_repeated_runs_are_safe(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();
        Transaction::create(['user_id' => $user->id, 'date' => now()->format('Y-m-d'),
            'type' => 'pengeluaran', 'category' => 'Lainnya', 'description' => 'Biaya',
            'amount' => 1000, 'attachment' => 'receipts/old.pdf']);
        Storage::disk('local')->put('receipts/old.pdf', 'receipt content');
        $this->artisan('receipts:publish')->assertSuccessful();
        $this->assertSame('receipt content', Storage::disk('public')->get('receipts/old.pdf'));
        Storage::disk('local')->assertExists('receipts/old.pdf');
        $this->artisan('receipts:publish')->assertSuccessful();
        $this->actingAs($user)->get('/transaksi/1/lampiran')->assertOk();
        Storage::disk('public')->put('receipts/old.pdf', 'different content');
        $this->artisan('receipts:publish')->assertFailed();
        $this->assertSame('different content', Storage::disk('public')->get('receipts/old.pdf'));
    }

    public function test_missing_receipts_are_reported(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create();
        Transaction::create(['user_id' => $user->id, 'date' => now()->format('Y-m-d'),
            'type' => 'pengeluaran', 'category' => 'Lainnya', 'description' => 'Biaya',
            'amount' => 1000, 'attachment' => 'receipts/missing.pdf']);
        $this->artisan('receipts:publish')->assertFailed();
    }
}
