<?php
// Ambil token dari Authorization header (Bearer) atau cookie sso_token.

class AuthMiddleware
{
    public static function getToken(): ?string
    {
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

        return null;
    }

    public static function require(): string
    {
        $token = self::getToken();
        if (!$token) {
            sendResponse(401, 'Unauthorized', null);
        }
        return $token;
    }
}
