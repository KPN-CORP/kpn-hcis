<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AdminMenuController extends Controller
{
    function adminMenu()
    {
        $userId = Auth::id();
        if (
            $userId == "23886" ||
            $userId == "23892" ||
            $userId == "23893" ||
            $userId == "25678" ||
            $userId == "25725" ||
            $userId == "25734" ||
            ($userId = "12345")
        ) {
            $access_ca = "Y";
        } else {
            $access_ca = "N";
        }

        return view("hcis.adminMenu.dash", [
            "userId" => $userId,
            "access_ca" => $access_ca,
        ]);
    }
}
