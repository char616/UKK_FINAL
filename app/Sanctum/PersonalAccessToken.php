<?php

namespace Laravel\Sanctum;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PersonalAccessToken extends Model
{
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'last_used_at',
        'expires_at',
    ];

    protected $hidden = [
        'token',
    ];

    protected function casts(): array
    {
        return [
            'abilities' => 'json',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function tokenable(): MorphTo
    {
        return $this->morphTo('tokenable');
    }

    public static function findToken(string $token): ?self
    {
        if (! str_contains($token, '|')) {
            return static::where('token', hash('sha256', $token))->first();
        }

        [$id, $plainTextToken] = explode('|', $token, 2);

        if ($instance = static::find($id)) {
            return hash_equals($instance->token, hash('sha256', $plainTextToken)) ? $instance : null;
        }

        return null;
    }

    public function can(string $ability): bool
    {
        $abilities = $this->abilities ?? ['*'];

        return in_array('*', $abilities, true)
            || array_key_exists($ability, array_flip($abilities));
    }

    public function cant(string $ability): bool
    {
        return ! $this->can($ability);
    }
}

