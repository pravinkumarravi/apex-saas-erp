<?php

namespace Modules\HumanResources\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\HumanResources\Domain\Contracts\Attendance;
use Modules\HumanResources\Domain\Contracts\AttendanceRepositoryInterface;
use Modules\HumanResources\Domain\Models\AttendanceProxy;
use Illuminate\Database\Eloquent\Collection;

class EloquentAttendanceRepository implements AttendanceRepositoryInterface
{
    public function all(): Collection
    {
        return AttendanceProxy::with('employee')->get();
    }

    public function find(int $id): ?Attendance
    {
        return AttendanceProxy::with('employee')->find($id);
    }

    public function recordAttendance(array $data): Attendance
    {
        return AttendanceProxy::updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'] ?? null,
                'employee_id' => $data['employee_id'],
                'date' => $data['date'],
            ],
            $data
        );
    }
}
