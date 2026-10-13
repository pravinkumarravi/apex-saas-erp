<?php

namespace Modules\HumanResources\Repositories;

use Modules\HumanResources\Contracts\PayrollRepositoryInterface;
use Modules\HumanResources\Models\PayrollRun;
use Modules\HumanResources\Models\Payslip;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentPayrollRepository implements PayrollRepositoryInterface
{
    public function all(): Collection
    {
        return PayrollRun::with('payslips')->get();
    }

    public function find(int $id): ?PayrollRun
    {
        return PayrollRun::with('payslips')->find($id);
    }

    public function createRun(array $data, array $payslips = []): PayrollRun
    {
        return DB::transaction(function () use ($data, $payslips) {
            $run = PayrollRun::create($data);
            foreach ($payslips as $slip) {
                $slip['payroll_run_id'] = $run->id;
                Payslip::create($slip);
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
