<?php

namespace App\Models;

use Database\Factories\InvestmentTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvestmentTransaction extends Model
{
    /** @use HasFactory<InvestmentTransactionFactory> */
    use HasFactory, SoftDeletes;

    public function category(): BelongsTo
    {
        return $this->belongsTo(InvestmentCategory::class);
    }

    #[Scope]
    protected function forUser(Builder $query, User $user): void
    {
        $query->whereIn('category_id', InvestmentCategory::whereBelongsTo($user)->select('id'));
    }

    protected function casts(): array
    {
        return ['date' => 'date:Y-m-d', 'amount_paise' => 'integer'];
    }
}
