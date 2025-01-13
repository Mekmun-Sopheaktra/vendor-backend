<?php

namespace App\Constants;

class OrderConstants
{
    public const PENDING = 'pending';

    public const CREATED = 'created';

    public const SUCCESS = 'success';

    public const FAILED = 'failed';

    public const CANCELLED = 'cancelled';

    public static function getStatusFromId($id)
    {
        switch ($id) {
            case 1:
                return self::SUCCESS;
            case 2:
                return self::CANCELLED;
            default:
                return self::PENDING;
        }
    }

    public static function getStatusFromString($status)
    {
        switch ($status) {
            case self::SUCCESS:
                return 1;
            case self::CANCELLED:
                return 2;
            default:
                return self::PENDING;
        }
    }
}
