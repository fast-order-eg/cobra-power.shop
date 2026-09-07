<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'employee', 'creator'])->latest('expense_date');

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $expenses = $query->paginate(15)->withQueryString();
        $categories = ExpenseCategory::withCount('expenses')->get();
        $employees = Employee::where('is_active', true)->get();

        // Statistics
        $filteredQuery = clone $query;
        $totalExpenses = $filteredQuery->sum('amount');

        // Totals by specific requested categories (Diesel, Labor, Food)
        $dieselTotal = Expense::whereHas('category', fn($q) => $q->where('code', 'diesel'))->sum('amount');
        $laborTotal = Expense::whereHas('category', fn($q) => $q->where('code', 'labor'))->sum('amount');
        $foodTotal = Expense::whereHas('category', fn($q) => $q->where('code', 'food'))->sum('amount');

        return view('expenses.index', compact('expenses', 'categories', 'employees', 'totalExpenses', 'dieselTotal', 'laborTotal', 'foodTotal'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'employee_id' => 'nullable|exists:employees,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('expenses', 'public');
        }

        Expense::create([
            'expense_category_id' => $validated['expense_category_id'],
            'employee_id' => $validated['employee_id'] ?? null,
            'title' => $validated['title'],
            'amount' => (float)$validated['amount'],
            'expense_date' => $validated['expense_date'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'attachment_path' => $attachmentPath,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('expenses.index')->with('success', 'تم تسجيل سند المصروف بنجاح.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->attachment_path && Storage::disk('public')->exists($expense->attachment_path)) {
            Storage::disk('public')->delete($expense->attachment_path);
        }

        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'تم حذف المصروف بنجاح.');
    }

    public function exportExcel(Request $request)
    {
        $query = Expense::with(['category', 'employee', 'creator'])->latest('expense_date');

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $expenses = $query->get();

        $filename = "expenses_report_" . date('Y-m-d_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'رقم السند',
                'التاريخ',
                'قسم المصروف',
                'بيان وعنوان المصروف',
                'المبلغ المصروف (د.أ)',
                'الموظف المرتبط',
                'طريقة الدفع',
                'ملاحظات',
            ]);

            $total = 0;
            foreach ($expenses as $exp) {
                $total += (float)$exp->amount;
                fputcsv($file, [
                    'EXP-' . str_pad($exp->id, 5, '0', STR_PAD_LEFT),
                    $exp->expense_date->format('Y/m/d'),
                    $exp->category->name ?? 'عام',
                    $exp->title,
                    number_format($exp->amount, 3, '.', ''),
                    $exp->employee->name ?? '-',
                    $exp->payment_method === 'cash' ? 'نقداً (كاش)' : ($exp->payment_method === 'bank' ? 'بنكي / شيك' : 'تحويل كليك'),
                    $exp->notes ?? '',
                ]);
            }

            fputcsv($file, [
                'الإجمالي العام',
                '',
                '',
                '',
                number_format($total, 3, '.', ''),
                '',
                '',
                '',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
