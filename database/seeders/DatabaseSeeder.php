<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InventoryLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@cobra-power.shop'],
            [
                'name' => 'مدير النظام (قوة الكوبرا)',
                'password' => Hash::make('12345678'),
            ]
        );

        // 2. Default Company Settings
        $setting = Setting::instance();

        // 3. Categories (الأصناف)
        $catMixers = Category::create([
            'name' => 'بطاريات وخلاطات',
            'description' => 'بطاريات مغاسل، مجالي، خلاطات دوش وشورات',
            'is_active' => true,
        ]);

        $catSinks = Category::create([
            'name' => 'مغاسل ومجالي',
            'description' => 'مجالي ستانلس ستيل فندقية وغذائية، مغاسل خزف ورخام',
            'is_active' => true,
        ]);

        $catShowers = Category::create([
            'name' => 'شاورات وكبائن',
            'description' => 'شور جيت، قواطع شور سيكوريت، أعمدة مساج',
            'is_active' => true,
        ]);

        $catHeaters = Category::create([
            'name' => 'سخانات مياه',
            'description' => 'سخانات مياه كهربائية وخزانات سيروزا وخزف سعودي',
            'is_active' => true,
        ]);

        $catAccessories = Category::create([
            'name' => 'إكسسوارات وقطع صحية',
            'description' => 'سيفونات، محابس زاوية، خراطيم، وقطع سباكة',
            'is_active' => true,
        ]);

        // 4. Products (المنتجات مع كلفة الجمارك)
        $products = [
            [
                'category_id' => $catShowers->id,
                'name' => 'شور جيت كروم 5 حركات توربو',
                'code' => 'PRD-101',
                'barcode' => '6281001001',
                'unit' => 'طقم',
                'description' => 'طقم شور جيت كامل رأسية كروم مضاد للتكلس مع بربيش ومسكة',
                'purchase_price' => 18.500,
                'customs_cost_per_unit' => 3.500,
                'actual_cost' => 22.000,
                'selling_price' => 32.500,
                'min_selling_price' => 28.000,
                'stock_quantity' => 45,
                'min_stock_alert' => 10,
            ],
            [
                'category_id' => $catMixers->id,
                'name' => 'بطارية مغسلة تركي كروم عالية',
                'code' => 'PRD-102',
                'barcode' => '6281001002',
                'unit' => 'حبة',
                'description' => 'خلاط مغسلة ديكور عالي للمغاسل السطحية نخب أول كفالة 5 سنوات',
                'purchase_price' => 24.000,
                'customs_cost_per_unit' => 4.000,
                'actual_cost' => 28.000,
                'selling_price' => 42.000,
                'min_selling_price' => 36.000,
                'stock_quantity' => 30,
                'min_stock_alert' => 8,
            ],
            [
                'category_id' => $catMixers->id,
                'name' => 'بطارية مجلى جداري سحب نخب أول',
                'code' => 'PRD-103',
                'barcode' => '6281001003',
                'unit' => 'حبة',
                'description' => 'خلاط مجلى جداري مع رأس مرن وسحب لغسيل الأواني والمجالي',
                'purchase_price' => 28.000,
                'customs_cost_per_unit' => 4.500,
                'actual_cost' => 32.500,
                'selling_price' => 48.000,
                'min_selling_price' => 42.000,
                'stock_quantity' => 25,
                'min_stock_alert' => 5,
            ],
            [
                'category_id' => $catSinks->id,
                'name' => 'مجلى غذائي / فندقي ستانلس ستيل حوضين 120سم',
                'code' => 'PRD-104',
                'barcode' => '6281001004',
                'unit' => 'قطعة',
                'description' => 'مجلى ستانلس ستيل غذائي عيار 304 سميك مناسب للمطابخ والمطاعم حوضين مع صفاية',
                'purchase_price' => 65.000,
                'customs_cost_per_unit' => 12.000,
                'actual_cost' => 77.000,
                'selling_price' => 110.000,
                'min_selling_price' => 98.000,
                'stock_quantity' => 15,
                'min_stock_alert' => 4,
            ],
            [
                'category_id' => $catShowers->id,
                'name' => 'قاطع شور سيكوريت 8 ملم مسطرة ستانلس',
                'code' => 'PRD-105',
                'barcode' => '6281001005',
                'unit' => 'طقم',
                'description' => 'لوح زجاجي سيكوريت معالج ضد الكسر مع طقم مساطر وتثبيت ستانلس مقاوم للصدأ',
                'purchase_price' => 80.000,
                'customs_cost_per_unit' => 15.000,
                'actual_cost' => 95.000,
                'selling_price' => 145.000,
                'min_selling_price' => 130.000,
                'stock_quantity' => 10,
                'min_stock_alert' => 3,
            ],
            [
                'category_id' => $catHeaters->id,
                'name' => 'سخان مياه سعودي سيروزا 50 لتر أصلي',
                'code' => 'PRD-106',
                'barcode' => '6281001006',
                'unit' => 'حبة',
                'description' => 'سخان سيروزا الكهربائي السعودي أصلي كفالة 5 سنوات مطلي بالمينا عازل حراري فائق',
                'purchase_price' => 58.000,
                'customs_cost_per_unit' => 7.000,
                'actual_cost' => 65.000,
                'selling_price' => 85.000,
                'min_selling_price' => 78.000,
                'stock_quantity' => 20,
                'min_stock_alert' => 5,
            ],
        ];

        $createdProducts = [];
        foreach ($products as $pData) {
            $prod = Product::create($pData);
            $createdProducts[] = $prod;

            // Log Initial Stock
            InventoryLog::create([
                'product_id' => $prod->id,
                'type' => 'purchase',
                'quantity_change' => $prod->stock_quantity,
                'quantity_before' => 0,
                'quantity_after' => $prod->stock_quantity,
                'reference_type' => 'رصيد افتتاحي',
                'notes' => 'رصيد افتتاحي تم إدخاله للنظام',
                'user_id' => $user->id,
            ]);
        }

        // 5. Expense Categories (أقسام المصروفات الأردنية)
        $expenseCategories = [
            ['name' => 'ديزل ومحروقات', 'code' => 'diesel', 'is_system' => true],
            ['name' => 'عمال ومياومة', 'code' => 'labor', 'is_system' => true],
            ['name' => 'طعام وإعاشة عمال', 'code' => 'food', 'is_system' => true],
            ['name' => 'سلف موظفين', 'code' => 'employee_advances', 'is_system' => true],
            ['name' => 'فواتير مشتريات وتوريد', 'code' => 'purchases', 'is_system' => true],
            ['name' => 'صيانة ونقليات ورسوم', 'code' => 'maintenance', 'is_system' => true],
            ['name' => 'مصروفات أخرى متنوعة', 'code' => 'other', 'is_system' => true],
        ];

        $createdCategories = [];
        foreach ($expenseCategories as $ec) {
            $createdCategories[$ec['code']] = ExpenseCategory::create($ec);
        }

        // 6. Employees (الموظفين والعمال)
        $emp1 = Employee::create([
            'name' => 'خالد العمري',
            'job_title' => 'أمين مستودع ومبيعات',
            'phone' => '+962 7 9111 2233',
            'basic_salary' => 450.000,
            'hire_date' => '2025-01-10',
            'is_active' => true,
        ]);

        $emp2 = Employee::create([
            'name' => 'أحمد المجالي',
            'job_title' => 'سائق سيارة توزيع ونقل',
            'phone' => '+962 7 9444 5566',
            'basic_salary' => 380.000,
            'hire_date' => '2025-03-01',
            'is_active' => true,
        ]);

        $emp3 = Employee::create([
            'name' => 'محمود الحنيطي',
            'job_title' => 'عامل تحميل ومساعد مستودع',
            'phone' => '+962 7 9777 8899',
            'basic_salary' => 320.000,
            'hire_date' => '2025-06-15',
            'is_active' => true,
        ]);

        // 7. Employee Advances (سلفيات)
        EmployeeAdvance::create([
            'employee_id' => $emp1->id,
            'amount' => 50.000,
            'advance_date' => now()->subDays(5),
            'status' => 'pending',
            'notes' => 'سلفة على راتب الشهر الحالي لحالة خاصة',
        ]);

        // 8. Sample Expenses (مصروفات الديزل والعمال والطعام)
        Expense::create([
            'expense_category_id' => $createdCategories['diesel']->id,
            'employee_id' => $emp2->id,
            'amount' => 45.000,
            'expense_date' => now()->subDays(2),
            'payment_method' => 'cash',
            'title' => 'تعبئة ديزل سيارة توزيع البضائع',
            'notes' => 'محطة المناصير - خط توزيع عمان الغربية والزرقاء',
            'created_by' => $user->id,
        ]);

        Expense::create([
            'expense_category_id' => $createdCategories['food']->id,
            'amount' => 18.500,
            'expense_date' => now()->subDays(1),
            'payment_method' => 'cash',
            'title' => 'وجبات غداء لعمال المستودع والسائقين',
            'notes' => 'طعام يوم تنزيل شحنة السخانات',
            'created_by' => $user->id,
        ]);

        Expense::create([
            'expense_category_id' => $createdCategories['labor']->id,
            'amount' => 60.000,
            'expense_date' => now()->subDays(3),
            'payment_method' => 'cash',
            'title' => 'يوميات عمال عتالة وتعتيق خارجيين',
            'notes' => 'تنزيل وترتيب حاوية المجالي والشورات في المستودع',
            'created_by' => $user->id,
        ]);

        // 9. Customers (العملاء)
        $cust1 = Customer::create([
            'name' => 'شركة المقاولات الأردنية الحديثة',
            'phone' => '+962 7 8888 9900',
            'tax_number' => '100234567',
            'address' => 'عمان - شارع مكة',
            'customer_type' => 'company',
            'current_balance' => 0.000,
        ]);

        $cust2 = Customer::create([
            'name' => 'المهندس طارق الزعبي',
            'phone' => '+962 7 7777 6655',
            'address' => 'إربد - الحي الشرقي',
            'customer_type' => 'individual',
            'current_balance' => 0.000,
        ]);

        // 10. Sample Invoices (Tax Invoice + Non-Tax Invoice)
        // Invoice 1: Tax Invoice
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-' . date('Y') . '-0001',
            'customer_id' => $cust1->id,
            'customer_name' => $cust1->name,
            'invoice_type' => 'tax',
            'invoice_date' => now()->subDays(2),
            'payment_method' => 'bank',
            'subtotal' => 240.000,
            'discount_amount' => 10.000,
            'tax_rate' => 16.00,
            'tax_amount' => 36.800, // 16% of 230
            'total_amount' => 266.800,
            'paid_amount' => 266.800,
            'remaining_amount' => 0.000,
            'total_cost' => 184.000,
            'profit_margin' => 46.000,
            'created_by' => $user->id,
        ]);
        $inv1->generateJordanQrPayload();
        $inv1->save();

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'product_id' => $createdProducts[5]->id, // سخان سيروزا
            'product_name' => $createdProducts[5]->name,
            'quantity' => 2,
            'unit_cost' => $createdProducts[5]->actual_cost,
            'unit_price' => 85.000,
            'total_price' => 170.000,
            'total_cost' => 130.000,
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'product_id' => $createdProducts[1]->id, // بطارية مغسلة تركي
            'product_name' => $createdProducts[1]->name,
            'quantity' => 2,
            'unit_cost' => $createdProducts[1]->actual_cost,
            'unit_price' => 35.000,
            'total_price' => 70.000,
            'total_cost' => 56.000,
        ]);

        // Invoice 2: Non-Tax Cash Invoice
        $inv2 = Invoice::create([
            'invoice_number' => 'INV-' . date('Y') . '-0002',
            'customer_id' => $cust2->id,
            'customer_name' => $cust2->name,
            'invoice_type' => 'non_tax',
            'invoice_date' => now()->subDay(),
            'payment_method' => 'cash',
            'subtotal' => 80.500,
            'discount_amount' => 0.000,
            'tax_rate' => 0.00,
            'tax_amount' => 0.000,
            'total_amount' => 80.500,
            'paid_amount' => 80.500,
            'remaining_amount' => 0.000,
            'total_cost' => 54.500,
            'profit_margin' => 26.000,
            'created_by' => $user->id,
        ]);
        $inv2->save();

        InvoiceItem::create([
            'invoice_id' => $inv2->id,
            'product_id' => $createdProducts[0]->id, // شور جيت
            'product_name' => $createdProducts[0]->name,
            'quantity' => 1,
            'unit_cost' => $createdProducts[0]->actual_cost,
            'unit_price' => 32.500,
            'total_price' => 32.500,
            'total_cost' => 22.000,
        ]);

        InvoiceItem::create([
            'invoice_id' => $inv2->id,
            'product_id' => $createdProducts[2]->id, // بطارية مجلى
            'product_name' => $createdProducts[2]->name,
            'quantity' => 1,
            'unit_cost' => $createdProducts[2]->actual_cost,
            'unit_price' => 48.000,
            'total_price' => 48.000,
            'total_cost' => 32.500,
        ]);
    }
}
