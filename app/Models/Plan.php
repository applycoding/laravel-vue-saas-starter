<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price_cents',
        'max_members',
        'max_projects',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
        ];
    }

    protected function maxMembers(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $value === null ? null : (int) $value,
        );
    }

    protected function maxProjects(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $value === null ? null : (int) $value,
        );
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public static function starter(): self
    {
        return static::query()->where('slug', 'starter')->firstOrFail();
    }

    public function isUnlimited(string $column): bool
    {
        return match ($column) {
            'max_members', 'max_projects' => $this->{$column} === null,
            default => throw new \InvalidArgumentException("Unknown plan limit [{$column}]."),
        };
    }
}
