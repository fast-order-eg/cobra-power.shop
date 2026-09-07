<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\SalarySlip;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['advances' => fn($q) => $q->latest()])
            ->withCount(['advances' => fn($q) => $q->where('status', 'pending')])
            ->get();

        $totalBasicSalaries = $employees->where('is_active', true)->sum('basic_salary');
        $totalPendingAdvances = EmployeeAdvance::where('status', 'pending')->sum('amount');

        return view('employees.index', compact('employees', 'totalBasicSalaries', 'totalPendingAdvances'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'national_id' => 'nullable|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'hire_date' => 'nullable|date',
        ]);

        Employee::create([
            'name' => $validated['name'],
            'job_title' => $validated['job_title'],
            'phone' => $validated['phone'] ?? null,
            'national_id' => $validated['national_id'] ?? null,
            'basic_salary' => (float)$validated['basic_salary'],
            'hire_date' => $validated['hire_date'] ?? now(),
            'is_active' => true,
        ]);

        return redirect()->route('employees.index')->with('success', 'تم تسجيل الموظف/العامل بنجاح.');
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'job_title' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'basic_salary' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $employee->update([
            'name' => $validated['name'],
            'job_title' => $validated['job_title'],
            'phone' => $validated['phone'] ?? null,
            'basic_salary' => (float)$validated['basic_salary'],
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        return redirect()->route('employees.index')->with('success', 'تم تعديل بيانات الموظف بنجاح.');
    }

    public function addAdvance(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.5',
            'advance_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $employee) {
            EmployeeAdvance::create([
                'employee_id' => $employee->id,
                'amount' => (float)$validated['amount'],
                'advance_date' => $validated['advance_date'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? 'سلفة على الراتب',
            ]);

            // Automatically record in Expenses under 'employee_advances' category
            $advCat = ExpenseCategory::firstOrCreate(['code' => 'employee_advances'], ['name' => 'سلف موظفين', 'is_system' => true]);
            Expense::create([
                'expense_category_id' => $advCat->id,
                'employee_id' => $employee->id,
                'amount' => (float)$validated['amount'],
                'expense_date' => $validated['advance_date'],
                'payment_method' => 'cash',
                'title' => "سلفة موظف: {$employee->name}",
                'notes' => $validated['notes'] ?? 'سلفة نقدية من الصندوق',
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('employees.index')->with('success', "تم صرف سلفة بقيمة {$validated['amount']} د.أ للموظف ({$employee->name}) وقيدها في المصروفات.");
    }

    public function generateSalarySlip(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'salary_month' => 'required|string|max:20', // e.g. 2026-08
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $basic = (float)$employee->basic_salary;
        $pendingAdvances = (float)$employee->advances()->where('status', 'pending')->sum('amount');
        $bonuses = (float)($validated['bonuses'] ?? 0);
        $deductions = (float)($validated['deductions'] ?? 0);

        $netSalary = max(0, $basic + $bonuses - $pendingAdvances - $deductions);

        DB::transaction(function () use ($employee, $validated, $basic, $pendingAdvances, $bonuses, $deductions, $netSalary) {
            // Update advances status to deducted
            $employee->advances()->where('status', 'pending')->update(['status' => 'deducted']);

            SalarySlip::create([
                'employee_id' => $employee->id,
                'salary_month' => $validated['salary_month'],
                'basic_salary' => $basic,
                'advances_deducted' => $pendingAdvances,
                'bonuses' => $bonuses,
                'deductions' => $deductions,
                'net_salary' => $netSalary,
                'payment_date' => $validated['payment_date'],
                'payment_status' => 'paid',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Log net salary payment in expenses
            $laborCat = ExpenseCategory::firstOrCreate(['code' => 'labor'], ['name' => 'عمال ومياومة ورواتب', 'is_system' => true]);
            Expense::create([
                'expense_category_id' => $laborCat->id,
                'employee_id' => $employee->id,
                'amount' => $netSalary,
                'expense_date' => $validated['payment_date'],
                'payment_method' => 'cash',
                'title' => "صرف صافي راتب شهر {$validated['salary_month']} للموظف: {$employee->name}",
                'notes' => "الراتب الأساسي: {$basic} د.أ - خصم سلف: {$pendingAdvances} د.أ - صافي مدفوع: {$netSalary} د.أ",
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('employees.index')->with('success', "تم صرف وتسوية مسير الراتب لشهر {$validated['salary_month']} للموظف ({$employee->name}) وخصم جميع السلف المعلقة تلقائياً.");
    }

    public function destroy(Employee $employee)
    {
        // Delete advances and salary slips first
        $employee->advances()->delete();
        $employee->salarySlips()->delete();
        $employee->delete();

        return redirect()->route('employees.index')->with('success', "تم حذف الموظف ({$employee->name}) وجميع سجلاته بنجاح.");
    }
}
