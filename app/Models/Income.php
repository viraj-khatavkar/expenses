<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use HasFactory, SoftDeletes;

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }
}
