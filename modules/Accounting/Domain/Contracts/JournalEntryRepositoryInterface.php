<?php

namespace Modules\Accounting\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface JournalEntryRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?JournalEntry;
    public function create(array $data, array $items = []): JournalEntry;
    public function post(int $id, int $userId): bool;
}
