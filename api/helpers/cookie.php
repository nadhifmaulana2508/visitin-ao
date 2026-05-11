<?php
// Helper untuk set/clear cookie SSO.

if (!function_exists('jwtExpiry')) {
    /**
     * Decode payload JWT, kembalikan timestamp exp (int) atau 0.
     */
    function jwtExpiry(string $token): int
    {
        $parts = explode('.', $token);
        if (count($parts) < 2) {
            return 0;
        }
        $payload = $parts[1];
        $payload = strtr($payload, '-_', '+/');
        $pad = strlen($payload) % 4;
        if ($pad) {
            $payload .= str_repeat('=', 4 - $pad);
        }
        $json = base64_decode($payload, true);
        if ($json === false) {
            return 0;
        }
        $data = json_decode($json, true);
        return isset($data['exp']) ? (int) $data['exp'] : 0;
    }
}

if (!function_exists('setAuthCookie')) {
    function setAuthCookie(string $token): void
    {
        $name   = env('COOKIE_NAME', 'sso_token');
        $domain = env('COOKIE_DOMAIN', '');
        $secure = env_bool('COOKIE_SECURE', false);
        $same   = env('COOKIE_SAMESITE', 'Lax');
        $path   = env('COOKIE_PATH', '/');

        $exp = jwtExpiry($token);
        if ($exp <= time()) {
            $exp = time() + (14 * 24 * 60 * 60); // fallback 14 hari
        }

        $options = [
            'expires'  => $exp,
            'path'     => $path,
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => $same,
        ];
        if ($domain !== '' && $domain !== null) {
            $options['domain'] = $domain;
        }

        setcookie($name, $token, $options);
        $_COOKIE[$name] = $token;
    }
}

if (!function_exists('clearAuthCookie')) {
    function clearAuthCookie(): void
    {
        $name   = env('COOKIE_NAME', 'sso_token');
        $domain = env('COOKIE_DOMAIN', '');
        $secure = env_bool('COOKIE_SECURE', false);
        $same   = env('COOKIE_SAMESITE', 'Lax');
        $path   = env('COOKIE_PATH', '/');

        $options = [
            'expires'  => time() - 3600,
            'path'     => $path,
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => $same,
        ];
        if ($domain !== '' && $domain !== null) {
            $options['domain'] = $domain;
        }

        setcookie($name, '', $options);
        unset($_COOKIE[$name]);
    }
}
