<?php
/**
 * Database Configuration - Dual Connection
 * 
 * 1. DPK (dpk) - Data Prospek & Kunjungan
 *    Menyimpan: prospects, kunjungan, mapping, kode_kantor, dll.
 * 
 * 2. SIMPEG (masq2971_simpeg_dummy) - Data Kepegawaian
 *    Read-only: tb_pegawai, tb_jabatan, tb_master_jabatan, tb_kantor
 *    Digunakan untuk: validasi user, ambil profil, filter AO per cabang.
 */

class Database
{
    private static ?PDO $dpkConn = null;
    private static ?PDO $simpegConn = null;

    private static function connect(string $host, string $port, string $name, string $user, string $pass): PDO
    {
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
    }

    private static function isLocalRequest(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return str_contains($host, 'localhost')
            || str_contains($host, '127.0.0.1')
            || str_contains($host, '::1');
    }

    private static function localCandidates(string $name): array
    {
        return [
            ['localhost', '3306', $name, 'root', ''],
            ['127.0.0.1', '3306', $name, 'root', ''],
        ];
    }

    private static function connectWithFallback(string $label, string $host, string $port, string $name, string $user, string $pass): PDO
    {
        $errors = [];
        $candidates = [[$host, $port, $name, $user, $pass]];

        if (self::isLocalRequest()) {
            foreach (self::localCandidates($name) as $candidate) {
                if (!in_array($candidate, $candidates, true)) {
                    $candidates[] = $candidate;
                }
            }
        }

        foreach ($candidates as [$tryHost, $tryPort, $tryName, $tryUser, $tryPass]) {
            try {
                return self::connect($tryHost, $tryPort, $tryName, $tryUser, $tryPass);
            } catch (PDOException $e) {
                $errors[] = $e->getCode() . ': ' . $e->getMessage();
            }
        }

        $hint = self::isLocalRequest()
            ? "Pastikan MySQL XAMPP aktif, database `{$name}` sudah dibuat, dan user `root` bisa login tanpa password."
            : "Periksa host, port, nama database, user, dan password di file .env.";

        sendResponse(500, "Koneksi database {$label} gagal. {$hint}", [
            'last_error' => end($errors),
        ]);
    }

    /**
     * Get DPK database connection (prospek, kunjungan, mapping)
     */
    public static function getDpk(): PDO
    {
        if (self::$dpkConn === null) {
            $host = env('DB_HOST', 'localhost');
            $port = env('DB_PORT', env('DB_ROOT', '3306'));
            $name = env('DB_NAME', 'dpk');
            $user = env('DB_USER', 'root');
            $pass = env('DB_PASS', '');

            self::$dpkConn = self::connectWithFallback('DPK', $host, $port, $name, $user, $pass);
        }

        return self::$dpkConn;
    }

    /**
     * Get SIMPEG database connection (data pegawai - read only)
     */
    public static function getSimpeg(): PDO
    {
        if (self::$simpegConn === null) {
            $host = env('SIMPEG_DB_HOST', env('DB_HOST', 'localhost'));
            $port = env('SIMPEG_DB_PORT', env('SIMPEG_DB_ROOT', env('DB_PORT', env('DB_ROOT', '3306'))));
            $name = env('SIMPEG_DB_NAME', 'masq2971_simpeg_dummy');
            $user = env('SIMPEG_DB_USER', env('DB_USER', 'root'));
            $pass = env('SIMPEG_DB_PASS', env('DB_PASS', ''));

            self::$simpegConn = self::connectWithFallback('SIMPEG', $host, $port, $name, $user, $pass);
        }

        return self::$simpegConn;
    }

    /**
     * Alias: backward-compatible getConnection() -> DPK
     */
    public function getConnection(): PDO
    {
        return self::getDpk();
    }

