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
            'target_month' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:255',
        ]);

        $targetMonth = !empty($validated['target_month']) ? $validated['target_month'] : date('Y-m');

        DB::transaction(function () use ($validated, $employee, $targetMonth) {
            EmployeeAdvance::create([
                'employee_id' => $employee->id,
                'amount' => (float)$validated['amount'],
                'advance_date' => $validated['advance_date'],
                'target_month' => $targetMonth,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? ("سلفة عن راتب شهر " . $targetMonth),
            ]);

            // Automatically record in Expenses under 'employee_advances' category
            $advCat = ExpenseCategory::firstOrCreate(['code' => 'employee_advances'], ['name' => 'سلف موظفين', 'is_system' => true]);
            Expense::create([
                'expense_category_id' => $advCat->id,
                'employee_id' => $employee->id,
                'amount' => (float)$validated['amount'],
                'expense_date' => $validated['advance_date'],
                'payment_method' => 'cash',
                'title' => "سلفة موظف: {$employee->name} (عن شهر {$targetMonth})",
                'notes' => $validated['notes'] ?? ("سلفة نقدية مخصصة لراتب شهر " . $targetMonth),
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('employees.index')->with('success', "تم صرف سلفة بقيمة {$validated['amount']} د.أ للموظف ({$employee->name}) مخصصة لشهر {$targetMonth} وقيدها في المصروفات.");
    }

    public function generateSalarySlip(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'salary_month' => 'required|string|max:20', // e.g. 2026-09
            'advances_deducted' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $basic = (float)$employee->basic_salary;
        $bonuses = (float)($validated['bonuses'] ?? 0);
        $deductions = (float)($validated['deductions'] ?? 0);
        $advancesDeducted = isset($validated['advances_deducted']) ? (float)$validated['advances_deducted'] : (float)$employee->advances()->where('status', 'pending')->sum('amount');

        $netSalary = max(0, $basic + $bonuses - $advancesDeducted - $deductions);

        DB::transaction(function () use ($employee, $validated, $basic, $advancesDeducted, $bonuses, $deductions, $netSalary) {
            // Deduct pending advances up to advancesDeducted amount
            if ($advancesDeducted > 0) {
                $remToDeduct = $advancesDeducted;
                $pendingAdvances = $employee->advances()->where('status', 'pending')->orderBy('advance_date', 'asc')->get();
                foreach ($pendingAdvances as $adv) {
                    if ($remToDeduct <= 0) break;
                    $advAmt = (float)$adv->amount;
                    if ($advAmt <= $remToDeduct) {
                        $remToDeduct -= $advAmt;
                        $adv->update(['status' => 'deducted']);
                    } else {
                        // Partial advance deduction
                        $remainingAdv = $advAmt - $remToDeduct;
                        $adv->update(['amount' => $remToDeduct, 'status' => 'deducted']);
                        EmployeeAdvance::create([
                            'employee_id' => $employee->id,
                            'amount' => $remainingAdv,
                            'advance_date' => $adv->advance_date,
                            'target_month' => $adv->target_month,
                            'status' => 'pending',
                            'notes' => "المتبقي من سلفة تاريخ " . ($adv->advance_date ? $adv->advance_date->format('Y-m-d') : ''),
                        ]);
                        $remToDeduct = 0;
                    }
                }
            }

            SalarySlip::create([
                'employee_id' => $employee->id,
                'salary_month' => $validated['salary_month'],
                'basic_salary' => $basic,
                'advances_deducted' => $advancesDeducted,
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
                'notes' => "الراتب الأساسي: {$basic} د.أ - خصم سلف: {$advancesDeducted} د.أ - مكافآت: {$bonuses} د.أ - خصومات: {$deductions} د.أ - صافي مدفوع: {$netSalary} د.أ",
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('employees.index')->with('success', "تم صرف وتسوية مسير الراتب لشهر {$validated['salary_month']} للموظف ({$employee->name}) بنجاح.");
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
