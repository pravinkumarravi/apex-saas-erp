<?php

namespace Modules\Accounting\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Modules\Accounting\Domain\Models\ChartOfAccount;
use Modules\Accounting\Domain\Models\FiscalYear;
use Modules\Accounting\Domain\Models\JournalEntry;
use Modules\Accounting\Domain\Models\JournalItem;
use Modules\Accounting\Domain\Models\GstTaxRate;
use Modules\Accounting\Domain\Models\TaxInvoice;
use Modules\Accounting\Domain\Models\TaxInvoiceItem;
use Modules\Accounting\Domain\Models\Payment;
use Modules\Accounting\Domain\Contracts\ChartOfAccountRepositoryInterface;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Contracts\TaxInvoiceRepositoryInterface;
use Modules\Accounting\Domain\Contracts\PaymentRepositoryInterface;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentChartOfAccountRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentJournalEntryRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentTaxInvoiceRepository;
use Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories\EloquentPaymentRepository;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        ChartOfAccount::class,
        FiscalYear::class,
        JournalEntry::class,
        JournalItem::class,
        GstTaxRate::class,
        TaxInvoice::class,
        TaxInvoiceItem::class,
        Payment::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(ChartOfAccountRepositoryInterface::class, EloquentChartOfAccountRepository::class);
        $this->app->bind(JournalEntryRepositoryInterface::class, EloquentJournalEntryRepository::class);
        $this->app->bind(TaxInvoiceRepositoryInterface::class, EloquentTaxInvoiceRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
    }
}
