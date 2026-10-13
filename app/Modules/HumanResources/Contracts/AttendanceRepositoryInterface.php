<?php

namespace Modules\HumanResources\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Attendance;
    public function recordAttendance(array $data): Attendance;
}
