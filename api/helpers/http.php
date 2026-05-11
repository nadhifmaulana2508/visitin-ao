<?php
// Wrapper cURL untuk memanggil API eksternal (SIMPEG).

if (!function_exists('httpRequest')) {
    /**
     * @param string $method GET|POST|PUT|DELETE
     * @param string $url Full URL
     * @param array|null $body Body JSON (untuk POST/PUT)
     * @param array $headers Extra headers (format "Key: Value")
     * @return array{status:int, body:array, raw:string}
     */
    function httpRequest(string $method, string $url, ?array $body = null, array $headers = []): array
    {
        $ch = curl_init();

        $defaultHeaders = ['Accept: application/json'];
        if ($body !== null) {
            $defaultHeaders[] = 'Content-Type: application/json';
        }
        $finalHeaders = array_merge($defaultHeaders, $headers);

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => $finalHeaders,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
        }

        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno  = curl_errno($ch);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($errno !== 0) {
            return [
                'status' => 0,
                'body'   => ['message' => 'cURL error: ' . $err],
                'raw'    => '',
            ];
        }

        $decoded = json_decode((string) $raw, true);
        return [
            'status' => $status,
            'body'   => is_array($decoded) ? $decoded : ['message' => (string) $raw],
            'raw'    => (string) $raw,
        ];
    }
}
