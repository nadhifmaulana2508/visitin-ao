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

        $payload = AuthController::decodeJwtPayload($token);
        $employeeId = $payload['id_peg'] ?? $payload['sub'] ?? null;
        if (!$payload || empty($employeeId)) {
            return;
        }

        $sessionUser = AuthController::buildSessionUser($token, AuthController::fetchSsoUser($token));
        if (!$sessionUser) {
            return;
        }

        $_SESSION['user_data'] = $sessionUser;
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
