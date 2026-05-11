<?php
// Front-controller untuk semua request /api/*.

declare(strict_types=1);

// --- bootstrap ---
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/http.php';
require_once __DIR__ . '/helpers/cookie.php';
require_once __DIR__ . '/middlewares/AuthMiddleware.php';

// Autoload sederhana untuk controllers (satu file per class)
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/controllers/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// --- CORS (aktif kalau FE beda origin) ---
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// --- parse path ---
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

// Strip prefix "/api"
$apiPrefix = '/api';
$pos = strpos($uri, $apiPrefix);
if ($pos !== false) {
    $path = substr($uri, $pos + strlen($apiPrefix));
} else {
    $path = $uri;
}
$path = '/' . ltrim($path, '/');
if ($path === '/index.php') {
    $path = '/';
}

// --- load routers ---
require_once __DIR__ . '/routers/auth.php';

// --- dispatch ---
if (routeAuth($method, $path)) {
    exit;
}

// Root API info
if ($path === '/' || $path === '') {
    sendResponse(200, 'visitin-ao API', [
        'endpoints' => [
            'POST /api/auth/login',
            'GET  /api/auth/whoami',
            'POST /api/auth/logout',
        ],
    ]);
}

sendResponse(404, 'Not Found', null);
