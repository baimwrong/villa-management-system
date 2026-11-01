<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCharge extends Model
{
    use HasFactory;

    public const CATEGORY_DINING = 'dining';

    public const CATEGORY_SPA = 'spa';

    public const CATEGORY_TRANSPORTATION = 'transportation';

    public const CATEGORY_EXPERIENCE = 'experience';

    public const CATEGORY_DAMAGE = 'damage';

    public const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'booking_id',
        'title',
        'category',
        'amount',
        'charged_at',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'charged_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryBadgeClass(): string
    {
        return match ($this->category) {
            self::CATEGORY_DINING => 'bg-warning text-dark',
            self::CATEGORY_SPA => 'bg-info text-dark',
            self::CATEGORY_TRANSPORTATION => 'bg-primary text-white',
            self::CATEGORY_EXPERIENCE => 'bg-success text-white',
            self::CATEGORY_DAMAGE => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }
}
