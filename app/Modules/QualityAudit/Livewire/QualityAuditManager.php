<?php

namespace Modules\QualityAudit\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\QualityAudit\Repositories\Contracts\QualityAuditRepositoryInterface;

class QualityAuditManager extends Component
{
    use WithPagination;

    public string $filterType = 'all';
    public string $search = '';

    // Form fields
    public string $title = '';
    public string $audit_type = 'internal';
    public string $auditor_name = '';
    public float $score_percentage = 95.0;
    public string $scheduled_date = '';
    public string $findings = '';

    protected function rules(): array
    {
        return [
            'title' => 'required|string|min:3|max:255',
            'audit_type' => 'required|in:internal,supplier,regulatory,customer',
            'auditor_name' => 'required|string|max:100',
            'score_percentage' => 'required|numeric|min:0|max:100',
            'scheduled_date' => 'required|date',
            'findings' => 'nullable|string',
        ];
    }

    public function mount(): void
    {
        $this->scheduled_date = date('Y-m-d');
    }

    public function saveAudit(QualityAuditRepositoryInterface $repository): void
    {
        $this->validate();

        $auditNumber = 'QA-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));

        $status = $this->score_percentage >= 85 ? 'passed' : 'capa_required';

        $repository->create([
            'audit_number' => $auditNumber,
            'title' => $this->title,
            'audit_type' => $this->audit_type,
            'auditor_name' => $this->auditor_name,
            'score_percentage' => $this->score_percentage,
            'status' => $status,
            'findings' => $this->findings,
            'scheduled_date' => $this->scheduled_date,
        ]);

        $this->reset(['title', 'auditor_name', 'findings']);
        $this->score_percentage = 95.0;
        $this->dispatch('close-audit-modal');
        session()->flash('message', "Audit {$auditNumber} recorded successfully with status: {$status}!");
    }

    public function render(QualityAuditRepositoryInterface $repository)
    {
        $query = $repository->with([]);

        if ($this->filterType !== 'all') {
            $query->where('audit_type', $this->filterType);
        }

        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        $audits = $query->orderBy('scheduled_date', 'desc')->paginate(10);

        return view('module-quality-audit::livewire.manager', [
            'audits' => $audits,
        ]);
    }
}
