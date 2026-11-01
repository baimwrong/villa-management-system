<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
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
            $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            return $query->where(function (Builder $q) use ($term, $operator): void {
                $q->where('name', $operator, "%{$term}%")
                    ->orWhere('code', $operator, "%{$term}%")
                    ->orWhere('location', $operator, "%{$term}%")
                    ->orWhere('category', $operator, "%{$term}%");
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

    /**
     * Calculate stay tariffs distinguishing between standard weekday and premium weekend nights.
     *
     * @return array{nights: int, weekday_nights: int, weekend_nights: int, accommodation_total: float, effective_nightly_rate: float}
     */
    public function calculateStayTotal(CarbonInterface|string $checkIn, CarbonInterface|string $checkOut, ?float $customBaseRate = null): array
    {
        $current = Carbon::parse($checkIn)->startOfDay();
        $end = Carbon::parse($checkOut)->startOfDay();
        $totalNights = max(1, $current->diffInDays($end));

        $weekdayNights = 0;
        $weekendNights = 0;
        $accommodationTotal = 0.0;

        $baseRate = $customBaseRate !== null && $customBaseRate > 0 ? $customBaseRate : (float) $this->price_per_night;
        $weekendRate = $this->weekend_price_per_night !== null && (float) $this->weekend_price_per_night > 0
            ? (float) $this->weekend_price_per_night
            : $baseRate;

        while ($current->lt($end)) {
            // Friday and Saturday nights are standard hospitality weekend rates
            if ($current->isFriday() || $current->isSaturday()) {
                $weekendNights++;
                $accommodationTotal += $weekendRate;
            } else {
                $weekdayNights++;
                $accommodationTotal += $baseRate;
            }
            $current->addDay();
        }

        $effectiveRate = $totalNights > 0 ? round($accommodationTotal / $totalNights, 2) : $baseRate;

        return [
            'nights' => $totalNights,
            'weekday_nights' => $weekdayNights,
            'weekend_nights' => $weekendNights,
            'accommodation_total' => round($accommodationTotal, 2),
            'effective_nightly_rate' => $effectiveRate,
        ];
    }
}
