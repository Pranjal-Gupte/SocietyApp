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
        Schema::create('flats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained()->cascadeOnDelete();
            $table->string('flat_number'); // 101, 102, 201, etc.
            $table->string('full_number')->unique(); // A-101, B-202, etc.
            $table->integer('floor');
            $table->decimal('carpet_area', 8, 2)->nullable(); // in sq ft
            $table->integer('bedrooms')->default(2);
            $table->enum('status', ['occupied', 'vacant', 'under_maintenance'])->default('occupied');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['block_id', 'flat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flats');
    }
};