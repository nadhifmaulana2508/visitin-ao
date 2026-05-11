<?php
// --- /api/index.php ---

// Setup Headers untuk REST API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Bootstrap modul auth (non-breaking untuk action lama)
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/http.php';
require_once __DIR__ . '/helpers/cookie.php';
require_once __DIR__ . '/middlewares/AuthMiddleware.php';

// Autoload controllers (satu class per file)
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/controllers/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

// Tangkap request method & endpoint
$request_method = $_SERVER["REQUEST_METHOD"];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Simple API Router
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

    case 'logout':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Method harus POST', null);
        }
        (new AuthController())->logout();
        break;

    // ===================== KUNJUNGAN =====================
    case 'get_mapping':
        // Nanti di sini include controllernya
        // require_once 'controllers/MappingController.php';
        
        // Contoh response JSON sementara:
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
            // Ambil data JSON dari body request
            $data = json_decode(file_get_contents("php://input"));
            
            // Logic simpan ke DB masuk sini...
            
            echo json_encode(["status" => "success", "message" => "Data kunjungan berhasil disimpan!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Method harus POST brokuu"]);
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Endpoint API tidak ditemukan"]);
        break;
}
?>
