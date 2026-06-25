<?php
// Ambil token dari Authorization header (Bearer) atau cookie sso_token.

class AuthMiddleware
{
    private static function hydrateSessionFromToken(string $token): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['user_data']['employee_id'])) {
            return;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return;
        }

        $payload = json_decode(base64_decode($parts[1]), true);
        if (!$payload || empty($payload['sub'])) {
            return;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return;
        }

        $_SESSION['user_data'] = [
            'token' => $token,
            'employee_id' => $payload['sub'],
            'full_name' => $payload['name'] ?? '',
            'role' => $payload['role'] ?? 'staff',
            'permissions' => $payload['permissions'] ?? [],
            'branch' => $payload['branch'] ?? '',
            'kode_kantor' => $payload['kode_kantor'] ?? '000',
            'job_position' => $payload['job_position'] ?? '',
            'group_jabatan' => $payload['group_jabatan'] ?? '',
        ];
    }

    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Authorization header (prioritas)
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

        if ($auth && stripos($auth, 'Bearer ') === 0) {
            $token = trim(substr($auth, 7));
            if ($token !== '') {
                return $token;
            }
        }

        // 2. Cookie
        $cookieName = env('COOKIE_NAME', 'sso_token');
        if (!empty($_COOKIE[$cookieName])) {
            return $_COOKIE[$cookieName];
        }

        // 3. Session halaman (fallback localhost/dummy login)
        if (!empty($_SESSION['user_data']['token'])) {
            return (string) $_SESSION['user_data']['token'];
        }

        if (!empty($_SESSION['user_data']['employee_id'])) {
            return 'session-authenticated';
        }

        return null;
    }

    public static function require(): string
    {
        $token = self::getToken();
        if (!$token) {
            sendResponse(401, 'Unauthorized', null);
        }
        self::hydrateSessionFromToken($token);
        return $token;
    }
}
