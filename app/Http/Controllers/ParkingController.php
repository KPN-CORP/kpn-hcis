<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class ParkingController extends Controller
{
    function __construct() {
        $this->auth_user = Auth()->user();
        $this->base_url = config('services.ocr.base_url');
        $this->internal_url = config('services.ocr.internal_url');
    }

    function index()
    {
        $employee_id = $this->auth_user->employee_id;

        $response = Http::acceptJson()->post($this->internal_url.'/generate-token', [
            'employee_id' => $employee_id,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data['token'] ?? '';
            $query = http_build_query([
                'employee_id' => $employee_id,
                'token' => $token,
            ]);

            return redirect()->away($this->base_url.'/auth-token-login?'.$query);
        }

        return redirect()->away($this->base_url);
    }
}
