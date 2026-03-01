<?php

namespace App\Enums;

enum LicenseCodeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Redeemed = 'redeemed';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'مفعل',
            self::Inactive => 'غير مفعل',
            self::Redeemed => 'مستخدم',
        };
    }
}
