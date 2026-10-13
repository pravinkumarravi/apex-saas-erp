<?php

namespace Modules\Accounting\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Accounting\Models\ChartOfAccount;
use Modules\Accounting\Models\FiscalYear;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalItem;
use Modules\Accounting\Models\GstTaxRate;
use Modules\Accounting\Models\TaxInvoice;
use Modules\Accounting\Models\TaxInvoiceItem;
use Modules\Accounting\Models\Payment;
use Modules\Accounting\Contracts\ChartOfAccountRepositoryInterface;
use Modules\Accounting\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Contracts\TaxInvoiceRepositoryInterface;
use Modules\Accounting\Contracts\PaymentRepositoryInterface;
use Modules\Accounting\Repositories\EloquentChartOfAccountRepository;
use Modules\Accounting\Repositories\EloquentJournalEntryRepository;
use Modules\Accounting\Repositories\EloquentTaxInvoiceRepository;
use Modules\Accounting\Repositories\EloquentPaymentRepository;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ChartOfAccountRepositoryInterface::class, EloquentChartOfAccountRepository::class);
        $this->app->bind(JournalEntryRepositoryInterface::class, EloquentJournalEntryRepository::class);
        $this->app->bind(TaxInvoiceRepositoryInterface::class, EloquentTaxInvoiceRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'accounting');
            $this->loadViewsFrom($viewsPath, 'finance');
        }
    }
}
