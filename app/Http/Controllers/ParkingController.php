<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class ParkingController extends Controller
{
    function __construct() {}

    function index()
    {
        return redirect()->away('https://apps-stage.hcis.live');
    }
}
