<?php

namespace Modules\Accounting\Infrastructure\Persistence\Eloquent\Repositories;

use Modules\Accounting\Domain\Contracts\JournalEntry;
use Modules\Accounting\Domain\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Domain\Models\JournalEntryProxy;
use Modules\Accounting\Domain\Models\JournalItemProxy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentJournalEntryRepository implements JournalEntryRepositoryInterface
{
    public function all(): Collection
    {
        return JournalEntryProxy::with('items')->get();
    }

    public function find(int $id): ?JournalEntry
    {
        return JournalEntryProxy::with('items')->find($id);
    }

    public function create(array $data, array $items = []): JournalEntry
    {
        return DB::transaction(function () use ($data, $items) {
            $entry = JournalEntryProxy::create($data);
            foreach ($items as $item) {
                $item['journal_entry_id'] = $entry->id;
                JournalItemProxy::create($item);
            }
            return $entry->load('items');
        });
    }

    public function post(int $id, int $userId): bool
    {
        $entry = $this->find($id);
        if (!$entry || !$entry->isBalanced()) {
            return false;
        }

        return $entry->update([
            'status' => 'posted',
            'posted_at' => now(),
            'posted_by' => $userId,
        ]);
    }
}
