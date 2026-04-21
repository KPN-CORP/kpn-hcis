<?php

namespace App\Helpers;

class Padding {
    public static function leftZero($val, $length) {
        return str_pad($val, $length, '0', STR_PAD_LEFT);
    }

    public static function rightZero($val, $length) {
        return str_pad($val, $length, '0', STR_PAD_RIGHT);
    }

    public static function incrementLeftZero($val, $length) {
        return str_pad((int)$val + 1, $length, '0', STR_PAD_LEFT);
    }

    public static function incrementRightZero($val, $length) {
        return str_pad((int)$val + 1, $length, '0', STR_PAD_RIGHT);
    }
}
