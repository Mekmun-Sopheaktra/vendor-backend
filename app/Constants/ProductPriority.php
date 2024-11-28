<?php

namespace App\Constants;

class ProductPriority
{
    public const HOT = 'hot';
    public const POPULAR = 'popular';
    public const BEST_SELLER = 'best_seller';
    public const RECOMMENDED = 'recommended';
    public const PROMO = 'promo';
    public const DISCOUNT = 'discount';
    public const SALE = 'sale';
    public const SOLD_OUT = 'sold_out';
    public const OUT_OF_STOCK = 'out_of_stock';
    public const COMING_SOON = 'coming_soon';
    public const PRE_ORDER = 'pre_order';
    public const LIMITED = 'limited';
    public const EXCLUSIVE = 'exclusive';
    public const RARE = 'rare';
    public const SPECIAL = 'special';
    public const UNIQUE = 'unique';
    public const FEATURED = 'featured';
    public const TRENDING = 'trending';
    public const LATEST = 'latest';
    public const UPDATED = 'updated';
    public const RECENT = 'recent';
    public const FRESH = 'fresh';
    public const NEW_ARRIVAL = 'new_arrival';
    public const NEW_RELEASE = 'new_release';
    public const NEW = 'new';
    public const UPCOMING = 'upcoming';

    public static function priorityLevel($priority)
    {
        switch ($priority) {
            case self::HOT: return 1;
            case self::POPULAR: return 2;
            case self::BEST_SELLER: return 3;
            case self::RECOMMENDED: return 4;
            case self::PROMO: return 5;
            case self::DISCOUNT: return 6;
            case self::SALE: return 7;
            case self::SOLD_OUT: return 8;
            case self::OUT_OF_STOCK: return 9;
            case self::COMING_SOON: return 10;
            case self::PRE_ORDER: return 11;
            case self::LIMITED: return 12;
            case self::EXCLUSIVE: return 13;
            case self::RARE: return 14;
            case self::SPECIAL: return 15;
            case self::UNIQUE: return 16;
            case self::FEATURED: return 17;
            case self::TRENDING: return 18;
            case self::LATEST: return 19;
            case self::UPDATED: return 20;
            case self::RECENT: return 21;
            case self::FRESH: return 22;
            case self::NEW_ARRIVAL: return 23;
            case self::NEW_RELEASE: return 24;
            case self::NEW: return 25;
            case self::UPCOMING: return 26;
            default: return 0;
        }
    }

    public static function getPriorityType($priority)
    {
        switch ($priority) {
            case self::HOT:
            case self::POPULAR:
            case self::BEST_SELLER:
            case self::RECOMMENDED:
            case self::FEATURED:
                return 'featured';
            case self::PROMO:
            case self::DISCOUNT:
            case self::SALE:
                return 'sales';
            case self::SOLD_OUT:
            case self::OUT_OF_STOCK:
            case self::COMING_SOON:
            case self::PRE_ORDER:
                return 'availability';
            case self::LIMITED:
            case self::EXCLUSIVE:
            case self::RARE:
            case self::SPECIAL:
            case self::UNIQUE:
                return 'exclusive';
            case self::TRENDING:
            case self::LATEST:
            case self::UPDATED:
            case self::RECENT:
            case self::FRESH:
            case self::NEW_ARRIVAL:
            case self::NEW_RELEASE:
            case self::NEW:
            case self::UPCOMING:
                return 'new';
            default:
                return 'unknown';
        }
    }
}
