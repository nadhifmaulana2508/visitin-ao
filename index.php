<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * =========================
 * BASE APP (AUTO)
 * =========================
 * Sesuaikan 'kunjungan-ao' dengan nama folder project-mu di htdocs lokal
 */
define('BASE_APP',
    (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' .
    $_SERVER['HTTP_HOST'] .
    (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? '/kunjungan-ao' : '')
);

// =========================
// COOKIE -> SESSION BRIDGE (DUMMY MODE)
// =========================
// Jika ada cookie sso_token, decode dan refresh session dari token.
// Jika tidak ada cookie, JANGAN hapus session (karena login POST set session tanpa cookie).
if (!empty($_COOKIE['sso_token'])) {
    $token = $_COOKIE['sso_token'];
    $parts = explode('.', $token);
    if (count($parts) === 3) {
        $payload = json_decode(base64_decode($parts[1]), true);
        if ($payload && isset($payload['sub']) && (!isset($payload['exp']) || $payload['exp'] >= time())) {
            $_SESSION['user_data'] = [
                'token' => $token,
                'employee_id' => $payload['sub'],
                'full_name' => $payload['name'] ?? '',
                'role' => $payload['role'] ?? 'staff',
                'permissions' => $payload['permissions'] ?? [],
                'branch' => $payload['branch'] ?? '',
                'kode_kantor' => $payload['kode_kantor'] ?? '000',
                'job_position' => $payload['job_position'] ?? '',
                'group_jabatan' => $payload['group_jabatan'] ?? '',
            ];
        } else {
            // Token expired atau invalid
            setcookie('sso_token', '', time() - 3600, '/');
            unset($_COOKIE['sso_token']);
            unset($_SESSION['user_data']);
        }
    }
}
// NOTE: Jika tidak ada cookie tapi session ada (dari POST login), biarkan session hidup.

// =========================
// AMBIL URL DARI REQUEST URI (ANTI BADAI NGINX)
// =========================
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Hapus base folder kalau jalan di localhost, otomatis hilang saat di server live
$requestUri = str_replace('/kunjungan-ao', '', $requestUri);

$url = trim($requestUri, '/');

// =========================
// JANGAN LEWATKAN API KE ROUTER HALAMAN
// =========================
if (strpos($url, 'api/') === 0) {
    $apiPath = __DIR__ . '/' . $url . '.php';

    if (is_file($apiPath)) {
        require $apiPath;
    } else {
        http_response_code(404);
        header('Content-Type: application/json'); // Output murni format JSON
        echo json_encode([
            'status' => false,
            'message' => 'API endpoint not found'
        ]);
    }
    exit;
}

// =========================
// ROUTING DEFAULT
// =========================

// *** HANDLE LOGIN POST (fallback tanpa JS/fetch) ***
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($url === 'login' || $url === '')) {
    $idPeg = trim($_POST['id_peg'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($idPeg !== '' && $password !== '') {
        // Load dummy users dari AuthController
        require_once __DIR__ . '/api/config/env.php';
        require_once __DIR__ . '/api/helpers/response.php';
        require_once __DIR__ . '/api/helpers/cookie.php';
        require_once __DIR__ . '/api/controllers/AuthController.php';
        
        // Dummy users check
        $dummyUsers = [
            '102-119' => ['password' => '123456', 'full_name' => 'SYAIFUN NADHIF MAULANA, S. Kom', 'role' => 'developer', 'permissions' => ['DEV','SUPERUSER_PROSPEK','AO_KREDIT','AO_DANA','AO_REMEDIAL_FE','AO_REMEDIAL_BE'], 'branch_name' => 'Kantor Pusat', 'kode' => '000', 'job_position' => 'Staf Sistem dan Jaringan TI', 'group_jabatan' => 'Staf'],
            '201-001' => ['password' => '123456', 'full_name' => 'BUDI SANTOSO', 'role' => 'ao_kredit', 'permissions' => ['AO_KREDIT'], 'branch_name' => 'Cabang Utama', 'kode' => '001', 'job_position' => 'Account Officer Kredit', 'group_jabatan' => 'AO Kredit'],
            '201-002' => ['password' => '123456', 'full_name' => 'SITI RAHAYU', 'role' => 'ao_dana', 'permissions' => ['AO_DANA'], 'branch_name' => 'Cabang Utama', 'kode' => '001', 'job_position' => 'Account Officer Dana', 'group_jabatan' => 'AO Dana'],
            '201-003' => ['password' => '123456', 'full_name' => 'ANDI SETIAWAN', 'role' => 'ao_remedial', 'permissions' => ['AO_REMEDIAL_FE','AO_REMEDIAL_BE'], 'branch_name' => 'Cabang Utama', 'kode' => '001', 'job_position' => 'Account Officer Remedial', 'group_jabatan' => 'AO Remedial'],
            '201-004' => ['password' => '123456', 'full_name' => 'WAHYU HIDAYAT', 'role' => 'superuser', 'permissions' => ['SUPERUSER_PROSPEK'], 'branch_name' => 'Cabang Utama', 'kode' => '001', 'job_position' => 'Kepala Bidang Pemasaran', 'group_jabatan' => 'Pejabat'],
            '201-005' => ['password' => '123456', 'full_name' => 'DEWI KUSUMA', 'role' => 'staff', 'permissions' => [], 'branch_name' => 'Cabang Utama', 'kode' => '001', 'job_position' => 'Teller', 'group_jabatan' => 'Staf'],
            '201-006' => ['password' => '123456', 'full_name' => 'RATNA SARI', 'role' => 'staff', 'permissions' => [], 'branch_name' => 'Cabang Utama', 'kode' => '001', 'job_position' => 'Customer Service', 'group_jabatan' => 'Staf'],
        ];
        
        if (isset($dummyUsers[$idPeg]) && $dummyUsers[$idPeg]['password'] === $password) {
            $u = $dummyUsers[$idPeg];
            $_SESSION['user_data'] = [
                'token' => 'dummy',
                'employee_id' => $idPeg,
                'full_name' => $u['full_name'],
                'role' => $u['role'],
                'permissions' => $u['permissions'],
                'branch' => $u['branch_name'],
                'kode_kantor' => $u['kode'],
                'job_position' => $u['job_position'],
                'group_jabatan' => $u['group_jabatan'],
            ];
            header('Location: ' . BASE_APP . '/home');
            exit;
        } else {
            $_SESSION['login_error'] = 'ID Pegawai atau Password salah.';
        }
    }
}

if ($url === '') {
    // Kalau sudah login (ada session), langsung ke home; jika belum, ke login.
    $url = !empty($_SESSION['user_data']) ? 'home' : 'login';
}

// Pisahkan antara halaman (page) dan parameter (param)
[$page, $param] = array_pad(explode('/', $url, 2), 2, null);

// =========================
// GUARD: halaman yang butuh login
// =========================
$publicPages = ['login', 'reset'];
if (!in_array($page, $publicPages, true) && empty($_SESSION['user_data'])) {
    header('Location: ' . BASE_APP . '/login');
    exit;
}

// =========================
// KEAMANAN (SECURITY FIX)
// =========================
// Mencegah Path Traversal (Hacker memasukkan URL ../../ untuk baca file rahasia)
$page = basename($page); 

$baseDir = __DIR__;

// Lempar variabel $page biar bisa dibaca sama navbar.php untuk nentuin menu aktif
$current_page = $page; 

// =========================
// HEADER
// =========================
include $baseDir . "/views/header.php";

// Halaman login tidak usah menampilkan navbar
if ($page !== 'login') {
    include $baseDir . "/views/navbar.php";
}

// =========================
// LOAD PAGE
// =========================
$path = $baseDir . "/pages/{$page}.php";

if (is_file($path)) {
    if ($param !== null) {
        // Cegah serangan XSS pada parameter URL
        $_GET['id'] = htmlspecialchars($param, ENT_QUOTES, 'UTF-8'); 
    }
    include $path;
} else {
    // Tampilkan pesan 404 jika file halaman tidak ditemukan
    http_response_code(404);
    echo "<div class='container mt-5 pt-5 text-center'>
            <h1 class='text-danger' style='font-size: 5rem;'>404</h1>
            <h4>Halaman Tidak Ditemukan</h4>
            <p>Halaman <b>" . htmlspecialchars($page) . "</b> belum dibuat brokuu!</p>
            <a href='" . BASE_APP . "' class='btn btn-primary mt-3'>Kembali ke Home</a>
          </div>";
}

// =========================
// FOOTER / SCRIPT
// =========================
include $baseDir . "/views/script.php";
// include $baseDir . "/views/footer.php"; // Buka comment ini kalau nanti punya file footer khusus
?>
