<?php
namespace App\Enums;

enum PaymentTransactionStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
    case ChargedBack = 'chargedback';

    public static function fromPayHereCode(int $code): self
    {
        return match ($code) {
            2 => self::Success,
            0 => self::Pending,
            -1 => self::Cancelled,
            -2 => self::Failed,
            -3 => self::ChargedBack,
            default => self::Failed,
        };
    }
}