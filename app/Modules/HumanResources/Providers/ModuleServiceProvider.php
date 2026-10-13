<?php

namespace Modules\HumanResources\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\HumanResources\Models\Department;
use Modules\HumanResources\Models\Designation;
use Modules\HumanResources\Models\Employee;
use Modules\HumanResources\Models\Attendance;
use Modules\HumanResources\Models\LeaveApplication;
use Modules\HumanResources\Models\PayrollRun;
use Modules\HumanResources\Models\Payslip;
use Modules\HumanResources\Contracts\DepartmentRepositoryInterface;
use Modules\HumanResources\Contracts\EmployeeRepositoryInterface;
use Modules\HumanResources\Contracts\AttendanceRepositoryInterface;
use Modules\HumanResources\Contracts\LeaveApplicationRepositoryInterface;
use Modules\HumanResources\Contracts\PayrollRepositoryInterface;
use Modules\HumanResources\Repositories\EloquentDepartmentRepository;
use Modules\HumanResources\Repositories\EloquentEmployeeRepository;
use Modules\HumanResources\Repositories\EloquentAttendanceRepository;
use Modules\HumanResources\Repositories\EloquentLeaveApplicationRepository;
use Modules\HumanResources\Repositories\EloquentPayrollRepository;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DepartmentRepositoryInterface::class, EloquentDepartmentRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, EloquentAttendanceRepository::class);
        $this->app->bind(LeaveApplicationRepositoryInterface::class, EloquentLeaveApplicationRepository::class);
        $this->app->bind(PayrollRepositoryInterface::class, EloquentPayrollRepository::class);
    }

    public function boot(): void
    {
        $viewsPath = dirname(__DIR__) . '/resources/views';
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, 'human-resources');
            $this->loadViewsFrom($viewsPath, 'hr');
        }
    }
}
