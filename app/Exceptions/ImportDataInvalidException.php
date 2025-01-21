<?php

namespace App\Exceptions;

use Exception;

class ImportDataInvalidException extends Exception
{
    public function __construct($message = "Invalid data format in Excel import.")
    {
        parent::__construct($message);
    }
}
