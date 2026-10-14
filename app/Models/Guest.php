<?php

namespace App\Models;

use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory;

    public const VIP_STANDARD = 'standard';

    public const VIP_VIP = 'vip';

    public const VIP_VVIP = 'vvip';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'nationality',
        'id_or_passport',
        'vip_level',
        'special_requests',
        'total_stays',
        'total_spent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_stays' => 'integer',
            'total_spent' => 'decimal:2',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim("{$this->first_name} {$this->last_name}")
        );
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(Payment::class, Booking::class);
    }

    public function scopeVip(Builder $query): Builder
    {
        return $query->whereIn('vip_level', [self::VIP_VIP, self::VIP_VVIP]);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! empty($term)) {
            return $query->where(function (Builder $q) use ($term): void {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('nationality', 'like', "%{$term}%");
            });
        }

        return $query;
    }

    public function getVipBadgeClass(): string
    {
        return match ($this->vip_level) {
            self::VIP_VVIP => 'bg-warning text-dark border border-warning-subtle',
            self::VIP_VIP => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }
}
