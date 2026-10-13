<?php

namespace Modules\HumanResources\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Modules\HumanResources\Domain\Models\Department;
use Modules\HumanResources\Domain\Models\Designation;
use Modules\HumanResources\Domain\Models\Employee;
use Modules\HumanResources\Domain\Models\Attendance;
use Modules\HumanResources\Domain\Models\LeaveApplication;
use Modules\HumanResources\Domain\Models\PayrollRun;
use Modules\HumanResources\Domain\Models\Payslip;
use Modules\HumanResources\Domain\Contracts\DepartmentRepositoryInterface;
use Modules\HumanResources\Domain\Contracts\EmployeeRepositoryInterface;
use Modules\HumanResources\Domain\Contracts\AttendanceRepositoryInterface;
use Modules\HumanResources\Domain\Contracts\LeaveApplicationRepositoryInterface;
use Modules\HumanResources\Domain\Contracts\PayrollRepositoryInterface;
use Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories\EloquentDepartmentRepository;
use Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories\EloquentEmployeeRepository;
use Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories\EloquentAttendanceRepository;
use Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories\EloquentLeaveApplicationRepository;
use Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories\EloquentPayrollRepository;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        Department::class,
        Designation::class,
        Employee::class,
        Attendance::class,
        LeaveApplication::class,
        PayrollRun::class,
        Payslip::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(DepartmentRepositoryInterface::class, EloquentDepartmentRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, EloquentAttendanceRepository::class);
        $this->app->bind(LeaveApplicationRepositoryInterface::class, EloquentLeaveApplicationRepository::class);
        $this->app->bind(PayrollRepositoryInterface::class, EloquentPayrollRepository::class);
    }
}
