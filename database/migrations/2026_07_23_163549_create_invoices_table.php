<?php

use App\Models\Child;
use App\PaymentStatus;
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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Child::class)->constrained()->onDelete('cascade');
            $table->integer('invoice_sequence');
            $table->integer('invoice_month');
            $table->integer('invoice_year');
            $table->integer('billing_month');
            $table->integer('billing_year');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('child_first_name');
            $table->string('child_last_name');
            $table->string('child_pesel', 11);
            $table->decimal('total_amount', 8, 2);
            $table->string('payment_status')->default(PaymentStatus::UNPAID->value);
            $table->timestamp('paid_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
