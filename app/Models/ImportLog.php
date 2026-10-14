<?php

namespace App\Models;

use Database\Factories\ImportLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    /** @use HasFactory<ImportLogFactory> */
    use HasFactory;

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_FAILED = 'failed';

    public const TYPE_VILLAS = 'villas';

    public const TYPE_GUESTS = 'guests';

    public const TYPE_BOOKINGS = 'bookings';

    public const TYPE_EXPENSES = 'expenses';

    protected $fillable = [
        'user_id',
        'import_type',
        'file_name',
        'total_rows',
        'imported_rows',
        'failed_rows',
        'status',
        'errors',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'errors' => 'array',
            'total_rows' => 'integer',
            'imported_rows' => 'integer',
            'failed_rows' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_PARTIAL => 'bg-warning text-dark',
            default => 'bg-danger',
        };
    }
}
