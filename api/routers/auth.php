<?php
// Routing untuk /api/auth/*

/**
 * @param string $method  HTTP method
 * @param string $path    Path relatif setelah /api (misal "/auth/login")
 * @return bool           true kalau route ditangani
 */
function routeAuth(string $method, string $path): bool
{
    $controller = new AuthController();

    if ($method === 'POST' && $path === '/auth/login') {
        $controller->login(readJsonBody());
        return true;
    }

    if ($method === 'GET' && $path === '/auth/whoami') {
        $controller->whoami();
        return true;
    }

    if ($method === 'POST' && $path === '/auth/logout') {
        $controller->logout();
        return true;
    }

    return false;
}
