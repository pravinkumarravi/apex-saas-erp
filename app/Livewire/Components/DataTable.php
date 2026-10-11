<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    public string $modelClass;
    public array $columns = [];
    public array $searchable = [];
    public string $search = '';
    public string $sortColumn = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public string $title = 'Data Records';
    public array $relations = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortColumn' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function exportCsv()
    {
        $items = $this->getQuery()->get();
        $filename = strtolower(class_basename($this->modelClass)) . '_export_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');
            // Header row
            $headerRow = array_map(fn($col) => $col['label'] ?? $col['key'], $this->columns);
            fputcsv($handle, $headerRow);

            foreach ($items as $item) {
                $row = [];
                foreach ($this->columns as $col) {
                    $key = $col['key'];
                    $val = data_get($item, $key);
                    $row[] = is_array($val) ? json_encode($val) : $val;
                }
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function getQuery()
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $this->modelClass;
        $query = $model->newQuery();

        if (!empty($this->relations)) {
            $query->with($this->relations);
        }

        if (!empty($this->search) && !empty($this->searchable)) {
            $query->where(function ($q) {
                foreach ($this->searchable as $field) {
                    $q->orWhere($field, 'like', '%' . $this->search . '%');
                }
            });
        }

        return $query->orderBy($this->sortColumn, $this->sortDirection);
    }

    public function render()
    {
        $records = $this->getQuery()->paginate($this->perPage);

        return view('livewire.components.data-table', [
            'records' => $records,
        ]);
    }
}
