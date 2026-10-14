<?php

namespace App\Models;

use Database\Factories\MaintenanceTaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceTask extends Model
{
    /** @use HasFactory<MaintenanceTaskFactory> */
    use HasFactory;

    public const TYPE_CLEANING = 'cleaning';

    public const TYPE_POOL_SERVICE = 'pool_service';

    public const TYPE_PLUMBING = 'plumbing';

    public const TYPE_ELECTRICAL = 'electrical';

    public const TYPE_HVAC = 'hvac';

    public const TYPE_LANDSCAPING = 'landscaping';

    public const TYPE_INSPECTION = 'inspection';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'villa_id',
        'assigned_to',
        'title',
        'task_type',
        'priority',
        'status',
        'cost',
        'scheduled_at',
        'completed_at',
        'description',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function villa(): BelongsTo
    {
        return $this->belongsTo(Villa::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function getPriorityBadgeClass(): string
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 'bg-danger text-white',
            self::PRIORITY_HIGH => 'bg-warning text-dark',
            self::PRIORITY_MEDIUM => 'bg-info text-dark',
            default => 'bg-light text-secondary border',
        };
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_IN_PROGRESS => 'bg-primary',
            self::STATUS_CANCELLED => 'bg-secondary',
            default => 'bg-warning text-dark',
        };
    }
}
