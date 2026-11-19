<?php

namespace App\Models;

use Database\Factories\InvestmentCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InvestmentCategory extends Model
{
    /** @use HasFactory<InvestmentCategoryFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function forName(User $user, string $name): self
    {
        $name = Str::squish($name);

        return self::firstOrCreate(
            ['user_id' => $user->getKey(), 'name_key' => Str::lower($name)],
            ['name' => $name],
        );
    }
}
