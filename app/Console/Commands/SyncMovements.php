<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\MovementTransaction;
use Illuminate\Support\Facades\DB;

class SyncMovements extends Command
{
    protected $signature = 'app:sync-movements';
    protected $description = 'Sync movement transactions data from Darwinbox API';

    public function handle()
    {
        $url = 'https://kpncorporation.darwinbox.com/reportsbuilderapi/reportdatav2';

        $headers = [
            'Authorization' => 'Basic ZGFyd2luYm94c3R1ZGlvOkRCc3R1ZGlvMTIzNDUh',
            'Content-Type'  => 'application/json',
            'Cookie'        => '_cfuvid=kY2vqtS1haaFahMTfH3vDu2tpCwvWDEPVQHYX1x.jSc-1756348863016-0.0.1.1-604800000; session=bc109f14650cef28611294a44b73b642',
        ];

        $body = [
            'api_key'   => '9e6279e6cbb4a39315b3c94f07c134c0f949202a0c14a046b6595ae2398a498c9fa4ca74c58bb0bcad3218e574d273a9029536bd867d4574d3135148fbd3691f',
            'report_id' => 'b82ae4d175e4cc',
        ];

        try {
            $response = Http::withHeaders($headers)->post($url, $body);

            if (!$response->successful()) {
                $this->error("Gagal ambil data. Status: " . $response->status());
                $this->error($response->body());
                return;
            }

            $data = $response->json();
            $records = $data['response']['data'] ?? [];

            if (empty($records)) {
                $this->warn("Tidak ada data movement dari API.");
                return;
            }

            $this->info("Jumlah data movement dari API: " . count($records));

            // simpan kombinasi employee_id + from dari API
            $apiKeys = [];

            foreach ($records as $item) {
                $employeeId = $item['Employee Id'] ?? null;
                $fromDate   = $this->parseDate($item['From'] ?? null); // API: "Form" → DB: "from"

                if (!$employeeId || !$fromDate) {
                    continue;
                }

                $apiKeys[] = $employeeId . '|' . $fromDate;

                MovementTransaction::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'from'        => $fromDate,
                    ],
                    [
                        'to'                => $this->parseDate($item['To'] ?? null),
                        'bu_code'           => $item['BU Code'] ?? null,
                        'bu_name'           => $item['BU Name'] ?? null,
                        'designation_code'  => $item['Designation Code'] ?? null,
                        'designation_name'  => $item['Designation Name'] ?? null,
                        'employee_name'     => $item['Employee Name'] ?? null,
                        'group_company'     => $item['Group Company'] ?? null,
                        'is_demotion'       => $item['Is Demotion'] ?? null,
                        'is_promotion'      => $item['Is Promotion'] ?? null,
                        'unit_code'         => $item['Unit Code'] ?? null,
                        'unit_name'         => $item['Unit Name'] ?? null,
                    ]
                );
            }

            // Soft delete data yang tidak ada di API
            MovementTransaction::whereNotIn(
                DB::raw("CONCAT(employee_id,'|',`from`)"),
                $apiKeys
            )->delete();

            $this->info("Sync movement selesai. Data berhasil disimpan & diperbarui.");
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
        }
    }

    /**
     * Parse tanggal format d-m-Y → Y-m-d
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        $parsed = \DateTime::createFromFormat('d-m-Y', $date);
        return $parsed ? $parsed->format('Y-m-d') : null;
    }
}
