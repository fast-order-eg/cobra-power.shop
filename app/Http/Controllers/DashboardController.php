<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Category;
use App\Models\ExpenseCategory;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // 1. Sales Statistics
        $todaySales = Invoice::whereDate('invoice_date', $today)->sum('total_amount');
        $monthSales = Invoice::where('invoice_date', '>=', $thisMonth)->sum('total_amount');
        $allSales = Invoice::sum('total_amount');

        $taxInvoicesCount = Invoice::taxInvoices()->count();
        $taxInvoicesTotal = Invoice::taxInvoices()->sum('total_amount');
        $nonTaxInvoicesCount = Invoice::nonTaxInvoices()->count();
        $nonTaxInvoicesTotal = Invoice::nonTaxInvoices()->sum('total_amount');

        // 2. Cost and Profit Calculation
        $monthCOGS = Invoice::where('invoice_date', '>=', $thisMonth)->sum('total_cost');
        $monthGrossProfit = $monthSales - $monthCOGS;

        // 3. Expenses Statistics
        $monthExpenses = Expense::where('expense_date', '>=', $thisMonth)->sum('amount');
        $allExpenses = Expense::sum('amount');
        $monthNetProfit = $monthGrossProfit - $monthExpenses;

        // 4. Counts
        $totalProducts = Product::where('is_active', true)->count();
        $lowStockProducts = Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->get();

        // 5. Recent Invoices
        $recentInvoices = Invoice::with('customer')
            ->latest('id')
            ->take(6)
            ->get();

        // 6. Expense Breakdown by Category (Current Month)
        $expenseCategories = ExpenseCategory::withSum(['expenses' => function ($q) use ($thisMonth) {
            $q->where('expense_date', '>=', $thisMonth);
        }], 'amount')->get();

        // 7. Monthly Sales Chart Data (Last 6 Months)
        $chartMonths = [];
        $chartSalesData = [];
        $chartExpensesData = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $mLabel = $mStart->translatedFormat('F Y');

            $mSales = (float)Invoice::whereBetween('invoice_date', [$mStart->toDateString(), $mEnd->toDateString()])->sum('total_amount');
            $mExp = (float)Expense::whereBetween('expense_date', [$mStart->toDateString(), $mEnd->toDateString()])->sum('amount');

            $chartMonths[] = $mLabel;
            $chartSalesData[] = $mSales;
            $chartExpensesData[] = $mExp;
        }

        return view('dashboard.index', compact(
            'todaySales',
            'monthSales',
            'allSales',
            'taxInvoicesCount',
            'taxInvoicesTotal',
            'nonTaxInvoicesCount',
            'nonTaxInvoicesTotal',
            'monthCOGS',
            'monthGrossProfit',
            'monthExpenses',
            'allExpenses',
            'monthNetProfit',
            'totalProducts',
            'lowStockProducts',
            'recentInvoices',
            'expenseCategories',
            'chartMonths',
            'chartSalesData',
            'chartExpensesData'
        ));
    }
}
