<?php
// --- /api/config/database.php ---

class Database {
    private $host = "localhost";
    private $db_name = "db_kunjungan"; // Ganti nama DB kamu nanti
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            // Mode error exception biar gampang debug
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        } catch(PDOException $exception) {
            echo json_encode(["status" => "error", "message" => "Koneksi Database Gagal: " . $exception->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}
?>