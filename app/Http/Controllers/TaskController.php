<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    function task() {
        $link = 'tasks';

        return view('pages.task', [
            'link' => $link
        ]);
    }
}
