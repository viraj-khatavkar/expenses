<?php

namespace App\Models;

use Database\Factories\InvestmentMonthFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentMonth extends Model
{
    /** @use HasFactory<InvestmentMonthFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['month' => 'integer', 'planned' => 'decimal:2'];
    }
}
