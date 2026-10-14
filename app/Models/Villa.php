<?php

namespace App\Models;

use Database\Factories\VillaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Villa extends Model
{
    /** @use HasFactory<VillaFactory> */
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_OCCUPIED = 'occupied';

    public const STATUS_CLEANING = 'cleaning';

    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUS_RESERVED = 'reserved';

    protected $fillable = [
        'code',
        'name',
        'slug',
        'category',
        'bedrooms',
        'bathrooms',
        'max_guests',
        'area_sqm',
        'price_per_night',
        'weekend_price_per_night',
        'cleaning_fee',
        'security_deposit',
        'status',
        'location',
        'address',
        'description',
        'amenities',
        'featured_image',
        'images',
        'is_featured',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'images' => 'array',
            'price_per_night' => 'decimal:2',
            'weekend_price_per_night' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
            'security_deposit' => 'decimal:2',
            'is_featured' => 'boolean',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'max_guests' => 'integer',
            'area_sqm' => 'integer',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function maintenanceTasks(): HasMany
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (! empty($category)) {
            return $query->where('category', $category);
        }

        return $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! empty($term)) {
            return $query->where(function (Builder $q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%");
            });
        }

        return $query;
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'bg-success',
            self::STATUS_OCCUPIED => 'bg-primary',
            self::STATUS_CLEANING => 'bg-warning text-dark',
            self::STATUS_MAINTENANCE => 'bg-danger',
            self::STATUS_RESERVED => 'bg-info text-dark',
            default => 'bg-secondary',
        };
    }
}
