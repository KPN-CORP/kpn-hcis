<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HttpClient {
    protected function request(
        string $method,
        string $url,
        array $headers = [],
        array|object $payload = []
    ) {
        $payload = $this->normalizePayload($payload);

        $http = Http::timeout(30)
            ->acceptJson()
            ->withHeaders($headers);

        return $http->{$method}($url, $payload);
    }

    protected function normalizePayload(array|object $payload): array {
        if (is_object($payload)) {
            if (method_exists($payload, 'toArray')) {
                return $payload->toArray();
            }

            if (method_exists($payload, 'toJSON')) {
                return json_decode($payload->toJSON(), true) ?? [];
            }
        }

        return $payload ?? [];
    }

    protected function formatResponse(Response $response): array {
        $status = $response->status();
        $body = $response->json();

        if (in_array($status, [200, 201])) {
            return [
                'success' => true,
                'status'  => $status,
                'data'    => $body ?? [],
                'error'   => null,
            ];
        }

        return [
            'success' => false,
            'status'  => $status,
            'data'    => null,
            'error'   => $body ?? $response->body(),
        ];
    }

    public function getJSON(string $url, array $headers = []): array {
        $response = $this->request('get', $url, $headers);

        return $this->formatResponse($response);
    }

    public function postJSON(string $url, array|object $payload = [], array $headers = []): array {
        $response = $this->request('post', $url, $headers, $payload);

        return $this->formatResponse($response);
    }

    public function putJSON(string $url, array|object $payload = [], array $headers = []): array {
        $response = $this->request('put', $url, $headers, $payload);

        return $this->formatResponse($response);
    }

    public function deleteJSON(string $url, array $headers = []): array {
        $response = $this->request('delete', $url, $headers);

        return $this->formatResponse($response);
    }
}
