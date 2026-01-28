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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('gate_pass_code')->unique(); // GP-2026-0001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Resident who registered
            $table->foreignId('flat_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('visitor_phone');
            $table->string('visitor_email')->nullable();
            $table->enum('visitor_type', ['guest', 'delivery', 'service', 'cab', 'family', 'other'])->default('guest');
            $table->string('purpose')->nullable(); // Purpose of visit
            $table->integer('number_of_persons')->default(1);
            $table->string('vehicle_number')->nullable();
            $table->enum('vehicle_type', ['two_wheeler', 'four_wheeler', 'none'])->default('none');
            $table->date('expected_date');
            $table->time('expected_time')->nullable();
            $table->enum('status', ['pending_approval', 'approved', 'rejected', 'checked_in', 'checked_out', 'cancelled', 'expired'])->default('approved');
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete(); // Security guard
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable(); // Security notes
            $table->boolean('is_frequent_visitor')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'expected_date']);
            $table->index('gate_pass_code');
            $table->index(['user_id', 'expected_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};