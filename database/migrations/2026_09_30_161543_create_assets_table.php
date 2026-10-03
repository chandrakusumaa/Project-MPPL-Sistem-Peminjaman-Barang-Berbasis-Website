<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_category_id')->constrained()->restrictOnDelete(); // restrict deletion if category is in use
            $table->ulid('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('specifications')->nullable();
            $table->string('location')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('photo')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
