<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('borrow_date');
            $table->date('due_date');
            $table->text('reason');
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('overdue_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('return_condition')->nullable();
            $table->text('return_notes')->nullable();
            $table->unsignedInteger('fine_amount')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index('user_id');
            $table->index('asset_id');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