    /**
     * Get pegawai aktif dari SIMPEG (cached per request)
     * 
     * @param string|null $kodeKantor Filter by kode_kantor (null = semua)
     * @param string|null $groupJabatan Filter by group_jabatan (null = semua)
     * @return array
     */
    public static function getPegawaiAktif(?string $kodeKantor = null, ?string $groupJabatan = null): array
    {
        $db = self::getSimpeg();

        $sql = "SELECT
                    k.kode_cabang AS kode_kantor,
                    j.id_peg AS employee_id,
                    p.nama AS full_name,
                    p.nip AS nik,
                    p.email,
                    p.telp,
                    j.unit_kerja AS kode_unit_kerja,
                    k.nama_kantor AS branch_name,
                    mj.nama_unit_kerja AS unit_kerja,
                    mj.nama_jabatan AS job_position,
                    mj.level,
                    mj.group_jabatan
                FROM tb_jabatan j
                INNER JOIN tb_pegawai p ON j.id_peg = p.id_peg
                INNER JOIN tb_master_jabatan mj ON CAST(j.kode_jabatan AS CHAR) = CAST(mj.kode_jabatan AS CHAR)
                LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
                WHERE j.status_jab = 'Aktif'";

        $params = [];

        if ($kodeKantor !== null) {
            $sql .= " AND k.kode_cabang = :kode_kantor";
            $params[':kode_kantor'] = $kodeKantor;
        }

        if ($groupJabatan !== null) {
            $sql .= " AND mj.group_jabatan = :group_jabatan";
            $params[':group_jabatan'] = $groupJabatan;
        }

        $sql .= " ORDER BY p.nama ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get single pegawai by id_peg
     */
    public static function getPegawaiById(string $idPeg): ?array
    {
        $db = self::getSimpeg();

        $sql = "SELECT
                    k.kode_cabang AS kode_kantor,
                    j.id_peg AS employee_id,
                    p.nama AS full_name,
                    p.nip AS nik,
                    p.email,
                    p.telp,
                    j.unit_kerja AS kode_unit_kerja,
                    k.nama_kantor AS branch_name,
                    mj.nama_unit_kerja AS unit_kerja,
                    mj.nama_jabatan AS job_position,
                    mj.level,
                    mj.group_jabatan
                FROM tb_jabatan j
                INNER JOIN tb_pegawai p ON j.id_peg = p.id_peg
                INNER JOIN tb_master_jabatan mj ON CAST(j.kode_jabatan AS CHAR) = CAST(mj.kode_jabatan AS CHAR)
                LEFT JOIN tb_kantor k ON j.unit_kerja = k.kode_kantor_detail
                WHERE j.status_jab = 'Aktif' AND j.id_peg = :id_peg
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([':id_peg' => $idPeg]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    /**
     * Get daftar kode_kantor (dari DB DPK)
     * 
     * @param string|null $korwil Filter by korwil (null = semua)
     * @return array
     */
    public static function getKodeKantor(?string $korwil = null): array
    {
        $db = self::getDpk();

        $columns = self::getTableColumns($db, 'kode_kantor');
        $where = [];
        $params = [];

        if (in_array('is_active', $columns, true)) {
            $where[] = "is_active = 1";
        }

        if ($korwil !== null && $korwil !== '' && $korwil !== 'all') {
            $where[] = "korwil = :korwil";
            $params[':korwil'] = $korwil;
        }

        $sql = "SELECT * FROM kode_kantor";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY kode_kantor ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get kode_kantor yang termasuk dalam korwil tertentu
     * Return array of kode strings, e.g. ['001','002',...]
     */
    public static function getKodeKantorByKorwil(string $korwil): array
    {
        $list = self::getKodeKantor($korwil);
        return array_column($list, 'kode_kantor');
    }

    private static function getTableColumns(PDO $db, string $table): array
    {
        try {
            $stmt = $db->query("SHOW COLUMNS FROM `{$table}`");
            return array_column($stmt->fetchAll(), 'Field');
        } catch (Throwable $e) {
            return [];
        }
    }
}
