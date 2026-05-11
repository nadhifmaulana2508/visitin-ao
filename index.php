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
// SSO COOKIE -> SESSION BRIDGE
// =========================
// Kalau cookie sso_token ada tapi session belum terisi, anggap user sudah login.
// Ini memungkinkan auto-login lintas aplikasi internal (monbis, report-dpk, dll).
if (!empty($_COOKIE['sso_token']) && empty($_SESSION['user_data'])) {
    $_SESSION['user_data'] = ['token' => $_COOKIE['sso_token']];
}

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
if ($url === '') {
    // Kalau sudah login (ada SSO token), langsung ke home; jika belum, ke login.
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