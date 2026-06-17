<?php

/**
 * AuthController - DUMMY MODE (tanpa SSO SIMPEG)
 * 
 * Menggunakan hardcoded users untuk development.
 * Setiap role memiliki akun dummy masing-masing.
 */
class AuthController
{
    /**
     * Daftar user dummy untuk development.
     * Format: id_peg => [password, data_user]
     */
    private static function getDummyUsers(): array
    {
        return [
            '102-119' => [
                'password' => '123456',
                'data' => [
                    'kode' => '000',
                    'employee_id' => '102-119',
                    'full_name' => 'SYAIFUN NADHIF MAULANA, S. Kom',
                    'email' => 'syaifunnadhif@gmail.com',
                    'telp' => '088228659668',
                    'branch_name' => 'Kantor Pusat',
                    'unit_kerja' => 'Divisi Operasional',
                    'job_position' => 'Staf Sistem dan Jaringan TI',
                    'level' => 'Staf',
                    'group_jabatan' => 'Staf',
                    'role' => 'developer',
                    'permissions' => ['DEV', 'SUPERUSER_PROSPEK', 'AO_KREDIT', 'AO_DANA', 'AO_REMEDIAL_FE', 'AO_REMEDIAL_BE'],
                ]
            ],
            '201-001' => [
                'password' => '123456',
                'data' => [
                    'kode' => '001',
                    'employee_id' => '201-001',
                    'full_name' => 'BUDI SANTOSO',
                    'email' => 'budi.santoso@bkkjateng.co.id',
                    'telp' => '081234567890',
                    'branch_name' => 'Cabang Utama',
                    'unit_kerja' => 'Divisi Pemasaran',
                    'job_position' => 'Account Officer Kredit',
                    'level' => 'AO',
                    'group_jabatan' => 'AO Kredit',
                    'role' => 'ao_kredit',
                    'permissions' => ['AO_KREDIT'],
                ]
            ],
            '201-002' => [
                'password' => '123456',
                'data' => [
                    'kode' => '002',
                    'employee_id' => '201-002',
                    'full_name' => 'SITI RAHAYU',
                    'email' => 'siti.rahayu@bkkjateng.co.id',
                    'telp' => '081298765432',
                    'branch_name' => 'Cabang Utama',
                    'unit_kerja' => 'Divisi Pemasaran',
                    'job_position' => 'Account Officer Dana',
                    'level' => 'AO',
                    'group_jabatan' => 'AO Dana',
                    'role' => 'ao_dana',
                    'permissions' => ['AO_DANA'],
                ]
            ],
            '201-003' => [
                'password' => '123456',
                'data' => [
                    'kode' => '003',
                    'employee_id' => '201-003',
                    'full_name' => 'ANDI SETIAWAN',
                    'email' => 'andi.setiawan@bkkjateng.co.id',
                    'telp' => '081345678901',
                    'branch_name' => 'Cabang Utama',
                    'unit_kerja' => 'Divisi Penyelamatan Kredit',
                    'job_position' => 'Account Officer Remedial',
                    'level' => 'AO',
                    'group_jabatan' => 'AO Remedial',
                    'role' => 'ao_remedial',
                    'permissions' => ['AO_REMEDIAL_FE', 'AO_REMEDIAL_BE'],
                ]
            ],
            '201-004' => [
                'password' => '123456',
                'data' => [
                    'kode' => '004',
                    'employee_id' => '201-004',
                    'full_name' => 'WAHYU HIDAYAT',
                    'email' => 'wahyu.hidayat@bkkjateng.co.id',
                    'telp' => '081456789012',
                    'branch_name' => 'Cabang Utama',
                    'unit_kerja' => 'Divisi Pemasaran',
                    'job_position' => 'Kepala Bidang Pemasaran',
                    'level' => 'Kabid',
                    'group_jabatan' => 'Pejabat',
                    'role' => 'superuser',
                    'permissions' => ['SUPERUSER_PROSPEK'],
                ]
            ],
            '201-005' => [
                'password' => '123456',
                'data' => [
                    'kode' => '005',
                    'employee_id' => '201-005',
                    'full_name' => 'DEWI KUSUMA',
                    'email' => 'dewi.kusuma@bkkjateng.co.id',
                    'telp' => '081567890123',
                    'branch_name' => 'Cabang Utama',
                    'unit_kerja' => 'Divisi Operasional',
                    'job_position' => 'Teller',
                    'level' => 'Staf',
                    'group_jabatan' => 'Staf',
                    'role' => 'staff',
                    'permissions' => [],
                ]
            ],
            '201-006' => [
                'password' => '123456',
                'data' => [
                    'kode' => '006',
                    'employee_id' => '201-006',
                    'full_name' => 'RATNA SARI',
                    'email' => 'ratna.sari@bkkjateng.co.id',
                    'telp' => '081678901234',
                    'branch_name' => 'Cabang Utama',
                    'unit_kerja' => 'Divisi Operasional',
                    'job_position' => 'Customer Service',
                    'level' => 'Staf',
                    'group_jabatan' => 'Staf',
                    'role' => 'staff',
                    'permissions' => [],
                ]
            ],
        ];
    }

