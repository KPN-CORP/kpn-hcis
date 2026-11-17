<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Support\Facades\Log;

class RuntimeExceptionNoStackTrace extends Exception implements ShouldntReport
{
    public function report(): void
    {
        Log::error($this->getMessage());
    }
}
