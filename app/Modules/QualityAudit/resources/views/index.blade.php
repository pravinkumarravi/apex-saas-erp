@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-erp.page-header 
        title="ISO 9001 Quality Audits & Compliance" 
        subtitle="Modular quality assurance subsystem with decoupled domain repository and Livewire real-time UI."
        badge="QualityAudit Module v1.0"
    />

    <!-- KPI Metric Cards via x-erp.stat-card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-erp.stat-card 
            title="Scheduled Audits" 
            value="14" 
            change="+2 this month" 
            changeType="increase" 
            color="brand"
        >
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </x-slot:icon>
        </x-erp.stat-card>

        <x-erp.stat-card 
            title="Average Compliance" 
            value="96.4%" 
            change="+1.8% vs Q3" 
            changeType="increase" 
            color="success"
        >
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-erp.stat-card>

        <x-erp.stat-card 
            title="Open CAPA Cases" 
            value="2" 
            change="-4 resolved" 
            changeType="decrease" 
            color="warning"
        >
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </x-slot:icon>
        </x-erp.stat-card>

        <x-erp.stat-card 
            title="External ISO Certification" 
            value="Certified" 
            change="Valid until 2028" 
            changeType="neutral" 
            color="blue"
        >
            <x-slot:icon>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </x-slot:icon>
        </x-erp.stat-card>
    </div>

    <!-- Reactive Livewire Quality Audit Manager -->
    <livewire:quality-audit-manager />
</div>
@endsection
