<?php

namespace Modules\Accounting\Http\Controllers;

use Modules\Governance\Domain\Models\AuditLog;
use Modules\Accounting\Domain\Models\ChartOfAccount;
use Modules\Sales\Domain\Models\Customer;
use Modules\Accounting\Domain\Models\JournalEntry;
use Modules\Accounting\Domain\Models\JournalItem;
use Modules\Accounting\Domain\Models\Payment;
use Modules\Inventory\Domain\Models\Product;
use Modules\Procurement\Domain\Models\PurchaseOrder;
use Modules\Procurement\Domain\Models\Supplier;
use Modules\Accounting\Domain\Models\TaxInvoice;
use Modules\Accounting\Domain\Models\TaxInvoiceItem;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController
{
    // --- CHART OF ACCOUNTS (COA) ---
    public function chartOfAccounts()
    {
        $accounts = ChartOfAccount::with(['parent', 'children'])
            ->orderBy('code')
            ->get();

        $types = ['asset' => 'Assets (1000)', 'liability' => 'Liabilities (2000)', 'equity' => 'Equity (3000)', 'revenue' => 'Revenue (4000)', 'expense' => 'Expenses (5000)'];

        return view('pages.finance.chart-of-accounts', compact('accounts', 'types'));
    }

    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'subtype' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
        ]);

        $account = ChartOfAccount::create(array_merge($validated, ['is_active' => true]));
        AuditLog::log('created', $account, null, $account->toArray());

        return redirect()->back()->with('success', "Account {$account->code} - {$account->name} created.");
    }

    // --- JOURNAL ENTRIES ---
    public function journalEntries(Request $request)
    {
        $entries = JournalEntry::with(['items.account', 'poster', 'creator'])
            ->latest('entry_date')
            ->paginate(10);

        $accounts = ChartOfAccount::where('is_active', true)->orderBy('code')->get();

        return view('pages.finance.journal-entries', compact('entries', 'accounts'));
    }

    public function storeJournalEntry(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:100',
            'description' => 'required|string',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.debit' => 'nullable|numeric|min:0',
            'items.*.credit' => 'nullable|numeric|min:0',
        ]);

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($request->items as $item) {
            $totalDebit += (float) ($item['debit'] ?? 0);
            $totalCredit += (float) ($item['credit'] ?? 0);
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return redirect()->back()->with('error', "Double-entry violation: Debits (₹{$totalDebit}) must equal Credits (₹{$totalCredit}).");
        }

        return DB::transaction(function () use ($request) {
            $entryNumber = 'JV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => $request->entry_date,
                'reference' => $request->reference,
                'description' => $request->description,
                'source_type' => 'manual',
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => Auth::id(),
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                $entry->items()->create([
                    'account_id' => $item['account_id'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                    'description' => $item['description'] ?? $request->description,
                ]);
            }

            AuditLog::log('created_and_posted', $entry, null, $entry->toArray());

            return redirect()->back()->with('success', "Journal Entry {$entryNumber} balanced and posted successfully.");
        });
    }

    // --- TAX INVOICES ---
    public function invoices(Request $request)
    {
        $query = TaxInvoice::with(['customer', 'supplier', 'items.product', 'creator']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('invoice_type', $request->type);
        }

        $invoices = $query->latest('invoice_date')->paginate(10);
        $customers = Customer::where('status', 'active')->get();
        $products = Product::where('is_active', true)->get();

        return view('pages.finance.invoices', compact('invoices', 'customers', 'products'));
    }

    public function showInvoice($id)
    {
        $invoice = TaxInvoice::with(['customer', 'supplier', 'items.product', 'payments', 'creator'])->findOrFail($id);
        $tenant = TenantContext::getTenant();

        return view('pages.finance.invoice-show', compact('invoice', 'tenant'));
    }

    // --- PAYMENTS & RECEIPTS ---
    public function payments(Request $request)
    {
        $payments = Payment::with(['taxInvoice', 'account', 'recorder'])->latest('payment_date')->paginate(10);
        $unpaidInvoices = TaxInvoice::where('payment_status', '!=', 'paid')->get();
        $bankAccounts = ChartOfAccount::where('subtype', 'bank')->get();

        return view('pages.finance.payments', compact('payments', 'unpaidInvoices', 'bankAccounts'));
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'tax_invoice_id' => 'required|exists:tax_invoices,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:bank_transfer,cheque,cash,upi,credit_card,other',
            'account_id' => 'required|exists:chart_of_accounts,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $invoice = TaxInvoice::findOrFail($request->tax_invoice_id);
            $amount = (float) $request->amount;

            if ($amount > (float) $invoice->balance_due + 0.01) {
                return redirect()->back()->with('error', "Payment amount (₹{$amount}) cannot exceed balance due (₹{$invoice->balance_due}).");
            }

            $paymentNumber = 'REC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'tax_invoice_id' => $invoice->id,
                'party_type' => $invoice->party_type,
                'party_id' => $invoice->party_id,
                'payment_date' => $request->payment_date,
                'amount' => $amount,
                'payment_type' => $invoice->invoice_type === 'sales' ? 'receive' : 'pay',
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'account_id' => $request->account_id,
                'notes' => $request->notes,
                'recorded_by' => Auth::id(),
            ]);

            // Update Invoice Paid and Balance Due
            $newPaid = (float) $invoice->paid_amount + $amount;
            $newBalance = max(0, (float) $invoice->total_amount - $newPaid);
            $newStatus = $newBalance < 0.01 ? 'paid' : 'partially_paid';

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => $newBalance,
                'payment_status' => $newStatus,
            ]);

            // Create Auto Double-Entry Journal Entry
            $jeNumber = 'JV-PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $arAccount = ChartOfAccount::where('code', '1200')->first() ?? ChartOfAccount::where('subtype', 'accounts_receivable')->first();

            if ($arAccount) {
                $je = JournalEntry::create([
                    'entry_number' => $jeNumber,
                    'entry_date' => $request->payment_date,
                    'reference' => $paymentNumber,
                    'description' => "Receipt for Invoice {$invoice->invoice_number}",
                    'source_type' => 'payment',
                    'source_id' => $payment->id,
                    'status' => 'posted',
                    'posted_at' => now(),
                    'posted_by' => Auth::id(),
                    'created_by' => Auth::id(),
                ]);

                // Debit Bank / Cash Account
                $je->items()->create([
                    'account_id' => $request->account_id,
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => "Funds received via {$request->payment_method}",
                ]);

                // Credit Accounts Receivable
                $je->items()->create([
                    'account_id' => $arAccount->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => "Receivable credit for Invoice {$invoice->invoice_number}",
                    'party_type' => $invoice->party_type,
                    'party_id' => $invoice->party_id,
                ]);
            }

            AuditLog::log('recorded_payment', $payment, null, $payment->toArray());

            return redirect()->back()->with('success', "Payment {$paymentNumber} recorded. Invoice updated and Journal Entry posted.");
        });
    }

    // --- INDIAN GST REPORTS ---
    public function gstReports(Request $request)
    {
        $tenant = TenantContext::getTenant();

        // GSTR-1 Outward Supplies (B2B)
        $outwardInvoices = TaxInvoice::where('invoice_type', 'sales')
            ->with(['customer', 'items'])
            ->latest('invoice_date')
            ->get();

        $totalTaxable = $outwardInvoices->sum('subtotal');
        $totalCgst = $outwardInvoices->sum('cgst_amount');
        $totalSgst = $outwardInvoices->sum('sgst_amount');
        $totalIgst = $outwardInvoices->sum('igst_amount');
        $totalGst = $outwardInvoices->sum('tax_amount');

        // GSTR-3B Summary
        $inwardPOs = PurchaseOrder::whereIn('status', ['received', 'ordered'])->get();
        $itcCgst = $inwardPOs->sum('cgst_amount');
        $itcSgst = $inwardPOs->sum('sgst_amount');
        $itcIgst = $inwardPOs->sum('igst_amount');
        $totalItc = $itcCgst + $itcSgst + $itcIgst;

        $netPayableCgst = max(0, $totalCgst - $itcCgst);
        $netPayableSgst = max(0, $totalSgst - $itcSgst);
        $netPayableIgst = max(0, $totalIgst - $itcIgst);
        $netTotalGst = $netPayableCgst + $netPayableSgst + $netPayableIgst;

        return view('pages.finance.gst-reports', compact(
            'tenant',
            'outwardInvoices',
            'totalTaxable',
            'totalCgst',
            'totalSgst',
            'totalIgst',
            'totalGst',
            'itcCgst',
            'itcSgst',
            'itcIgst',
            'totalItc',
            'netPayableCgst',
            'netPayableSgst',
            'netPayableIgst',
            'netTotalGst'
        ));
    }

    // --- FINANCIAL STATEMENTS ---
    public function financialStatements()
    {
        $tenant = TenantContext::getTenant();

        $accounts = ChartOfAccount::with(['journalItems.journalEntry' => function ($q) {
            $q->where('status', 'posted');
        }])->get();

        // Profit & Loss
        $revenues = $accounts->where('type', 'revenue');
        $expenses = $accounts->where('type', 'expense');

        $totalRevenue = $revenues->sum('balance');
        $totalExpense = $expenses->sum('balance');
        $netProfit = $totalRevenue - $totalExpense;

        // Balance Sheet
        $assets = $accounts->where('type', 'asset');
        $liabilities = $accounts->where('type', 'liability');
        $equity = $accounts->where('type', 'equity');

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance') + $netProfit; // Retained earnings includes net profit

        // Trial Balance
        $trialBalance = $accounts->map(function ($acc) {
            $postedItems = $acc->journalItems->filter(fn($item) => $item->journalEntry?->status === 'posted');
            $debit = (float) $postedItems->sum('debit');
            $credit = (float) $postedItems->sum('credit');

            return [
                'code' => $acc->code,
                'name' => $acc->name,
                'type' => $acc->type,
                'debit' => $debit,
                'credit' => $credit,
                'net_debit' => $debit > $credit ? ($debit - $credit) : 0,
                'net_credit' => $credit > $debit ? ($credit - $debit) : 0,
            ];
        });

        $totalTbDebit = $trialBalance->sum('net_debit');
        $totalTbCredit = $trialBalance->sum('net_credit');

        return view('pages.finance.financial-statements', compact(
            'tenant',
            'revenues',
            'expenses',
            'totalRevenue',
            'totalExpense',
            'netProfit',
            'assets',
            'liabilities',
            'equity',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'trialBalance',
            'totalTbDebit',
            'totalTbCredit'
        ));
    }
}
