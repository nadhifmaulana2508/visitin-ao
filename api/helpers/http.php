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
        $timeout = max(1, (int) env('HTTP_TIMEOUT', 30));
        $connectTimeout = max(1, (int) env('HTTP_CONNECT_TIMEOUT', 10));
        $verifySsl = env_bool('HTTP_SSL_VERIFY', true);
        $ipResolve = trim((string) env('HTTP_IP_RESOLVE', '4'));
        $proxy = trim((string) env('HTTP_PROXY', ''));

        $defaultHeaders = ['Accept: application/json'];
        if ($body !== null) {
            $defaultHeaders[] = 'Content-Type: application/json';
        }
        $finalHeaders = array_merge($defaultHeaders, $headers);

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => $connectTimeout,
            CURLOPT_HTTPHEADER     => $finalHeaders,
            CURLOPT_SSL_VERIFYPEER => $verifySsl,
            CURLOPT_SSL_VERIFYHOST => $verifySsl ? 2 : 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_NOSIGNAL       => true,
        ]);

        if ($ipResolve === '4' && defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        } elseif ($ipResolve === '6' && defined('CURL_IPRESOLVE_V6')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V6);
        }

        if ($proxy !== '') {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

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
                'body'   => ['message' => "cURL error ({$errno}): {$err}"],
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
