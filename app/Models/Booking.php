<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_CHECKED_OUT = 'checked_out';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    public const PAYMENT_PENDING = 'pending';

    public const PAYMENT_PARTIAL = 'partial';

    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_REFUNDED = 'refunded';

    protected $fillable = [
        'booking_code',
        'villa_id',
        'guest_id',
        'user_id',
        'check_in',
        'check_out',
        'adults',
        'children',
        'total_nights',
        'nightly_rate',
        'cleaning_fee',
        'service_fee',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'payment_status',
        'status',
        'special_notes',
        'cancellation_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'date',
            'check_out' => 'date',
            'total_nights' => 'integer',
            'adults' => 'integer',
            'children' => 'integer',
            'nightly_rate' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
        ];
    }

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function extraCharges(): HasMany
    {
        return $this->hasMany(BookingCharge::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN]);
    }

    public function scopeOverlapping(Builder $query, int $villaId, string $checkIn, string $checkOut, ?int $ignoreBookingId = null): Builder
    {
        $checkInDate = Carbon::parse($checkIn)->toDateString();
        $checkOutDate = Carbon::parse($checkOut)->toDateString();

        return $query->where('villa_id', $villaId)
            ->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_NO_SHOW])
            ->when($ignoreBookingId, fn (Builder $q) => $q->where('id', '!=', $ignoreBookingId))
            ->whereDate('check_in', '<', $checkOutDate)
            ->whereDate('check_out', '>', $checkInDate);
    }

    public function recalculatePaymentStatus(): void
    {
        $totalPaid = (float) $this->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount');
        $this->paid_amount = $totalPaid;
        $this->balance_due = max(0, (float) $this->total_amount - $totalPaid);

        if ($this->balance_due <= 0 && $this->total_amount > 0) {
            $this->payment_status = self::PAYMENT_PAID;
        } elseif ($totalPaid > 0) {
            $this->payment_status = self::PAYMENT_PARTIAL;
        } else {
            $this->payment_status = self::PAYMENT_PENDING;
        }

        $this->save();
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'bg-info text-dark',
            self::STATUS_CHECKED_IN => 'bg-success',
            self::STATUS_CHECKED_OUT => 'bg-secondary',
            self::STATUS_CANCELLED => 'bg-danger',
            self::STATUS_NO_SHOW => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    public function getPaymentBadgeClass(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PAID => 'bg-success',
            self::PAYMENT_PARTIAL => 'bg-warning text-dark',
            self::PAYMENT_REFUNDED => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
