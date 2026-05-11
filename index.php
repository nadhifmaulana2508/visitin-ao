<?php
// Root entry - cek cookie SSO, redirect sesuai status login.

$cookieName = 'sso_token';
$token      = $_COOKIE[$cookieName] ?? '';

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$path = $base === '' ? $uri : substr($uri, strlen($base));
$path = '/' . ltrim($path, '/');

// Halaman publik (tidak butuh login)
$publicPaths = ['/pages/login.php', '/login'];

if ($path === '/' || $path === '/index.php') {
    if ($token) {
        // TODO: ganti ke dashboard saat sudah dibuat
        header('Location: ' . $base . '/pages/dashboard.php');
    } else {
        header('Location: ' . $base . '/pages/login.php');
    }
    exit;
}

// Proteksi halaman non-publik
if (!in_array($path, $publicPaths, true) && !$token) {
    header('Location: ' . $base . '/pages/login.php');
    exit;
}

// Kalau sampai sini, biarkan .htaccess mem-serve file fisik.
http_response_code(404);
echo 'Not Found';
