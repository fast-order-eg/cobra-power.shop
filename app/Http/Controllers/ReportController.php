<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $period = $request->get('period', 'month'); // today, week, month, year, custom
        $invoiceType = $request->get('invoice_type', 'all'); // tax, non_tax, all

        $query = Invoice::with('customer')->latest('invoice_date');

        // Period filter
        if ($period === 'today') {
            $query->whereDate('invoice_date', Carbon::today());
            $fromDate = Carbon::today()->toDateString();
            $toDate = Carbon::today()->toDateString();
        } elseif ($period === 'week') {
            $query->where('invoice_date', '>=', Carbon::now()->startOfWeek());
            $fromDate = Carbon::now()->startOfWeek()->toDateString();
            $toDate = Carbon::now()->endOfWeek()->toDateString();
        } elseif ($period === 'month') {
            $query->where('invoice_date', '>=', Carbon::now()->startOfMonth());
            $fromDate = Carbon::now()->startOfMonth()->toDateString();
            $toDate = Carbon::now()->endOfMonth()->toDateString();
        } elseif ($period === 'year') {
            $query->where('invoice_date', '>=', Carbon::now()->startOfYear());
            $fromDate = Carbon::now()->startOfYear()->toDateString();
            $toDate = Carbon::now()->endOfYear()->toDateString();
        } elseif ($period === 'custom') {
            $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
            $toDate = $request->get('to_date', Carbon::now()->toDateString());
            $query->whereBetween('invoice_date', [$fromDate, $toDate]);
        }

        // Invoice type filter (ضريبية / غير ضريبية / الإجمالي)
        if ($invoiceType === 'tax') {
            $query->taxInvoices();
        } elseif ($invoiceType === 'non_tax') {
            $query->nonTaxInvoices();
        }

        $invoices = $query->get();

        // Calculate aggregates
        $totalSales = (float)$invoices->sum('total_amount');
        $totalSubtotal = (float)$invoices->sum('subtotal');
        $totalDiscount = (float)$invoices->sum('discount_amount');
        $totalTax = (float)$invoices->sum('tax_amount');
        $totalCost = (float)$invoices->sum('total_cost');
        $totalProfit = (float)$invoices->sum('profit_margin');
        $totalInvoicesCount = $invoices->count();

        // Tax vs Non-tax summary within current period
        $taxInvoicesCount = $invoices->where('invoice_type', 'tax')->count();
        $taxInvoicesTotal = $invoices->where('invoice_type', 'tax')->sum('total_amount');
        $nonTaxInvoicesCount = $invoices->where('invoice_type', 'non_tax')->count();
        $nonTaxInvoicesTotal = $invoices->where('invoice_type', 'non_tax')->sum('total_amount');

        return view('reports.sales', compact(
            'invoices',
            'period',
            'invoiceType',
            'fromDate',
            'toDate',
            'totalSales',
            'totalSubtotal',
            'totalDiscount',
            'totalTax',
            'totalCost',
            'totalProfit',
            'totalInvoicesCount',
            'taxInvoicesCount',
            'taxInvoicesTotal',
            'nonTaxInvoicesCount',
            'nonTaxInvoicesTotal'
        ));
    }

    public function profitLoss(Request $request)
    {
        $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
        $toDate = $request->get('to_date', Carbon::now()->toDateString());

        // 1. Revenue
        $invoices = Invoice::whereBetween('invoice_date', [$fromDate, $toDate])->get();
        $totalSales = (float)$invoices->sum('total_amount');
        $taxAmount = (float)$invoices->sum('tax_amount');
        $netSales = $totalSales - $taxAmount; // Net revenue excluding tax

        // 2. Cost of Goods Sold (COGS)
        $cogs = (float)$invoices->sum('total_cost');
        $grossProfit = $netSales - $cogs;
        $grossMarginPercent = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0;

        // 3. Operating Expenses
        $expenses = Expense::with('category')->whereBetween('expense_date', [$fromDate, $toDate])->get();
        $totalExpenses = (float)$expenses->sum('amount');

        // Breakdown by specific categories
        $dieselExpenses = (float)$expenses->where('category.code', 'diesel')->sum('amount');
        $laborExpenses = (float)$expenses->where('category.code', 'labor')->sum('amount');
        $foodExpenses = (float)$expenses->where('category.code', 'food')->sum('amount');
        $advancesExpenses = (float)$expenses->where('category.code', 'employee_advances')->sum('amount');
        $otherExpenses = $totalExpenses - ($dieselExpenses + $laborExpenses + $foodExpenses + $advancesExpenses);

        // 4. Net Profit
        $netProfit = $grossProfit - $totalExpenses;
        $netMarginPercent = $netSales > 0 ? round(($netProfit / $netSales) * 100, 1) : 0;

        return view('reports.profit_loss', compact(
            'fromDate',
            'toDate',
            'totalSales',
            'taxAmount',
            'netSales',
            'cogs',
            'grossProfit',
            'grossMarginPercent',
            'totalExpenses',
            'dieselExpenses',
            'laborExpenses',
            'foodExpenses',
            'advancesExpenses',
            'otherExpenses',
            'netProfit',
            'netMarginPercent'
        ));
    }

    public function inventory()
    {
        $products = Product::with('category')->where('is_active', true)->get();

        $totalItems = $products->count();
        $totalStockQty = $products->sum('stock_quantity');
        $totalCostValue = $products->sum(fn($p) => (float)$p->stock_quantity * (float)$p->actual_cost);
        $totalSellingValue = $products->sum(fn($p) => (float)$p->stock_quantity * (float)$p->selling_price);
        $expectedProfit = $totalSellingValue - $totalCostValue;

        $lowStockProducts = $products->filter(fn($p) => (float)$p->stock_quantity <= (float)$p->min_stock_alert);

        return view('reports.inventory', compact(
            'products',
            'totalItems',
            'totalStockQty',
            'totalCostValue',
            'totalSellingValue',
            'expectedProfit',
            'lowStockProducts'
        ));
    }

    public function exportSalesExcel(Request $request)
    {
        $period = $request->get('period', 'month');
        $invoiceType = $request->get('invoice_type', 'all');

        $query = Invoice::with('customer')->latest('invoice_date');

        if ($period === 'today') {
            $query->whereDate('invoice_date', Carbon::today());
        } elseif ($period === 'week') {
            $query->where('invoice_date', '>=', Carbon::now()->startOfWeek());
        } elseif ($period === 'month') {
            $query->where('invoice_date', '>=', Carbon::now()->startOfMonth());
        } elseif ($period === 'year') {
            $query->where('invoice_date', '>=', Carbon::now()->startOfYear());
        } elseif ($period === 'custom') {
            $fromDate = $request->get('from_date', Carbon::now()->startOfMonth()->toDateString());
            $toDate = $request->get('to_date', Carbon::now()->toDateString());
            $query->whereBetween('invoice_date', [$fromDate, $toDate]);
        }

        if ($invoiceType === 'tax') {
            $query->taxInvoices();
        } elseif ($invoiceType === 'non_tax') {
            $query->nonTaxInvoices();
        }

        $invoices = $query->get();

        $filename = "sales_report_" . date('Y-m-d_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel Arabic compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'رقم الفاتورة',
                'نوع الفاتورة',
                'تاريخ الإصدار',
                'اسم العميل',
                'طريقة الدفع',
                'المجموع قبل الضريبة (د.أ)',
                'الخصم (د.أ)',
                'ضريبة المبيعات 16% (د.أ)',
                'الصافي النهائي (د.أ)',
                'كلفة البضاعة COGS (د.أ)',
                'هامش الربح (د.أ)',
            ]);

            $totalSub = 0;
            $totalDisc = 0;
            $totalTax = 0;
            $totalNet = 0;
            $totalCost = 0;
            $totalProfit = 0;

            foreach ($invoices as $inv) {
                $totalSub += (float)$inv->subtotal;
                $totalDisc += (float)$inv->discount_amount;
                $totalTax += (float)$inv->tax_amount;
                $totalNet += (float)$inv->total_amount;
                $totalCost += (float)$inv->total_cost;
                $totalProfit += (float)$inv->profit_margin;

                fputcsv($file, [
                    $inv->invoice_number,
                    $inv->invoice_type === 'tax' ? 'ضريبية 16%' : 'غير ضريبية (نقدية)',
                    $inv->invoice_date->format('Y/m/d'),
                    $inv->customer_name ?? 'زبون نقدي',
                    $inv->payment_method_name,
                    number_format($inv->subtotal, 3, '.', ''),
                    number_format($inv->discount_amount, 3, '.', ''),
                    number_format($inv->tax_amount, 3, '.', ''),
                    number_format($inv->total_amount, 3, '.', ''),
                    number_format($inv->total_cost, 3, '.', ''),
                    number_format($inv->profit_margin, 3, '.', ''),
                ]);
            }

            // Summary row
            fputcsv($file, [
                'الإجمالي الكلي',
                '',
                '',
                '',
                '',
                number_format($totalSub, 3, '.', ''),
                number_format($totalDisc, 3, '.', ''),
                number_format($totalTax, 3, '.', ''),
                number_format($totalNet, 3, '.', ''),
                number_format($totalCost, 3, '.', ''),
                number_format($totalProfit, 3, '.', ''),
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportInventoryExcel()
    {
        $products = Product::with('category')->where('is_active', true)->get();

        $filename = "inventory_report_" . date('Y-m-d_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'كود المنتج',
                'الباركود',
                'اسم المنتج',
                'التصنيف / الصنف',
                'الرصيد بالمستودع',
                'الوحدة',
                'سعر الشراء الأساسي (د.أ)',
                'نصيب الجمارك للقطعة (د.أ)',
                'الكلفة الفعلية للقطعة (د.أ)',
                'إجمالي قيمة التكلفة (د.أ)',
                'سعر البيع (د.أ)',
                'إجمالي قيمة البيع (د.أ)',
                'الربح المتوقع بالمخزون (د.أ)',
            ]);

            $totalQty = 0;
            $totalCostVal = 0;
            $totalSellVal = 0;
            $totalExpProfit = 0;

            foreach ($products as $p) {
                $qty = (float)$p->stock_quantity;
                $rowCost = $qty * (float)$p->actual_cost;
                $rowSell = $qty * (float)$p->selling_price;
                $rowProfit = $rowSell - $rowCost;

                $totalQty += $qty;
                $totalCostVal += $rowCost;
                $totalSellVal += $rowSell;
                $totalExpProfit += $rowProfit;

                fputcsv($file, [
                    $p->code,
                    $p->barcode ?? '',
                    $p->name,
                    $p->category->name ?? 'عام',
                    $p->formatted_stock,
                    $p->unit,
                    number_format($p->purchase_price, 3, '.', ''),
                    number_format($p->customs_cost_per_unit, 3, '.', ''),
                    number_format($p->actual_cost, 3, '.', ''),
                    number_format($rowCost, 3, '.', ''),
                    number_format($p->selling_price, 3, '.', ''),
                    number_format($rowSell, 3, '.', ''),
                    number_format($rowProfit, 3, '.', ''),
                ]);
            }

            fputcsv($file, [
                'الإجمالي العام',
                '',
                '',
                '',
                $totalQty,
                'قطعة',
                '',
                '',
                '',
                number_format($totalCostVal, 3, '.', ''),
                '',
                number_format($totalSellVal, 3, '.', ''),
                number_format($totalExpProfit, 3, '.', ''),
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
