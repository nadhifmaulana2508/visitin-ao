<?php
// Loader .env super-ringan (tanpa dependency composer).

if (!function_exists('env_load')) {
    function env_load(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            $GLOBALS['ENV'][$key] = $value;
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }

    function env(string $key, $default = null)
    {
        if (isset($GLOBALS['ENV'][$key])) {
            return $GLOBALS['ENV'][$key];
        }
        $val = getenv($key);
        return $val === false ? $default : $val;
    }

    function env_bool(string $key, bool $default = false): bool
    {
        $val = env($key, null);
        if ($val === null) {
            return $default;
        }
        $val = strtolower((string) $val);
        return in_array($val, ['1', 'true', 'yes', 'on'], true);
    }
}

env_load(__DIR__ . '/../.env');
