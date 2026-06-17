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

    /**
     * Get DPK database connection (prospek, kunjungan, mapping)
     */
    public static function getDpk(): PDO
    {
        if (self::$dpkConn === null) {
            $host = env('DB_HOST', 'localhost');
            $port = env('DB_PORT', '3306');
            $name = env('DB_NAME', 'dpk');
            $user = env('DB_USER', 'root');
            $pass = env('DB_PASS', '');

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            try {
                self::$dpkConn = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                sendResponse(500, 'Koneksi database DPK gagal: ' . $e->getMessage(), null);
            }
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
            $port = env('SIMPEG_DB_PORT', env('DB_PORT', '3306'));
            $name = env('SIMPEG_DB_NAME', 'masq2971_simpeg_dummy');
            $user = env('SIMPEG_DB_USER', env('DB_USER', 'root'));
            $pass = env('SIMPEG_DB_PASS', env('DB_PASS', ''));

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            try {
                self::$simpegConn = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]);
            } catch (PDOException $e) {
                sendResponse(500, 'Koneksi database SIMPEG gagal: ' . $e->getMessage(), null);
            }
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

        $sql = "SELECT * FROM kode_kantor WHERE is_active = 1";
        $params = [];

        if ($korwil !== null && $korwil !== '' && $korwil !== 'all') {
            $sql .= " AND korwil = :korwil";
            $params[':korwil'] = $korwil;
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
}
