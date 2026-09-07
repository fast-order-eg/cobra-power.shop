<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('مؤسسة قوة الكوبرا للأدوات الصحية والسباكة');
            $table->string('tax_number')->nullable()->default('123456789');
            $table->string('commercial_registry')->nullable();
            $table->string('phone')->nullable()->default('+962 7 9000 0000');
            $table->string('email')->nullable();
            $table->string('address')->nullable()->default('عمان - المملكة الأردنية الهاشمية');
            $table->string('currency_name')->default('دينار أردني');
            $table->string('currency_symbol')->default('د.أ');
            $table->decimal('tax_rate', 5, 2)->default(16.00); // 16% Jordan Sales Tax
            $table->boolean('tax_enabled_default')->default(true);
            $table->text('invoice_footer_text')->nullable()->default('شكراً لتعاملكم معنا - البضاعة المباعة لا ترد ولا تستبدل بعد 3 أيام');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });

        // 2. Categories Table (الأصناف)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Products Table (المنتجات)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->string('barcode')->nullable();
            $table->string('unit')->default('قطعة'); // قطعة، طقم، حبة، متر
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('purchase_price', 12, 3)->default(0.000); // سعر الشراء الأساسي
            $table->decimal('customs_cost_per_unit', 12, 3)->default(0.000); // نصيب القطعة من الجمارك والشحن
            $table->decimal('actual_cost', 12, 3)->default(0.000); // التكلفة الفعلية = الشراء + الجمارك
            $table->decimal('selling_price', 12, 3)->default(0.000); // سعر البيع للجمهور
            $table->decimal('min_selling_price', 12, 3)->nullable(); // أقل سعر بيع مسموح به
            $table->decimal('stock_quantity', 10, 2)->default(0.00); // الكمية المتوفرة بالمستودع
            $table->integer('min_stock_alert')->default(5); // حد التنبيه عند نقص المخزون
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Customs & Landed Cost Shipments (شحنات الاستيراد واحتساب كلفة الجمارك)
        Schema::create('customs_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->string('supplier_name');
            $table->date('shipment_date');
            $table->string('origin_country')->nullable();
            $table->decimal('total_goods_cost', 12, 3)->default(0.000); // كلفة البضاعة في الفاتورة
            $table->decimal('customs_fees', 12, 3)->default(0.000); // الرسوم الجمركية والضرائب
            $table->decimal('shipping_fees', 12, 3)->default(0.000); // أجور الشحن والنقل
            $table->decimal('clearance_fees', 12, 3)->default(0.000); // أتعاب التخليص الجمركي
            $table->decimal('other_fees', 12, 3)->default(0.000); // مصاريف عمال ومستندات أخرى
            $table->decimal('total_landed_cost', 12, 3)->default(0.000); // الكلفة الإجمالية للشحنة
            $table->enum('status', ['draft', 'applied'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Customs Shipment Items
        Schema::create('customs_shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customs_shipment_id')->constrained('customs_shipments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_purchase_price', 12, 3);
            $table->decimal('allocated_customs_cost', 12, 3)->default(0.000); // نصيب الوحدة من المصاريف الجمركية
            $table->decimal('final_unit_cost', 12, 3); // الكلفة النهائية للوحدة
            $table->decimal('total_item_cost', 12, 3);
            $table->timestamps();
        });

        // 6. Customers Table (العملاء والذمم)
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('national_id')->nullable();
            $table->string('address')->nullable();
            $table->decimal('current_balance', 12, 3)->default(0.000);
            $table->enum('customer_type', ['individual', 'company'])->default('individual');
            $table->timestamps();
        });

        // 7. Invoices Table (المبيعات والفواتير الضريبية وغير الضريبية)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->enum('invoice_type', ['tax', 'non_tax'])->default('tax'); // ضريبية أو غير ضريبية
            $table->date('invoice_date');
            $table->enum('payment_method', ['cash', 'card', 'credit', 'bank'])->default('cash');
            $table->decimal('subtotal', 12, 3)->default(0.000); // المجموع قبل الضريبة
            $table->decimal('discount_amount', 12, 3)->default(0.000);
            $table->decimal('tax_rate', 5, 2)->default(0.00); // 16% للضريبية و 0 لغير الضريبية
            $table->decimal('tax_amount', 12, 3)->default(0.000);
            $table->decimal('total_amount', 12, 3)->default(0.000); // المجموع الصافي بعد الضريبة والخصم
            $table->decimal('paid_amount', 12, 3)->default(0.000);
            $table->decimal('remaining_amount', 12, 3)->default(0.000);
            $table->decimal('total_cost', 12, 3)->default(0.000); // كلفة البضاعة المباعة COGS
            $table->decimal('profit_margin', 12, 3)->default(0.000); // هامش الربح التقريبي
            $table->text('qr_payload')->nullable(); // كود QR المشفر للضريبة الأردنية
            $table->text('qr_image_data')->nullable();
            $table->enum('jordan_tax_status', ['not_sent', 'pending', 'synced', 'error'])->default('not_sent');
            $table->string('jordan_tax_invoice_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 8. Invoice Items Table
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 12, 3)->default(0.000);
            $table->decimal('unit_price', 12, 3)->default(0.000);
            $table->decimal('discount', 12, 3)->default(0.000);
            $table->decimal('tax_amount', 12, 3)->default(0.000);
            $table->decimal('total_price', 12, 3)->default(0.000);
            $table->decimal('total_cost', 12, 3)->default(0.000);
            $table->timestamps();
        });

        // 9. Inventory Logs / Adjustments Table (حركات المخزن)
        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('type', ['purchase', 'sale', 'adjustment', 'damage', 'return']);
            $table->decimal('quantity_change', 10, 2);
            $table->decimal('quantity_before', 10, 2);
            $table->decimal('quantity_after', 10, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 10. Employees Table (الموظفين والعمال)
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('national_id')->nullable();
            $table->string('job_title'); // محاسب، مندوب، أمين مستودع، سائق ديزل، عامل تحميل
            $table->string('phone')->nullable();
            $table->decimal('basic_salary', 12, 3)->default(0.000);
            $table->date('hire_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 11. Employee Advances Table (السلف)
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('amount', 12, 3);
            $table->date('advance_date');
            $table->enum('status', ['pending', 'deducted', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 12. Salary Slips Table (مسير الرواتب)
        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('salary_month'); // e.g. 2026-08
            $table->decimal('basic_salary', 12, 3);
            $table->decimal('advances_deducted', 12, 3)->default(0.000);
            $table->decimal('bonuses', 12, 3)->default(0.000);
            $table->decimal('deductions', 12, 3)->default(0.000);
            $table->decimal('net_salary', 12, 3);
            $table->date('payment_date')->nullable();
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 13. Expense Categories (أقسام المصروفات: ديزل، عمال، طعام، سلف، مشتريات، صيانة، أخرى)
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // 14. Expenses Table (المصروفات اليومية والتشغيلية)
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('amount', 12, 3);
            $table->date('expense_date');
            $table->string('payment_method')->default('cash');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('salary_slips');
        Schema::dropIfExists('employee_advances');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customs_shipment_items');
        Schema::dropIfExists('customs_shipments');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('settings');
    }
};
