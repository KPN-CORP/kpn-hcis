<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class TaskBoxController extends Controller
{
    function taskBox()
    {
        $userId = Auth::id();

        return view("hcis.taskBox.dash", [
            "userId" => $userId,
        ]);
    }
}
