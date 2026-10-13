<?php

namespace Modules\Accounting\Repositories;

use Modules\Accounting\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class EloquentJournalEntryRepository implements JournalEntryRepositoryInterface
{
    public function all(): Collection
    {
        return JournalEntry::with('items')->get();
    }

    public function find(int $id): ?JournalEntry
    {
        return JournalEntry::with('items')->find($id);
    }

    public function create(array $data, array $items = []): JournalEntry
    {
        return DB::transaction(function () use ($data, $items) {
            $entry = JournalEntry::create($data);
            foreach ($items as $item) {
                $item['journal_entry_id'] = $entry->id;
                JournalItem::create($item);
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
