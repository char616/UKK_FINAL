<?php

namespace Laravel\Sanctum;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

trait HasApiTokens
{
    protected ?PersonalAccessToken $accessToken = null;

    public function tokens(): MorphMany
    {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }

    public function tokenCan(string $ability): bool
    {
        return $this->accessToken ? $this->accessToken->can($ability) : false;
    }

    public function createToken(string $name, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): NewAccessToken
    {
        $plainTextToken = sprintf(
            '%s%s%s',
            config('sanctum.token_prefix', ''),
            $tokenEntropy = Str::random(40),
            hash('crc32b', $tokenEntropy)
        );

        /** @var PersonalAccessToken $token */
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    public function currentAccessToken(): ?PersonalAccessToken
    {
        return $this->accessToken;
    }

    public function withAccessToken(?PersonalAccessToken $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }
}

