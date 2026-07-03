<?php
// --- /api/index.php ---
// Front Controller / Router utama API

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Max-Age: 86400");
    http_response_code(204);
    exit;
}

// Setup Headers untuk REST API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Bootstrap
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/http.php';
require_once __DIR__ . '/helpers/cookie.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middlewares/AuthMiddleware.php';

// Autoload controllers
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/controllers/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// Start session (untuk session bridge & dummy storage)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tangkap request method & action
$request_method = $_SERVER["REQUEST_METHOD"];
$action = $_GET['action'] ?? '';

// =====================================================
// ROUTER: Coba match di file router khusus dulu
// =====================================================

// Prospect router (action prefix: prospect_* atau master_*)
if (str_starts_with($action, 'prospect_') || str_starts_with($action, 'master_')) {
    $matched = require __DIR__ . '/routers/prospect.php';
    if ($matched !== false) exit;
}

// =====================================================
// ROUTER: Action utama (auth, kunjungan, dll)
// =====================================================
switch ($action) {

    // ===================== AUTH =====================
    case 'login':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Method harus POST', null);
        }
        (new AuthController())->login(readJsonBody());
        break;

    case 'whoami':
        if ($request_method !== 'GET') {
            sendResponse(405, 'Method harus GET', null);
        }
        (new AuthController())->whoami();
        break;

    case 'sso_session':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Method harus POST', null);
        }
        (new AuthController())->storeSsoSession(readJsonBody());
        break;

    case 'logout':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Method harus POST', null);
        }
        (new AuthController())->logout();
        break;

    // ===================== KUNJUNGAN (legacy) =====================
    case 'get_mapping':
        echo json_encode([
            "status" => "success",
            "data" => [
                ["id" => 1, "nama" => "Bapak Supriyadi", "jenis" => "Remedial"],
                ["id" => 2, "nama" => "Ibu Siti", "jenis" => "Kredit"]
            ]
        ]);
        break;

    case 'create_kunjungan':
        if ($request_method === 'POST') {
            $data = json_decode(file_get_contents("php://input"));
            echo json_encode(["status" => "success", "message" => "Data kunjungan berhasil disimpan!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Method harus POST"]);
        }
        break;

    // ===================== UPLOAD FOTO =====================
    case 'upload_foto':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Method harus POST', null);
        }
        $token = AuthMiddleware::require();

        $body = readJsonBody();
        $base64 = $body['foto_base64'] ?? null;
        $prefix = $body['prefix'] ?? 'prospek';

        if (!$base64) {
            sendResponse(400, 'foto_base64 wajib diisi', null);
        }

        // Decode base64
        $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
        if (!$imgData) {
            sendResponse(400, 'Format base64 tidak valid', null);
        }

        // Buat folder uploads
        $uploadDir = __DIR__ . '/../uploads/' . $prefix . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate filename
        $filename = $prefix . '_' . date('Ymd_His') . '_' . uniqid() . '.jpg';
        $filepath = $uploadDir . $filename;

        if (file_put_contents($filepath, $imgData)) {
            $relativePath = 'uploads/' . $prefix . '/' . $filename;
            sendResponse(200, 'Foto berhasil diupload', ['path' => $relativePath, 'filename' => $filename]);
        } else {
            sendResponse(500, 'Gagal menyimpan file', null);
        }
        break;

    // ===================== DEFAULT =====================
    default:
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Endpoint API tidak ditemukan: " . $action]);
        break;
}
