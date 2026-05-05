<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class ID {
    public static function generateUUID() {
        return (string) Str::uuid();
    }
}
