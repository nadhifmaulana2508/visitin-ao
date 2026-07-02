<?php

/**
 * AuthController - SSO login + local role mapping.
 * 
 * Autentikasi tetap ke SSO SIMPEG, sementara role aplikasi Visitin AO
 * masih dipetakan lokal berdasarkan id_peg.
 */
class AuthController
{
    /**
     * Daftar role lokal untuk development/permission aplikasi.
     * Password lama dibiarkan agar data dummy tetap mudah dikenali,
     * tetapi login tidak lagi memvalidasi password ini.
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
            '102-118' => [
                'password' => 'bkkjtg123',
                'data' => [
                    'kode' => '000',
                    'employee_id' => '102-118',
                    'full_name' => 'STAFF DEMO',
                    'email' => '',
                    'telp' => '',
                    'branch_name' => 'Kantor Pusat',
                    'unit_kerja' => 'Divisi Operasional',
                    'job_position' => 'Staf',
                    'level' => 'Staf',
                    'group_jabatan' => 'Staf',
                    'role' => 'staff',
                    'permissions' => [],
                ]
            ],
            '128-001' => [
                'password' => 'bkkjtg123',
                'data' => [
                    'kode' => '000',
                    'employee_id' => '128-001',
                    'full_name' => 'BAMBANG RISMANTO, SE',
                    'email' => 'brismanto1970@gmail.com',
                    'telp' => '087700720888',
                    'branch_name' => 'Kantor Wilayah Pekalongan',
                    'unit_kerja' => 'Area Kantor Wilayah',
                    'job_position' => 'Kepala Kantor Wilayah',
                    'level' => 'Kepala Kantor Wilayah',
                    'group_jabatan' => 'PE',
                    'role' => 'superuser',
                    'permissions' => ['SUPERUSER_PROSPEK'],
                    'access_korwil' => 'pekalongan',
                ]
            ],
            '113-008' => [
                'password' => 'bkkjtg123',
                'data' => [
                    'kode' => '008',
                    'employee_id' => '113-008',
                    'full_name' => 'SUPERUSER CABANG DEMO',
                    'email' => '',
                    'telp' => '',
                    'branch_name' => 'Kc. Wonogiri',
                    'unit_kerja' => 'Cabang',
                    'job_position' => 'Kepala Bidang Pemasaran',
                    'level' => 'Pejabat',
                    'group_jabatan' => 'PE',
                    'role' => 'superuser',
                    'permissions' => ['SUPERUSER_PROSPEK'],
                ]
            ],
            '111-027' => [
                'password' => 'bkkjtg123',
                'data' => [
                    'kode' => '027',
                    'employee_id' => '111-027',
                    'full_name' => 'AO REMEDIAL DEMO',
                    'email' => '',
                    'telp' => '',
                    'branch_name' => 'Kc. Kab. Pekalongan',
                    'unit_kerja' => 'Cabang',
                    'job_position' => 'Account Officer Remedial',
                    'level' => 'AO',
                    'group_jabatan' => 'AO Remedial',
                    'role' => 'ao_remedial',
                    'permissions' => ['AO_REMEDIAL_FE', 'AO_REMEDIAL_BE'],
                ]
            ],
            '111-028' => [
                'password' => 'bkkjtg123',
                'data' => [
                    'kode' => '028',
                    'employee_id' => '111-028',
                    'full_name' => 'AO KREDIT DEMO',
                    'email' => '',
                    'telp' => '',
                    'branch_name' => 'Kc. Batang',
                    'unit_kerja' => 'Cabang',
                    'job_position' => 'Account Officer Kredit',
                    'level' => 'AO',
                    'group_jabatan' => 'AO Kredit',
                    'role' => 'ao_kredit',
                    'permissions' => ['AO_KREDIT'],
                ]
            ],
            '111-007' => [
                'password' => 'bkkjtg123',
                'data' => [
                    'kode' => '007',
                    'employee_id' => '111-007',
                    'full_name' => 'AO DANA DEMO',
                    'email' => '',
                    'telp' => '',
                    'branch_name' => 'Kc. Kab. Semarang',
                    'unit_kerja' => 'Cabang',
                    'job_position' => 'Account Officer Dana',
                    'level' => 'AO',
                    'group_jabatan' => 'AO Dana',
                    'role' => 'ao_dana',
                    'permissions' => ['AO_DANA'],
                ]
            ],
            '111-026' => [
                'password' => 'bkkjtg123',
                'data' => [
                    'kode' => '026',
                    'employee_id' => '111-026',
                    'full_name' => 'STAFF CABANG DEMO',
                    'email' => '',
                    'telp' => '',
                    'branch_name' => 'Kc. Kota Pekalongan',
                    'unit_kerja' => 'Cabang',
                    'job_position' => 'Staf',
                    'level' => 'Staf',
                    'group_jabatan' => 'Staf',
                    'role' => 'staff',
                    'permissions' => [],
                ]
            ],
            '102-081' => [
                'password' => 'bkkjtg123',
                'data' => [
                    'kode' => '000',
                    'employee_id' => '102-081',
                    'full_name' => 'SUPERUSER PUSAT DEMO',
                    'email' => '',
                    'telp' => '',
                    'branch_name' => 'Kantor Pusat',
                    'unit_kerja' => 'Divisi Pemasaran',
                    'job_position' => 'Staf',
                    'level' => 'Staf',
                    'group_jabatan' => 'Staf',
                    'role' => 'superuser',
                    'permissions' => ['SUPERUSER_PROSPEK'],
                ]
            ],
        ];
    }

    public static function getLocalUser(string $idPeg): ?array
    {
        $users = self::getDummyUsers();
        return $users[$idPeg]['data'] ?? null;
    }

    private static function getLocalAccessOverride(string $idPeg, array $local): ?array
    {
        if (empty($local['role'])) {
            return null;
        }

        $demoOverrideIds = [
            '102-119', '102-118', '113-008', '111-027',
            '111-028', '111-007', '111-026', '102-081',
            '128-001',
        ];
        if (!in_array($idPeg, $demoOverrideIds, true)) {
            return null;
        }

        return [
            'role' => $local['role'],
            'permissions' => $local['permissions'] ?? [],
            'group_jabatan' => $local['group_jabatan'] ?? 'Staf',
            'access_korwil' => $local['access_korwil'] ?? null,
        ];
    }

    public static function decodeJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = strtr($parts[1], '-_', '+/');
        $pad = strlen($payload) % 4;
        if ($pad) {
            $payload .= str_repeat('=', 4 - $pad);
        }

        $json = base64_decode($payload, true);
        if ($json === false) {
            return null;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return null;
        }

        if (isset($decoded['exp']) && (int) $decoded['exp'] < time()) {
            return null;
        }

        return $decoded;
    }

    private static function normalizeText(?string $value): string
    {
        $value = strtolower(trim((string) $value));
        return preg_replace('/\s+/', ' ', $value);
    }

    private static function isBranchCode(string $kodeKantor): bool
    {
        return preg_match('/^(00[1-9]|0[1-2][0-9]|028)$/', $kodeKantor) === 1;
    }

    private static function inferKorwilFromBranch(?string $branchName): ?string
    {
        $branch = self::normalizeText($branchName);
        if (str_contains($branch, 'semarang')) return 'semarang';
        if (str_contains($branch, 'solo') || str_contains($branch, 'surakarta')) return 'solo';
        if (str_contains($branch, 'banyumas')) return 'banyumas';
        if (str_contains($branch, 'pekalongan')) return 'pekalongan';
        return null;
    }

    private static function deriveAppAccess(array $profile, array $local = []): array
    {
        $job = self::normalizeText($profile['job_position'] ?? '');
        $unit = self::normalizeText($profile['unit_kerja'] ?? '');
        $group = self::normalizeText($profile['group_jabatan'] ?? '');
        $level = self::normalizeText($profile['level'] ?? '');
        $kodeKantor = (string) ($profile['kode_kantor'] ?? '000');

        if (
            str_contains($job, 'staf sistem dan jaringan ti')
            || str_contains($job, 'kepala divisi operasional')
            || (str_contains($job, 'kepala divisi') && $unit === 'divisi operasional')
        ) {
            return [
                'role' => 'developer',
                'permissions' => ['DEV', 'SUPERUSER_PROSPEK', 'AO_KREDIT', 'AO_DANA', 'AO_REMEDIAL_FE', 'AO_REMEDIAL_BE'],
                'group_jabatan' => $profile['group_jabatan'] ?: ($local['group_jabatan'] ?? 'Staf'),
            ];
        }

        if (str_contains($job, 'ao kredit') || str_contains($job, 'account officer kredit') || str_contains($group, 'ao kredit')) {
            return ['role' => 'ao_kredit', 'permissions' => ['AO_KREDIT'], 'group_jabatan' => 'AO Kredit'];
        }

        if (str_contains($job, 'ao dana') || str_contains($job, 'account officer dana') || str_contains($group, 'ao dana')) {
            return ['role' => 'ao_dana', 'permissions' => ['AO_DANA'], 'group_jabatan' => 'AO Dana'];
        }

        if (str_contains($job, 'ao remedial') || str_contains($job, 'account officer remedial') || str_contains($group, 'ao remedial')) {
            return ['role' => 'ao_remedial', 'permissions' => ['AO_REMEDIAL_FE', 'AO_REMEDIAL_BE'], 'group_jabatan' => 'AO Remedial'];
        }

        $isAreaKorwil = $unit === 'area kantor wilayah';
        $isSuperuserUnit = in_array($unit, [
            'divisi pemasaran',
            'divisi penyelesaian kredit',
            'area kantor wilayah',
            'dewan komisaris',
            'direksi',
        ], true);
        $isBranchLeader = self::isBranchCode($kodeKantor) && (
            str_contains($job, 'kepala cabang')
            || str_contains($job, 'kepala bidang pemasaran')
            || (str_contains($job, 'kepala bidang') && str_contains($job, 'pemasaran'))
            || str_contains($level, 'kepala cabang')
            || str_contains($level, 'kepala bidang pemasaran')
            || in_array($group, ['pe', 'ps'], true)
        );
        if ($isSuperuserUnit || $isBranchLeader) {
            return [
                'role' => 'superuser',
                'permissions' => ['SUPERUSER_PROSPEK'],
                'group_jabatan' => $profile['group_jabatan'] ?: ($local['group_jabatan'] ?? 'Pejabat'),
                'access_korwil' => $isAreaKorwil ? self::inferKorwilFromBranch($profile['branch'] ?? '') : null,
            ];
        }

        return [
            'role' => 'staff',
            'permissions' => [],
            'group_jabatan' => $profile['group_jabatan'] ?: ($local['group_jabatan'] ?? 'Staf'),
            'access_korwil' => null,
        ];
    }

    public static function buildSessionUser(string $token, ?array $ssoUser = null): ?array
    {
        $payload = self::decodeJwtPayload($token) ?? [];
        $employeeId = (string) (
            $ssoUser['employee_id']
            ?? $ssoUser['id_peg']
            ?? $payload['employee_id']
            ?? $payload['id_peg']
            ?? $payload['sub']
            ?? ''
        );

        if ($employeeId === '') {
            return null;
        }

        $local = self::getLocalUser($employeeId) ?? [];
        $profile = [
            'full_name' => $ssoUser['full_name'] ?? $ssoUser['nama'] ?? $payload['name'] ?? $payload['nama'] ?? $local['full_name'] ?? '',
            'email' => $ssoUser['email'] ?? $local['email'] ?? '',
            'telp' => $ssoUser['telp'] ?? $local['telp'] ?? '',
            'branch' => $ssoUser['branch_name'] ?? $ssoUser['nama_kantor'] ?? $local['branch_name'] ?? '',
            'kode_kantor' => (string) ($ssoUser['kode'] ?? $ssoUser['kode_kantor'] ?? $ssoUser['kode_cabang'] ?? $local['kode'] ?? '000'),
            'unit_kerja' => $ssoUser['unit_kerja'] ?? $local['unit_kerja'] ?? '',
            'job_position' => $ssoUser['job_position'] ?? $ssoUser['jabatan'] ?? $local['job_position'] ?? '',
            'group_jabatan' => $ssoUser['group_jabatan'] ?? $local['group_jabatan'] ?? '',
            'level' => $ssoUser['level'] ?? $local['level'] ?? '',
        ];
        $access = self::getLocalAccessOverride($employeeId, $local) ?? self::deriveAppAccess($profile, $local);

        return [
            'token' => $token,
            'employee_id' => $employeeId,
            'full_name' => $profile['full_name'],
            'email' => $profile['email'],
            'telp' => $profile['telp'],
            'role' => $access['role'],
            'permissions' => $access['permissions'],
            'branch' => $profile['branch'],
            'kode_kantor' => $profile['kode_kantor'],
            'unit_kerja' => $profile['unit_kerja'],
            'job_position' => $profile['job_position'],
            'group_jabatan' => $access['group_jabatan'],
            'level' => $profile['level'],
            'access_korwil' => $access['access_korwil'] ?? null,
        ];
    }

    public static function authenticateAndStoreSession(string $idPeg, string $password, ?string $app = null): array
    {
        $app = $app ?: env('SSO_APP', 'ims');
        $baseUrl = rtrim((string) env('SSO_BASE_URL', env('SIMPEG_BASE_URL', 'https://apisso.bkkjateng.co.id')), '/');

        $login = httpRequest('POST', $baseUrl . '/api/auth/login', [
            'id_peg' => $idPeg,
            'password' => $password,
            'app' => $app,
        ]);

        if (($login['status'] ?? 0) !== 200 || empty($login['body']['data']['token'])) {
            $message = $login['body']['message'] ?? 'Login SSO gagal';
            throw new RuntimeException($message, (int) ($login['status'] ?: 401));
        }

        $token = (string) $login['body']['data']['token'];
        setAuthCookie($token);

        $ssoUser = self::fetchSsoUser($token);
        $sessionUser = self::buildSessionUser($token, $ssoUser);
        if (!$sessionUser) {
            clearAuthCookie();
            throw new RuntimeException('Token SSO tidak berisi id_peg yang valid', 401);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_data'] = $sessionUser;

        return [
            'token' => $token,
            'user' => $sessionUser,
        ];
    }

    public static function fetchSsoUser(string $token): ?array
    {
        $baseUrl = rtrim((string) env('SSO_BASE_URL', env('SIMPEG_BASE_URL', 'https://apisso.bkkjateng.co.id')), '/');
        $whoami = httpRequest('GET', $baseUrl . '/api/auth/whoami', null, [
            'Authorization: Bearer ' . $token,
        ]);

        if (($whoami['status'] ?? 0) !== 200 || !is_array($whoami['body']['data'] ?? null)) {
            return null;
        }

        return $whoami['body']['data'];
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
            'kode_kantor' => $userData['kode'] ?? '000',
            'job_position' => $userData['job_position'] ?? '',
            'group_jabatan' => $userData['group_jabatan'] ?? '',
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

        $payload = self::decodeJwtPayload($token);
        if (!$payload || (!isset($payload['sub']) && !isset($payload['id_peg']))) {
            return null;
        }

        return $payload;
    }

    /**
     * LOGIN - Proxy ke SSO, lalu enrich role dari mapping lokal.
     */
    public function login(array $input): void
    {
        $idPeg    = trim($input['id_peg'] ?? ($input['nik'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $app      = trim((string) ($input['app'] ?? env('SSO_APP', 'ims')));

        if ($idPeg === '' || $password === '') {
            sendResponse(400, 'id_peg dan password wajib diisi', null);
        }

        try {
            $auth = self::authenticateAndStoreSession($idPeg, $password, $app);
            sendResponse(200, 'Login berhasil', ['token' => $auth['token']]);
        } catch (RuntimeException $e) {
            $status = $e->getCode();
            if ($status < 400 || $status > 599) {
                $status = 401;
            }
            sendResponse($status, $e->getMessage(), null);
        }
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

        $ssoUser = self::fetchSsoUser($token);
        $sessionUser = self::buildSessionUser($token, $ssoUser);
        if (!$sessionUser) {
            clearAuthCookie();
            sendResponse(401, 'Token tidak valid atau sudah expired', null);
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_data'] = $sessionUser;

        sendResponse(200, 'OK', [
            'kode' => $sessionUser['kode_kantor'],
            'employee_id' => $sessionUser['employee_id'],
            'full_name' => $sessionUser['full_name'],
            'email' => $sessionUser['email'],
            'telp' => $sessionUser['telp'],
            'branch_name' => $sessionUser['branch'],
            'unit_kerja' => $sessionUser['unit_kerja'],
            'job_position' => $sessionUser['job_position'],
            'level' => $sessionUser['level'],
            'group_jabatan' => $sessionUser['group_jabatan'],
            'role' => $sessionUser['role'],
            'permissions' => $sessionUser['permissions'],
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
