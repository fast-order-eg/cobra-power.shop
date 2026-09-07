<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
        ]);

        $code = Str::slug($validated['name'], '_');
        if (empty($code) || ExpenseCategory::where('code', $code)->exists()) {
            $code = 'cat_' . time() . '_' . rand(10, 99);
        }

        ExpenseCategory::create([
            'name' => $validated['name'],
            'code' => $code,
            'is_system' => false,
        ]);

        return redirect()->route('expenses.index')->with('success', "تمت إضافة قسم المصروف ({$validated['name']}) بنجاح.");
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name,' . $expenseCategory->id,
        ]);

        $expenseCategory->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('expenses.index')->with('success', "تم تعديل اسم القسم إلى ({$validated['name']}) بنجاح.");
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        // 1. Find or create the default fallback category
        $fallbackCategory = ExpenseCategory::firstOrCreate(
            ['code' => 'other'],
            ['name' => 'مصروفات أخرى متنوعة', 'is_system' => false]
        );

        // If trying to delete the fallback category itself
        if ($expenseCategory->id === $fallbackCategory->id) {
            $anotherCategory = ExpenseCategory::where('id', '!=', $expenseCategory->id)->first();
            if (!$anotherCategory) {
                return redirect()->route('expenses.index')->with('error', 'يجب الإبقاء على قسم مصروفات واحد على الأقل في النظام.');
            }
            $fallbackCategory = $anotherCategory;
        }

        $expenseCount = $expenseCategory->expenses()->count();
        $name = $expenseCategory->name;

        // 2. Reassign all existing expenses safely to preserve history & financial balances
        if ($expenseCount > 0) {
            $expenseCategory->expenses()->update([
                'expense_category_id' => $fallbackCategory->id
            ]);
        }

        // 3. Delete the category
        $expenseCategory->delete();

        $msg = "تم حذف قسم ({$name}) بنجاح.";
        if ($expenseCount > 0) {
            $msg .= " وتم نقل جميع السندات والمصروفات التابعة له وعددهم ({$expenseCount}) سند تلقائياً إلى قسم ({$fallbackCategory->name}) للحفاظ التام على السجلات والمبالغ المالية دون أي حذف.";
        }

        return redirect()->route('expenses.index')->with('success', $msg);
    }
}
