<?php

namespace Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\HumanResources\Domain\Contracts\PayrollRun;
use Modules\HumanResources\Domain\Contracts\PayrollRepositoryInterface;
use Modules\HumanResources\Domain\Models\PayrollRunProxy;
use Modules\HumanResources\Domain\Models\PayslipProxy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentPayrollRepository implements PayrollRepositoryInterface
{
    public function all(): Collection
    {
        return PayrollRunProxy::with('payslips')->get();
    }

    public function find(int $id): ?PayrollRun
    {
        return PayrollRunProxy::with('payslips')->find($id);
    }

    public function createRun(array $data, array $payslips = []): PayrollRun
    {
        return DB::transaction(function () use ($data, $payslips) {
            $run = PayrollRunProxy::create($data);
            foreach ($payslips as $slip) {
                $slip['payroll_run_id'] = $run->id;
                PayslipProxy::create($slip);
            }
            return $run->load('payslips');
        });
    }

    public function updateStatus(int $id, string $status): bool
    {
        $run = $this->find($id);
        return $run ? $run->update(['status' => $status]) : false;
    }
}
