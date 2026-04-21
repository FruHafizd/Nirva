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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->dateTime('transaction_date')->index();
            $table->decimal('subtotal', 15, 2);
            $table->string('discount_type', 10)->nullable(); // percentage, fixed
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0); // e.g. 11.00
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);
            $table->string('payment_method', 30)->index(); // cash, qris, etc.
            $table->string('payment_reference', 100)->nullable();
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('completed')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
