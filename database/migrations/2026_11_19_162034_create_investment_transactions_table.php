<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('investment_categories')->cascadeOnDelete();
            $table->char('period', 7);
            $table->date('date')->nullable();
            $table->string('direction', 10);
            $table->unsignedBigInteger('amount_paise');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['category_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_transactions');
    }
};
