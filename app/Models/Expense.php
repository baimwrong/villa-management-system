<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    public const CATEGORY_UTILITIES = 'utilities';

    public const CATEGORY_SUPPLIES = 'supplies';

    public const CATEGORY_MAINTENANCE = 'maintenance';

    public const CATEGORY_STAFF_PAYROLL = 'staff_payroll';

    public const CATEGORY_MARKETING = 'marketing';

    public const CATEGORY_FOOD_BEVERAGE = 'food_beverage';

    public const CATEGORY_OTHER = 'other';

    protected $fillable = [
        'villa_id',
        'title',
        'category',
        'amount',
        'expense_date',
        'vendor',
        'payment_method',
        'invoice_number',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function scopeMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('expense_date', $month)->whereYear('expense_date', $year);
    }

    public function getCategoryBadgeClass(): string
    {
        return match ($this->category) {
            self::CATEGORY_MAINTENANCE => 'bg-danger text-white',
            self::CATEGORY_UTILITIES => 'bg-primary text-white',
            self::CATEGORY_STAFF_PAYROLL => 'bg-info text-dark',
            self::CATEGORY_FOOD_BEVERAGE => 'bg-warning text-dark',
            self::CATEGORY_SUPPLIES => 'bg-secondary text-white',
            default => 'bg-dark text-white',
        };
    }
}
