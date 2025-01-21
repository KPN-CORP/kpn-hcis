<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    function assignment() {
        return view('pages.assignment');
    }
}
