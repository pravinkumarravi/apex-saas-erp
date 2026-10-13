<?php

namespace Modules\HumanResources\Repositories;

use Modules\HumanResources\Contracts\AttendanceRepositoryInterface;
use Modules\HumanResources\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

class EloquentAttendanceRepository implements AttendanceRepositoryInterface
{
    public function all(): Collection
    {
        return Attendance::with('employee')->get();
    }

    public function find(int $id): ?Attendance
    {
        return Attendance::with('employee')->find($id);
    }

    public function recordAttendance(array $data): Attendance
    {
        return Attendance::updateOrCreate(
            [
                'tenant_id' => $data['tenant_id'] ?? null,
                'employee_id' => $data['employee_id'],
                'date' => $data['date'],
            ],
            $data
        );
    }
}
