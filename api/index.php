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

    // ===================== PROSPEK =====================
    case 'create_prospect':
        if ($request_method !== 'POST') {
            sendResponse(405, 'Method harus POST', null);
        }
        $data = readJsonBody();
        // Dummy: simpan ke session sebagai storage sementara
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['prospects'])) $_SESSION['prospects'] = [];
        
        $newProspect = [
            'id' => count($_SESSION['prospects']) + 1,
            'prospect_type' => $data['prospect_type'] ?? '',
            'customer_name' => $data['customer_name'] ?? '',
            'identity_number' => $data['identity_number'] ?? null,
            'phone_number' => $data['phone_number'] ?? '',
            'product_interest' => $data['product_interest'] ?? null,
            'estimated_amount' => $data['estimated_amount'] ?? 0,
            'provinsi' => $data['provinsi'] ?? '',
            'kab_kota' => $data['kab_kota'] ?? '',
            'kecamatan' => $data['kecamatan'] ?? '',
            'desa' => $data['desa'] ?? '',
            'address' => $data['address'] ?? '',
            'description' => $data['description'] ?? '',
            'is_ao_input' => $data['is_ao_input'] ?? false,
            'delegation_status' => ($data['is_ao_input'] ?? false) ? 'SUDAH_DIDELEGASIKAN' : 'BELUM_DIDELEGASIKAN',
            'status' => 'OPEN',
            'created_by' => $_SESSION['user_data']['employee_id'] ?? 'unknown',
            'created_by_name' => $_SESSION['user_data']['full_name'] ?? 'Unknown',
            'created_at' => date('Y-m-d H:i:s'),
            'assigned_to' => ($data['is_ao_input'] ?? false) ? ($_SESSION['user_data']['employee_id'] ?? null) : null,
        ];
        $_SESSION['prospects'][] = $newProspect;
        sendResponse(201, 'Prospek berhasil disimpan', $newProspect);
        break;

    case 'get_prospects':
        if (session_status() === PHP_SESSION_NONE) session_start();
        $prospects = $_SESSION['prospects'] ?? [];
        sendResponse(200, 'OK', $prospects);
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
