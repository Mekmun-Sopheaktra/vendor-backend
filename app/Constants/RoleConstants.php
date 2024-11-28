<?php

namespace App\Constants;

class RoleConstants
{
    public const SUPERUSER = 'superuser';

    public const VENDOR = 'vendor';

    public const USER = 'user';

    public const PUBLIC = 'public';


    public static function getStatusFromId($id)
    {
        switch ($id) {
            case 1:
                return self::SUPERUSER;
            case 2:
                return self::VENDOR;
            case 3:
                return self::USER;
            case 4:
            default:
                return self::PUBLIC;
        }
    }

    public static function getStatusFromString($status)
    {
        switch ($status) {
            case self::SUPERUSER:
                return 1;
            case self::VENDOR:
                return 2;
            case self::USER:
                return 3;
            case 4:
            default:
                return self::PUBLIC;
        }
    }
}