    /**
     * Generate dummy JWT-like token (base64 encoded user data)
     */
    private static function generateDummyToken(array $userData): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'sub' => $userData['employee_id'],
            'name' => $userData['full_name'],
            'role' => $userData['role'],
            'permissions' => $userData['permissions'],
            'branch' => $userData['branch_name'],
            'iat' => time(),
            'exp' => time() + (14 * 24 * 60 * 60), // 14 hari
        ]));
        $signature = base64_encode(hash_hmac('sha256', "$header.$payload", 'dummy-secret-key', true));
        
        return "$header.$payload.$signature";
    }

    /**
     * Decode dummy token dan return user data
     */
    public static function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = json_decode(base64_decode($parts[1]), true);
        if (!$payload || !isset($payload['sub'])) {
            return null;
        }

        // Cek expired
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * LOGIN - Dummy (tanpa SSO)
     */
    public function login(array $input): void
    {
        $idPeg    = trim($input['id_peg'] ?? ($input['nik'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($idPeg === '' || $password === '') {
            sendResponse(400, 'id_peg dan password wajib diisi', null);
        }

        $users = self::getDummyUsers();

        // Cari user
        if (!isset($users[$idPeg])) {
            sendResponse(401, 'ID Pegawai tidak ditemukan', null);
        }

        $user = $users[$idPeg];

        // Validasi password
        if ($password !== $user['password']) {
            sendResponse(401, 'Password salah', null);
        }

        // Generate token
        $token = self::generateDummyToken($user['data']);

        // Set cookie
        setAuthCookie($token);

        // Response sesuai format yang diminta
        sendResponse(200, 'OK', [
            'token' => $token,
            'kode' => $user['data']['kode'],
            'employee_id' => $user['data']['employee_id'],
            'full_name' => $user['data']['full_name'],
            'email' => $user['data']['email'],
            'telp' => $user['data']['telp'],
            'branch_name' => $user['data']['branch_name'],
            'unit_kerja' => $user['data']['unit_kerja'],
            'job_position' => $user['data']['job_position'],
            'level' => $user['data']['level'],
            'group_jabatan' => $user['data']['group_jabatan'],
            'role' => $user['data']['role'],
            'permissions' => $user['data']['permissions'],
        ]);
    }

    /**
     * WHOAMI - Decode token dan return profil user
     */
    public function whoami(): void
    {
        $token = AuthMiddleware::getToken();
        if (!$token) {
            sendResponse(401, 'Unauthorized', null);
        }

        $payload = self::decodeToken($token);
        if (!$payload) {
            clearAuthCookie();
            sendResponse(401, 'Token tidak valid atau sudah expired', null);
        }

        // Cari data lengkap user dari dummy
        $users = self::getDummyUsers();
        $employeeId = $payload['sub'];

        if (isset($users[$employeeId])) {
            $userData = $users[$employeeId]['data'];
            sendResponse(200, 'OK', $userData);
        }

        // Fallback: return dari payload saja
        sendResponse(200, 'OK', [
            'employee_id' => $payload['sub'],
            'full_name' => $payload['name'],
            'role' => $payload['role'],
            'permissions' => $payload['permissions'],
            'branch_name' => $payload['branch'] ?? '',
        ]);
    }

    /**
     * LOGOUT
     */
    public function logout(): void
    {
        clearAuthCookie();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        sendResponse(200, 'Logout berhasil', null);
    }
}
