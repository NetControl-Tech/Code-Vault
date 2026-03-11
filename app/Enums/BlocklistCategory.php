<?php

namespace App\Enums;

enum BlocklistCategory: string
{
    case Family = 'family';
    case Social = 'social';
    case Ads = 'ads';
    case Privacy = 'privacy';

    public function label(): string
    {
        return match ($this) {
            self::Family => 'أمان الأسرة',
            self::Social => 'السوشيال ميديا',
            self::Ads => 'الإعلانات المزعجة',
            self::Privacy => 'الخصوصية',
        };
    }
}
