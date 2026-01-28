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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['draft', 'published', 'archived'])->default('published');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('block_id')->nullable()->constrained()->nullOnDelete(); // null = all blocks
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('attachment')->nullable(); // PDF, image, etc.
            $table->boolean('send_notification')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'published_at']);
            $table->index('block_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};