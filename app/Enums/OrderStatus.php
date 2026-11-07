<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Ready = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Map of allowed transitions from the current status.
     *
     * @var array<string, list<string>>
     */
    private const Transitions = [
        'pending' => ['processing', 'ready', 'cancelled'],
        'processing' => ['ready', 'cancelled'],
        'ready' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function canTransitionTo(self $target): bool
    {
        return in_array($target->value, self::Transitions[$this->value], true);
    }

    /**
     * Terminal statuses can no longer be changed.
     */
    public function isFinal(): bool
    {
        return self::Transitions[$this->value] === [];
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Konfirmasi',
            self::Processing => 'Sedang Diproses',
            self::Ready => 'Siap Diambil',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
