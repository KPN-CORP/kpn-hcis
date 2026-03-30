<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class ParkingController extends Controller
{
    function __construct() {
        $this->auth_user = Auth()->user();
    }

    function index()
    {
        $employee_id = $this->auth_user->employee_id;

        $response = Http::acceptJson()->post('http://0.0.0.0:5004/generate-token', [
            'employee_id' => $employee_id,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data->token ?? '';
            $query = http_build_query([
                'employee_id' => $employee_id,
                'token' => $token,
            ]);

            return redirect()->away('https://apps-stage.hcis.live/auth-token-login?'.$query);
        }

        return redirect()->away('https://apps-stage.hcis.live/auth-token-login');
    }
}
