<?php

class AuthController
{
    public function login(array $input): void
    {
        $idPeg    = trim($input['id_peg']   ?? ($input['nik'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($idPeg === '' || $password === '') {
            sendResponse(400, 'id_peg dan password wajib diisi', null);
        }

        $baseUrl = rtrim(env('SIMPEG_BASE_URL', 'https://apisso.bkkjateng.co.id'), '/');
        $app     = env('APP_NAME', 'visitin-ao');

        $resp = httpRequest('POST', $baseUrl . '/auth/login', [
            'id_peg'   => $idPeg,
            'password' => $password,
            'app'      => $app,
        ]);

        $body    = $resp['body'];
        $status  = $body['status']  ?? $resp['status'];
        $message = $body['message'] ?? 'Unknown error';
        $data    = $body['data']    ?? null;

        if ((int) $status === 200 && !empty($data['token'])) {
            setAuthCookie($data['token']);
            sendResponse(200, $message, ['token' => $data['token']]);
        }

        sendResponse((int) $status ?: 500, $message, $data);
    }

    public function whoami(): void
    {
        $token = AuthMiddleware::require();

        $baseUrl = rtrim(env('SIMPEG_BASE_URL', 'https://apisso.bkkjateng.co.id'), '/');

        $resp = httpRequest('GET', $baseUrl . '/auth/whoami', null, [
            'Authorization: Bearer ' . $token,
        ]);

        $body    = $resp['body'];
        $status  = $body['status']  ?? $resp['status'];
        $message = $body['message'] ?? 'Unknown error';
        $data    = $body['data']    ?? null;

        // Kalau SIMPEG bilang unauthorized, bersihkan cookie juga.
        if ((int) $status === 401) {
            clearAuthCookie();
        }

        sendResponse((int) $status ?: 500, $message, $data);
    }

    public function logout(): void
    {
        clearAuthCookie();
        sendResponse(200, 'Logout berhasil', null);
    }
}
