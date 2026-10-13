<?php

// Module Initialization for Finance & Accounting

register_activation_hook('accounting', function () {
    // Initial activation tasks
});

add_action('admin_init', function () {
    // Menu or admin initialization
});

// Action: Handle payroll journal entry generation
add_action('payroll_run_processed', function ($payrollRun, array $data) {
    if (!class_exists(\Modules\Accounting\Models\JournalEntry::class)) return;

    $salExpenseAcc = \Modules\Accounting\Models\ChartOfAccount::where('code', '6100')->first()
        ?? \Modules\Accounting\Models\ChartOfAccount::where('subtype', 'payroll')->where('type', 'expense')->first();
    $bankAcc = \Modules\Accounting\Models\ChartOfAccount::where('code', '1110')->first()
        ?? \Modules\Accounting\Models\ChartOfAccount::where('subtype', 'bank')->first();
    $pfPayableAcc = \Modules\Accounting\Models\ChartOfAccount::where('code', '2400')->first();

    if ($salExpenseAcc && $bankAcc) {
        $jeNumber = 'JV-PAYROLL-' . date('Ym', mktime(0, 0, 0, $data['month'], 1, $data['year']));
        $je = \Modules\Accounting\Models\JournalEntry::create([
            'entry_number' => $jeNumber,
            'entry_date' => now()->toDateString(),
            'reference' => "Payroll {$payrollRun->month_name} {$data['year']}",
            'description' => "Monthly salary disbursement for {$data['employee_count']} employees",
            'source_type' => 'payroll',
            'source_id' => $payrollRun->id,
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => \Illuminate\Support\Facades\Auth::id(),
            'created_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        $je->items()->create([
            'account_id' => $salExpenseAcc->id,
            'debit' => $data['total_gross'],
            'credit' => 0,
            'description' => 'Gross Employee Salaries',
        ]);

        $je->items()->create([
            'account_id' => $bankAcc->id,
            'debit' => 0,
            'credit' => $data['total_net'],
            'description' => 'Net salary bank disbursement',
        ]);

        if ($data['total_deductions'] > 0 && $pfPayableAcc) {
            $je->items()->create([
                'account_id' => $pfPayableAcc->id,
                'debit' => 0,
                'credit' => $data['total_deductions'],
                'description' => 'Statutory Deductions (PF, PT, TDS) Payable',
            ]);
        }

        $payrollRun->update(['journal_entry_id' => $je->id]);
    }
}, 10, 2);

// Action: Auto-generate Tax Invoice when Sales Delivery Challan is dispatched
add_action('sales_delivery_challan_dispatched', function ($challan, $order) {
    if (!class_exists(\Modules\Accounting\Models\TaxInvoice::class)) return;

    if ($order->invoices()->exists()) return;

    $tenantState = \App\Services\TenantContext::getStateCode();
    $isInterstate = ($order->customer->state_code !== $tenantState);
    $invNumber = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    $invoice = \Modules\Accounting\Models\TaxInvoice::create([
        'invoice_number' => $invNumber,
        'invoice_type' => 'sales',
        'party_type' => 'customer',
        'party_id' => $order->customer_id,
        'sales_order_id' => $order->id,
        'invoice_date' => $challan->dispatch_date,
        'due_date' => now()->addDays(30)->toDateString(),
        'state_of_supply' => $order->customer->state_code,
        'is_interstate' => $isInterstate,
        'subtotal' => $order->subtotal,
        'cgst_amount' => $order->cgst_amount,
        'sgst_amount' => $order->sgst_amount,
        'igst_amount' => $order->igst_amount,
        'tax_amount' => $order->tax_amount,
        'total_amount' => $order->total_amount,
        'paid_amount' => 0,
        'balance_due' => $order->total_amount,
        'payment_status' => 'unpaid',
        'gst_breakup' => [
            'cgst' => $order->cgst_amount,
            'sgst' => $order->sgst_amount,
            'igst' => $order->igst_amount,
        ],
        'notes' => "Generated automatically upon dispatch under Delivery Challan {$challan->challan_number}",
        'created_by' => \Illuminate\Support\Facades\Auth::id(),
    ]);

    foreach ($order->items as $item) {
        $invoice->items()->create([
            'product_id' => $item->product_id,
            'item_name' => $item->product?->name ?? 'Item',
            'hsn_code' => $item->hsn_code,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'discount_percent' => $item->discount_percent,
            'gst_rate' => $item->gst_rate,
            'taxable_value' => $item->quantity * $item->unit_price,
            'cgst_amount' => $isInterstate ? 0 : round($item->tax_amount / 2, 2),
            'sgst_amount' => $isInterstate ? 0 : ($item->tax_amount - round($item->tax_amount / 2, 2)),
            'igst_amount' => $isInterstate ? $item->tax_amount : 0,
            'total_amount' => $item->total_amount,
        ]);
    }
}, 10, 2);


