<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Store;
use App\Models\User;
use App\Models\ChartOfAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class AccountingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::factory()->create();
        $this->user = User::factory()->create();
    }

    public function test_create_journal_throws_exception_when_not_balanced()
    {
        $this->actingAs($this->user);

        // Kas = 1000 (debit), Penjualan = 900 (credit) -> Imbalanced by 100
        $entries = [
            [
                'account_sub_type' => ChartOfAccount::SUB_KAS,
                'debit' => 1000,
                'credit' => 0,
                'memo' => 'Debit Kas',
            ],
            [
                'account_sub_type' => ChartOfAccount::SUB_PENJUALAN,
                'debit' => 0,
                'credit' => 900,
                'memo' => 'Credit Penjualan',
            ]
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Jurnal tidak seimbang!');

        DB::transaction(function () use ($entries) {
            AccountingService::createJournal(
                $this->store->id,
                'Test Imbalanced Journal',
                'POS',
                $entries
            );
        });
    }

    public function test_journal_sale_creates_correct_balanced_entries()
    {
        $this->actingAs($this->user);

        DB::transaction(function () {
            // Sale with gross_amount = 50000 and totalHpp = 20000
            AccountingService::journalSale(
                $this->store->id,
                50000,
                20000,
                123, // order id
                'POS'
            );
        });

        // Verify Journal header was created
        $journal = Journal::where('store_id', $this->store->id)
            ->where('reference_id', 123)
            ->first();

        $this->assertNotNull($journal);
        $this->assertEquals(70000, $journal->total_debit); // 50000 (Kas) + 20000 (HPP)
        $this->assertEquals(70000, $journal->total_credit); // 50000 (Penjualan) + 20000 (Inventory FG)

        // Verify Journal entry lines
        $this->assertDatabaseHas('journal_entries', [
            'journal_id' => $journal->id,
            'debit' => 50000,
            'credit' => 0,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'journal_id' => $journal->id,
            'debit' => 0,
            'credit' => 50000,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'journal_id' => $journal->id,
            'debit' => 20000,
            'credit' => 0,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'journal_id' => $journal->id,
            'debit' => 0,
            'credit' => 20000,
        ]);
    }
}
