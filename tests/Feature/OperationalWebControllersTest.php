<?php

namespace Tests\Feature;

use App\Domains\Transaction\Models\Invoice;
use App\Domains\Transaction\Models\LedgerAccount;
use App\Domains\Transaction\Models\Payment;
use App\Domains\Transaction\Models\Payout;
use App\Domains\Transaction\Models\PayoutAccount;
use App\Models\DevelopmentUnit;
use App\Models\HandoverRecord;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\AmenityAndFeatureSeeder;
use Database\Seeders\AuthorizationSeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\DeveloperSalesSeeder;
use Database\Seeders\FinancialLedgerSeeder;
use Database\Seeders\LocationSeeder;
use Database\Seeders\ModerationUserSeeder;
use Database\Seeders\OperationsSeeder;
use Database\Seeders\PublicDiscoverySeeder;
use Database\Seeders\RentalSeeder;
use Database\Seeders\TrustSafetySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OperationalWebControllersTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AuthorizationSeeder::class);
        $this->seed(DemoUserSeeder::class);
        $this->seed(LocationSeeder::class);
        $this->seed(AmenityAndFeatureSeeder::class);
        $this->seed(PublicDiscoverySeeder::class);
        $this->seed(ModerationUserSeeder::class);
        $this->seed(RentalSeeder::class);
        $this->seed(DeveloperSalesSeeder::class);
        $this->seed(OperationsSeeder::class);
        $this->seed(FinancialLedgerSeeder::class);
        $this->seed(TrustSafetySeeder::class);

        $this->user = User::first();
    }

    public function test_rental_dashboard_and_subpages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.rental.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.rental.applications'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.rental.tenants'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.rental.leases'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.rental.payments'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.rental.tenants.show', $this->user->id));
        $response->assertStatus(200);
    }

    public function test_rental_application_and_lease_lifecycle(): void
    {
        $property = Property::first();
        $this->assertNotNull($property);

        // 1. Submit Application
        $appResponse = $this->actingAs($this->user)->post(route('dashboard.rental.applications.store'), [
            'property_id' => $property->id,
            'user_id' => $this->user->id,
            'rent_amount' => 28000,
            'deposit_amount' => 56000,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'employment_status' => 'Software Engineer',
            'annual_income' => 600000,
        ]);
        $appResponse->assertRedirect(route('dashboard.rental.applications'));

        $lease = Lease::where('property_id', $property->id)->where('status', 'pending')->first();
        $this->assertNotNull($lease);

        // 2. Approve Application -> Should activate and create rent schedules
        $statusResponse = $this->actingAs($this->user)->post(route('dashboard.rental.applications.status', $lease->id), [
            'status' => 'approved',
        ]);
        $statusResponse->assertStatus(302);
        $lease->refresh();
        $this->assertEquals('active', $lease->status);
        $this->assertGreaterThan(0, $lease->schedules()->count());

        $firstSchedule = $lease->schedules()->first();
        $this->assertNotNull($firstSchedule);

        // 3. Record rent payment
        $payResponse = $this->actingAs($this->user)->post(route('dashboard.rental.leases.payments', $lease->id), [
            'schedule_id' => $firstSchedule->id,
            'payment_method' => 'Telebirr',
            'reference_number' => 'TLB-2026-XYZ',
        ]);
        $payResponse->assertStatus(302);
        $firstSchedule->refresh();
        $this->assertEquals('paid', $firstSchedule->status);

        // 4. Renew Lease
        $renewResponse = $this->actingAs($this->user)->post(route('dashboard.rental.leases.renew', $lease->id), [
            'new_end_date' => now()->addMonths(12)->toDateString(),
            'new_rent_amount' => 30000,
        ]);
        $renewResponse->assertStatus(302);
        $lease->refresh();
        $this->assertEquals(30000, (float) $lease->rent_amount);

        // 5. View Lease Details Page
        $showResponse = $this->actingAs($this->user)->get(route('dashboard.rental.leases.show', $lease->id));
        $showResponse->assertStatus(200);

        // 6. Terminate Lease
        $terminateResponse = $this->actingAs($this->user)->post(route('dashboard.rental.leases.terminate', $lease->id));
        $terminateResponse->assertRedirect(route('dashboard.rental.leases'));
        $lease->refresh();
        $this->assertEquals('terminated', $lease->status);
    }

    public function test_developer_sales_pages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.sales.units'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.sales.buyers'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.sales.payment_plans'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.sales.contracts'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.sales.handovers'));
        $response->assertStatus(200);
    }

    public function test_developer_sales_unit_reservation_and_handover_workflow(): void
    {
        // 1. Register buyer
        $buyerEmail = 'test.buyer.'.uniqid().'@example.com';
        $buyerResponse = $this->actingAs($this->user)->post(route('dashboard.sales.buyers.store'), [
            'name' => 'Tesfaye Alemu',
            'email' => $buyerEmail,
            'phone' => '+251912345678',
        ]);
        $buyerResponse->assertRedirect(route('dashboard.sales.buyers'));
        $buyer = User::where('email', $buyerEmail)->first();
        $this->assertNotNull($buyer);

        // 2. Find available unit
        $unit = DevelopmentUnit::first();
        $this->assertNotNull($unit);

        // 3. Make reservation
        $resResponse = $this->actingAs($this->user)->post(route('dashboard.sales.reservation.store', $unit->id), [
            'buyer_id' => $buyer->id,
            'deposit_amount' => 150000,
            'notes' => 'Client reserved 2BR on 4th floor.',
        ]);
        $resResponse->assertRedirect(route('dashboard.sales.units.show', $unit->id));
        $unit->refresh();
        $this->assertEquals('reserved', $unit->status);

        $handover = HandoverRecord::where('unit_id', $unit->id)->first();
        $this->assertNotNull($handover);

        // 4. Update contract status to contracted
        $contractResponse = $this->actingAs($this->user)->post(route('dashboard.sales.contracts.status', $unit->id), [
            'status' => 'signed',
        ]);
        $contractResponse->assertStatus(302);
        $unit->refresh();
        $this->assertEquals('contracted', $unit->status);

        // 5. Complete Handover
        $handoverResponse = $this->actingAs($this->user)->post(route('dashboard.sales.handovers.complete', $handover->id), [
            'plumbing' => 1,
            'electrical' => 1,
            'finishes' => 1,
            'notes' => 'All snagging items fixed. 3 master keys handed to buyer.',
        ]);
        $handoverResponse->assertRedirect(route('dashboard.sales.handovers'));

        $handover->refresh();
        $this->assertEquals('completed', $handover->status);
        $this->assertNotNull($handover->keys_released_at);

        $unit->refresh();
        $this->assertEquals('sold', $unit->status);
    }

    public function test_operations_pages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.operations.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.operations.tickets'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.operations.cleaning'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.operations.staff'));
        $response->assertStatus(200);
    }

    public function test_finance_pages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.finance.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.transactions'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.invoices'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.ledger'));
        $response->assertStatus(200);
    }

    public function test_trust_and_safety_pages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.trust.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.trust.reports'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.trust.disputes'));
        $response->assertStatus(200);
    }

    public function test_operations_ticket_lifecycle_and_actions(): void
    {
        $property = Property::first();

        // 1. Store ticket
        $createResponse = $this->actingAs($this->user)->post(route('dashboard.operations.tickets.store'), [
            'title' => 'Plumbing leak test',
            'description' => 'Water leaking beneath kitchen sink',
            'priority' => 'high',
            'property_id' => $property?->id,
        ]);
        $createResponse->assertRedirect(route('dashboard.operations.tickets'));
        $this->assertDatabaseHas('maintenance_tickets', [
            'title' => 'Plumbing leak test',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $ticket = MaintenanceTicket::where('title', 'Plumbing leak test')->first();
        $this->assertNotNull($ticket);

        // 2. View ticket show page
        $showResponse = $this->actingAs($this->user)->get(route('dashboard.operations.tickets.show', $ticket->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Plumbing leak test');

        // 3. Update status
        $statusResponse = $this->actingAs($this->user)->post(route('dashboard.operations.tickets.status', $ticket->id), [
            'status' => 'in_progress',
            'assigned_to' => $this->user->id,
        ]);
        $statusResponse->assertStatus(302);
        $this->assertDatabaseHas('maintenance_tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'assigned_to' => $this->user->id,
        ]);

        // 4. Post internal note
        $noteResponse = $this->actingAs($this->user)->post(route('dashboard.operations.tickets.note', $ticket->id), [
            'note' => 'Technician dispatched with replacement pipe fittings.',
        ]);
        $noteResponse->assertStatus(302);
        $ticket->refresh();
        $this->assertStringContainsString('Technician dispatched with replacement pipe fittings', $ticket->description);
    }

    public function test_operations_cleaning_task_scheduling(): void
    {
        $property = Property::first();

        $response = $this->actingAs($this->user)->post(route('dashboard.operations.cleaning.store'), [
            'property_id' => $property->id,
            'assigned_cleaner_id' => $this->user->id,
            'notes' => 'Deep clean requested before guest arrival tomorrow.',
        ]);

        $response->assertRedirect(route('dashboard.operations.cleaning'));
        $this->assertDatabaseHas('cleaning_tasks', [
            'property_id' => $property->id,
            'assigned_cleaner_id' => $this->user->id,
            'status' => 'pending',
        ]);
    }

    public function test_finance_dashboard_and_subpages_render_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.finance.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.transactions'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.invoices'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.payouts'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('dashboard.finance.ledger'));
        $response->assertStatus(200);
    }

    public function test_finance_invoice_and_payout_settlement_workflow(): void
    {
        // 1. Create an invoice
        $createInvoice = $this->actingAs($this->user)->post(route('dashboard.finance.invoices.store'), [
            'user_id' => $this->user->id,
            'amount' => 18500,
            'due_date' => now()->addDays(7)->toDateString(),
            'notes' => 'Commercial lease quarterly invoice',
        ]);
        $createInvoice->assertRedirect(route('dashboard.finance.invoices'));
        $this->assertDatabaseHas('invoices', [
            'user_id' => $this->user->id,
            'amount' => 18500,
            'status' => 'pending',
        ]);

        $invoice = Invoice::where('user_id', $this->user->id)
            ->where('amount', 18500)
            ->first();
        $this->assertNotNull($invoice);

        // 2. Mark invoice paid
        $payInvoice = $this->actingAs($this->user)->post(route('dashboard.finance.invoices.pay', $invoice->id), [
            'payment_method' => 'bank_transfer',
            'notes' => 'Settled via CBE direct transfer',
        ]);
        $payInvoice->assertRedirect(route('dashboard.finance.invoices'));
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 18500,
            'status' => 'completed',
        ]);

        // 3. Request Payout
        $payoutAccount = PayoutAccount::firstOrCreate(
            ['user_id' => $this->user->id],
            [
                'bank_name' => 'Commercial Bank of Ethiopia',
                'account_number' => '1000998877665',
                'account_name' => 'Abebe Tadesse',
                'is_verified' => true,
            ]
        );

        $requestPayout = $this->actingAs($this->user)->post(route('dashboard.finance.payouts.store'), [
            'payout_account_id' => $payoutAccount->id,
            'amount' => 15000,
            'notes' => 'Landlord disbursement for Meskel Flower',
        ]);
        $requestPayout->assertRedirect(route('dashboard.finance.payouts'));
        $this->assertDatabaseHas('payouts', [
            'payout_account_id' => $payoutAccount->id,
            'amount' => 15000,
            'status' => 'pending',
        ]);

        $payout = Payout::where('payout_account_id', $payoutAccount->id)
            ->where('amount', 15000)
            ->first();
        $this->assertNotNull($payout);

        // 4. Process Payout
        $processPayout = $this->actingAs($this->user)->post(route('dashboard.finance.payouts.process', $payout->id), [
            'status' => 'completed',
            'notes' => 'Disbursement completed via core banking API',
        ]);
        $processPayout->assertRedirect(route('dashboard.finance.payouts'));
        $payout->refresh();
        $this->assertEquals('completed', $payout->status);
        $this->assertNotNull($payout->processed_at);

        // 5. Post double-entry manual ledger adjustment
        $accounts = LedgerAccount::take(2)->get();
        $this->assertCount(2, $accounts);

        $storeLedger = $this->actingAs($this->user)->post(route('dashboard.finance.ledger.store'), [
            'description' => 'Manual escrow reclassification',
            'debit_account_id' => $accounts[0]->id,
            'credit_account_id' => $accounts[1]->id,
            'amount' => 8500,
            'entry_date' => now()->toDateString(),
        ]);
        $storeLedger->assertRedirect(route('dashboard.finance.ledger'));
        $this->assertDatabaseHas('ledger_entries', [
            'description' => 'Manual escrow reclassification',
        ]);
        $this->assertDatabaseHas('ledger_entry_lines', [
            'ledger_account_id' => $accounts[0]->id,
            'type' => 'debit',
            'amount' => 8500,
        ]);
        $this->assertDatabaseHas('ledger_entry_lines', [
            'ledger_account_id' => $accounts[1]->id,
            'type' => 'credit',
            'amount' => 8500,
        ]);
    }

    public function test_transaction_refund_workflow(): void
    {
        $payment = Payment::create([
            'user_id' => $this->user->id,
            'payable_type' => Invoice::class,
            'payable_id' => Str::uuid(),
            'amount' => 5000,
            'currency' => 'ETB',
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
            'transaction_reference' => 'PAY-TEST-REFUND',
        ]);

        $response = $this->actingAs($this->user)->post(route('dashboard.finance.transactions.refund', $payment->transaction_reference), [
            'amount' => 2500,
            'reason' => 'Partial adjustment test',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('payments', [
            'amount' => -2500,
            'status' => 'completed',
        ]);
    }

    public function test_invoice_show_and_decision_workflow(): void
    {
        $invoice = Invoice::create([
            'user_id' => $this->user->id,
            'amount' => 12000,
            'due_date' => now()->addDays(15)->format('Y-m-d'),
            'status' => 'pending',
            'notes' => 'Consulting & Escrow services for Property Alpha',
        ]);

        // 1. View Invoice Show details page
        $showResponse = $this->actingAs($this->user)->get(route('dashboard.finance.invoices.show', $invoice->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('INV-'.strtoupper(substr($invoice->id, 0, 8)));
        $showResponse->assertSee('12,000.00 ETB');
        $showResponse->assertSee('Financial Decision Panel');

        // 2. Apply decision: Mark as PAID with payment settlement
        $decisionResponse = $this->actingAs($this->user)->post(route('dashboard.finance.invoices.decision', $invoice->id), [
            'decision' => 'pay',
            'payment_method' => 'telebirr',
            'decision_notes' => 'Settled via Telebirr merchant transaction verified',
        ]);

        $decisionResponse->assertStatus(302);
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertStringContainsString('Settled via Telebirr', $invoice->notes);

        // Verify payment record was created
        $this->assertDatabaseHas('payments', [
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'amount' => 12000,
            'payment_method' => 'telebirr',
            'status' => 'completed',
        ]);

        // 3. Apply decision: Cancel / Void an invoice
        $cancelInvoice = Invoice::create([
            'user_id' => $this->user->id,
            'amount' => 4500,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'pending',
            'notes' => 'Invoice to be voided',
        ]);

        $cancelResponse = $this->actingAs($this->user)->post(route('dashboard.finance.invoices.decision', $cancelInvoice->id), [
            'decision' => 'cancel',
            'decision_notes' => 'Customer requested cancellation prior to processing',
        ]);

        $cancelResponse->assertStatus(302);
        $cancelInvoice->refresh();
        $this->assertEquals('cancelled', $cancelInvoice->status);
    }

    public function test_bank_receipt_and_financial_receipt_workflow(): void
    {
        // 1. Verify clicking Bank Receipt / Financial Receipt for pipeline deal (TRX-ADD-8821) returns 200
        $receiptResponse = $this->actingAs($this->user)->get(route('dashboard.finance.transactions.show', 'TRX-ADD-8821'));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('TRX-ADD-8821');
        $receiptResponse->assertSee('Commercial Bank of Ethiopia');
        $receiptResponse->assertSee('Print Receipt');

        // 2. Verify another pipeline deal receipt (TRX-ADD-8822)
        $receiptResponse2 = $this->actingAs($this->user)->get(route('dashboard.finance.transactions.show', 'TRX-ADD-8822'));
        $receiptResponse2->assertStatus(200);
        $receiptResponse2->assertSee('TRX-ADD-8822');
        $receiptResponse2->assertSee('Awash Bank Escrow');

        // 3. Verify deal cards on Transactions page render Bank Receipt links properly
        $pipelineResponse = $this->actingAs($this->user)->get(route('dashboard.transactions'));
        $pipelineResponse->assertStatus(200);
        $pipelineResponse->assertSee('Bank Receipt');
        $pipelineResponse->assertSee(route('dashboard.finance.transactions.show', 'TRX-ADD-8821'));

        // 4. Verify Deal show page renders Financial Receipt button
        $dealShowResponse = $this->actingAs($this->user)->get(route('dashboard.transactions.show', 'TRX-ADD-8821'));
        $dealShowResponse->assertStatus(200);
        $dealShowResponse->assertSee('Financial Receipt');
        $dealShowResponse->assertSee(route('dashboard.finance.transactions.show', 'TRX-ADD-8821'));
    }
}
