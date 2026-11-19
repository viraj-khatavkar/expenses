<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investment_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('name_key', 100);
            $table->timestamps();
            $table->unique(['user_id', 'name_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investment_categories');
    }
};
