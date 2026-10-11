<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\LeaveApplication;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HrController
{
    // --- EMPLOYEES ---
    public function employees(Request $request)
    {
        $query = Employee::with(['department', 'designation', 'user']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('emp_code', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $employees = $query->latest()->paginate(10);
        $departments = Department::all();
        $designations = Designation::all();

        return view('pages.hr.employees', compact('employees', 'departments', 'designations'));
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'emp_code' => 'required|string|max:30',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'date_of_joining' => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
            'hra' => 'required|numeric|min:0',
            'special_allowance' => 'required|numeric|min:0',
            'pan' => 'nullable|string|max:10',
            'aadhar' => 'nullable|string|max:12',
            'pf_number' => 'nullable|string|max:30',
            'esi_number' => 'nullable|string|max:30',
            'bank_account' => 'nullable|string|max:50',
            'bank_ifsc' => 'nullable|string|max:20',
        ]);

        $employee = Employee::create(array_merge($validated, ['status' => 'active']));
        AuditLog::log('created', $employee, null, $employee->toArray());

        return redirect()->back()->with('success', "Employee {$employee->full_name} ({$employee->emp_code}) added successfully.");
    }

    // --- ATTENDANCE ---
    public function attendance(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        $attendances = Attendance::with('employee.department')->where('date', $date)->get();
        $allEmployees = Employee::where('status', 'active')->get();

        return view('pages.hr.attendance', compact('attendances', 'allEmployees', 'date'));
    }

    public function recordAttendance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,on_leave,holiday',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $att = Attendance::updateOrCreate([
            'tenant_id' => TenantContext::getTenantId(),
            'employee_id' => $request->employee_id,
            'date' => $request->date,
        ], [
            'status' => $request->status,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'notes' => $request->notes,
        ]);

        AuditLog::log('marked_attendance', $att, null, $att->toArray());

        return redirect()->back()->with('success', 'Attendance recorded successfully.');
    }

    // --- LEAVE APPLICATIONS ---
    public function leaves(Request $request)
    {
        $leaves = LeaveApplication::with(['employee.department', 'approver'])->latest()->paginate(10);
        $employees = Employee::where('status', 'active')->get();

        return view('pages.hr.leaves', compact('leaves', 'employees'));
    }

    public function storeLeave(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:casual,sick,earned,unpaid,maternity,paternity',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_days' => 'required|numeric|min:0.5',
            'reason' => 'required|string',
        ]);

        $leave = LeaveApplication::create(array_merge($validated, ['status' => 'pending']));
        AuditLog::log('applied_leave', $leave, null, $leave->toArray());

        return redirect()->back()->with('success', 'Leave application submitted.');
    }

    public function updateLeaveStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leave = LeaveApplication::findOrFail($id);
        $leave->update([
            'status' => $request->status,
            'approved_by' => Auth::id(),
            'actioned_at' => now(),
        ]);

        AuditLog::log('actioned_leave', $leave, null, ['status' => $request->status]);

        return redirect()->back()->with('success', "Leave application {$request->status}.");
    }

    // --- PAYROLL & PAYSLIPS ---
    public function payroll(Request $request)
    {
        $payrollRuns = PayrollRun::with(['payslips.employee', 'processor', 'journalEntry'])->latest()->paginate(5);
        $employees = Employee::where('status', 'active')->get();

        return view('pages.hr.payroll', compact('payrollRuns', 'employees'));
    }

    public function processPayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2035',
        ]);

        $month = (int) $request->month;
        $year = (int) $request->year;

        $existing = PayrollRun::where('month', $month)->where('year', $year)->first();
        if ($existing) {
            return redirect()->back()->with('error', "Payroll for {$existing->month_name} {$year} has already been processed.");
        }

        return DB::transaction(function () use ($month, $year) {
            $employees = Employee::where('status', 'active')->get();

            if ($employees->isEmpty()) {
                return redirect()->back()->with('error', 'No active employees to run payroll for.');
            }

            $totalGross = 0;
            $totalDeductions = 0;
            $totalNet = 0;

            $payrollRun = PayrollRun::create([
                'month' => $month,
                'year' => $year,
                'total_gross' => 0,
                'total_deductions' => 0,
                'total_net' => 0,
                'status' => 'processed',
                'processed_by' => Auth::id(),
            ]);

            foreach ($employees as $emp) {
                $gross = (float) $emp->gross_salary;
                $pf = round((float) $emp->basic_salary * 0.12, 2);
                $pt = 200.00; // Maharashtra standard professional tax
                $tds = round($gross * 0.05, 2); // Standard 5% TDS assumption
                $ded = $pf + $pt + $tds;
                $net = $gross - $ded;

                $totalGross += $gross;
                $totalDeductions += $ded;
                $totalNet += $net;

                Payslip::create([
                    'payroll_run_id' => $payrollRun->id,
                    'employee_id' => $emp->id,
                    'basic_pay' => $emp->basic_salary,
                    'hra' => $emp->hra,
                    'special_allowance' => $emp->special_allowance,
                    'gross_salary' => $gross,
                    'pf_deduction' => $pf,
                    'esi_deduction' => 0.00,
                    'pt_deduction' => $pt,
                    'tds_deduction' => $tds,
                    'total_deductions' => $ded,
                    'net_salary' => $net,
                    'payment_status' => 'paid',
                ]);
            }

            $payrollRun->update([
                'total_gross' => $totalGross,
                'total_deductions' => $totalDeductions,
                'total_net' => $totalNet,
            ]);

            // Automatically post Journal Entry to General Ledger
            $salExpenseAcc = ChartOfAccount::where('code', '6100')->first() ?? ChartOfAccount::where('subtype', 'payroll')->where('type', 'expense')->first();
            $bankAcc = ChartOfAccount::where('code', '1110')->first() ?? ChartOfAccount::where('subtype', 'bank')->first();
            $pfPayableAcc = ChartOfAccount::where('code', '2400')->first();

            if ($salExpenseAcc && $bankAcc) {
                $jeNumber = 'JV-PAYROLL-' . date('Ym', mktime(0, 0, 0, $month, 1, $year));
                $je = JournalEntry::create([
                    'entry_number' => $jeNumber,
                    'entry_date' => now()->toDateString(),
                    'reference' => "Payroll {$payrollRun->month_name} {$year}",
                    'description' => "Monthly salary disbursement for {$employees->count()} employees",
                    'source_type' => 'payroll',
                    'source_id' => $payrollRun->id,
                    'status' => 'posted',
                    'posted_at' => now(),
                    'posted_by' => Auth::id(),
                    'created_by' => Auth::id(),
                ]);

                // Debit Salary Expense (Gross)
                $je->items()->create([
                    'account_id' => $salExpenseAcc->id,
                    'debit' => $totalGross,
                    'credit' => 0,
                    'description' => 'Gross Employee Salaries',
                ]);

                // Credit Bank Account (Net)
                $je->items()->create([
                    'account_id' => $bankAcc->id,
                    'debit' => 0,
                    'credit' => $totalNet,
                    'description' => 'Net salary bank disbursement',
                ]);

                // Credit Deductions Payable
                if ($totalDeductions > 0 && $pfPayableAcc) {
                    $je->items()->create([
                        'account_id' => $pfPayableAcc->id,
                        'debit' => 0,
                        'credit' => $totalDeductions,
                        'description' => 'Statutory Deductions (PF, PT, TDS) Payable',
                    ]);
                }

                $payrollRun->update(['journal_entry_id' => $je->id]);
            }

            AuditLog::log('processed_payroll', $payrollRun, null, $payrollRun->toArray());

            return redirect()->back()->with('success', "Payroll for {$payrollRun->month_name} {$year} processed successfully. Payslips generated and Journal Entry posted.");
        });
    }
}
