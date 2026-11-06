<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cycle extends Model
{
    public const ACTIVE_ID = 1;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'active_round' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public static function current(): static
    {
        return static::query()->findOrFail(static::ACTIVE_ID);
    }
}
