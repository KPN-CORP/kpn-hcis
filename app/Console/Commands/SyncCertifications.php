<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Certification;

class SyncCertifications extends Command
{
    protected $signature = 'app:sync-certifications';
    protected $description = 'Get certifications data from Darwinbox API and sync with database';

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
            'report_id' => 'cc6926d7af492e',
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
                $this->warn("Tidak ada data certifications dari API.");
                return;
            }

            $this->info("Jumlah data dari API: " . count($records));

            // Simpan semua array_id yang ada di API
            $apiArrayIds = [];

            foreach ($records as $item) {
                $apiArrayIds[] = $item['Array Id'];

                Certification::updateOrCreate(
                    ['array_id' => $item['Array Id']],
                    [
                        'employee_id'                    => $item['Employee Id'] ?? null,
                        'employee_fullname'              => $item['Employee Full Name'] ?? null,
                        'certification_completion_date'  => $this->parseDate($item['Certification Completion Date'] ?? null),
                        'certification_document'         => $item['Certification Document'] ?? null,
                        'certification_expiry_date'      => $this->parseDate($item['Certification Expiry Date'] ?? null),
                        'certification_issue_date'       => $this->parseDate($item['Certification Issue Date'] ?? null),
                        'certification_name'             => $item['Certification Name'] ?? null,
                        'certification_number'           => $item['Certification Number'] ?? null,
                        'created_on'                     => $this->parseDate($item['Created On'] ?? null),
                        'description'                    => $item['Description'] ?? null,
                        'transaction_status'             => $item['Transaction Status'] ?? null,
                        'update_on'                      => $this->parseDate($item['Updated On'] ?? null),
                    ]
                );
            }

            // Soft delete data yang tidak ada di API
            Certification::whereNotIn('array_id', $apiArrayIds)->delete();

            $this->info("Sync selesai. Data berhasil disimpan & diperbarui.");

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
