<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Organization\Models\Tenant;
use Modules\Identity\Models\User;
use Modules\Accounting\Models\TaxInvoice;
use Modules\Accounting\Models\ChartOfAccount;
use App\Services\TenantContext;

class GstAndAccountingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::where('subdomain', 'companya')->first();
        TenantContext::setTenant($tenant);
        $user = User::where('tenant_id', $tenant->id)->first();
        $this->actingAs($user);
    }

    public function test_double_entry_balance_constraint()
    {
        $accountDeb = ChartOfAccount::where('type', 'expense')->first();
        $accountCred = ChartOfAccount::where('type', 'liability')->first();

        // 1. Attempt creating unbalanced journal entry (Debit != Credit)
        $response = $this->post('/finance/journal-entries', [
            'entry_date' => now()->format('Y-m-d'),
            'description' => 'Unbalanced test entry',
            'items' => [
                ['account_id' => $accountDeb->id, 'debit' => 1000, 'credit' => 0],
                ['account_id' => $accountCred->id, 'debit' => 0, 'credit' => 500], // Difference of 500
            ]
        ]);

        $response->assertSessionHas('error');

        // 2. Create balanced journal entry (Debit == Credit)
        $responseBalanced = $this->post('/finance/journal-entries', [
            'entry_date' => now()->format('Y-m-d'),
            'description' => 'Balanced test entry',
            'items' => [
                ['account_id' => $accountDeb->id, 'debit' => 1000, 'credit' => 0],
                ['account_id' => $accountCred->id, 'debit' => 0, 'credit' => 1000],
            ]
        ]);

        $responseBalanced->assertRedirect();
        $responseBalanced->assertSessionHas('success');
    }

    public function test_indian_gst_tax_invoice_generation()
    {
        $invoice = TaxInvoice::first();
        $this->assertNotNull($invoice, 'Seeded tax invoice should exist');

        // Check GST calculations
        if ($invoice->igst_amount > 0) {
            // Inter-state
            $this->assertEquals(0, $invoice->cgst_amount);
            $this->assertEquals(0, $invoice->sgst_amount);
            $this->assertEquals($invoice->subtotal + $invoice->igst_amount, $invoice->total_amount);
        } else {
            // Intra-state (CGST == SGST)
            $this->assertEquals($invoice->cgst_amount, $invoice->sgst_amount);
            $this->assertEquals($invoice->subtotal + $invoice->cgst_amount + $invoice->sgst_amount, $invoice->total_amount);
        }
    }
}
