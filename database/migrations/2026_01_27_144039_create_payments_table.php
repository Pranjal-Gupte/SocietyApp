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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique(); // PAY-2026-0001
            $table->foreignId('flat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Flat owner/resident
            $table->enum('payment_type', ['maintenance', 'water', 'parking', 'penalty', 'other'])->default('maintenance');
            $table->string('title'); // e.g., "Maintenance Bill - January 2026"
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2); // Bill amount
            $table->decimal('paid_amount', 10, 2)->default(0); // Amount paid
            $table->decimal('due_amount', 10, 2); // Remaining amount
            $table->date('bill_date'); // When bill was generated
            $table->date('due_date'); // Payment deadline
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['online', 'cash', 'cheque', 'bank_transfer', 'upi', 'other'])->nullable();
            $table->string('transaction_id')->nullable(); // Reference number
            $table->date('payment_date')->nullable(); // When payment was made
            $table->text('notes')->nullable(); // Admin notes
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); // Admin who created
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete(); // Who received payment
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'due_date']);
            $table->index('flat_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};