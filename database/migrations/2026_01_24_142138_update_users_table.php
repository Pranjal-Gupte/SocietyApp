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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->after('id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('flat_id')->after('role_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone')->after('email')->nullable();
            $table->text('address')->after('phone')->nullable();
            $table->date('date_of_birth')->after('address')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->after('date_of_birth')->nullable();
            $table->enum('resident_type', ['owner', 'tenant', 'family_member'])->after('gender')->default('owner');
            $table->date('move_in_date')->after('resident_type')->nullable();
            $table->boolean('is_active')->after('move_in_date')->default(true);
            $table->text('profile_photo')->after('is_active')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['flat_id']);
            $table->dropColumn([
                'role_id',
                'flat_id',
                'phone',
                'address',
                'date_of_birth',
                'gender',
                'resident_type',
                'move_in_date',
                'is_active',
                'profile_photo'
            ]);
        });
    }
};