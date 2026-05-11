# Issue #001 — Implementasi Auth (Login + Whoami) via SIMPEG

## Ringkasan

Membangun modul autentikasi untuk aplikasi `visitin-ao` dengan memanfaatkan
API SSO SIMPEG BKK Jateng. Auth ini menjadi fondasi untuk semua endpoint
dan halaman yang membutuhkan identitas user.

Token hasil login **disimpan sebagai cookie** agar dapat dipakai
lintas-aplikasi internal (monbis, report-dpk, visitin-ao, dll) sehingga
user yang sudah login di salah satu aplikasi otomatis login di aplikasi
lain (single sign-on lewat shared cookie domain).

---

## Tujuan

- Struktur backend ala REST API yang konsisten dengan project `report-dpk`
  (folder `api/config`, `controllers`, `helpers`, `middlewares`, `routers`).
- Endpoint `POST /api/auth/login` meneruskan kredensial ke SIMPEG,
  menerima token JWT, mengembalikannya ke FE **dan** menyetel cookie
  `sso_token` pada response.
- Endpoint `GET /api/auth/whoami` memvalidasi token (Bearer atau cookie),
  memanggil SIMPEG untuk ambil profil user, dan mengembalikan data user.
- Halaman `pages/login.php` sebagai UI login sederhana yang memanggil
  endpoint di atas dan melakukan redirect ke dashboard bila sukses.

---

## Referensi

- SIMPEG base URL: `https://apisso.bkkjateng.co.id/`
- Request login SIMPEG: `POST /auth/login` body `{id_peg, password, app}`
- Response login SIMPEG: `{status:200, message, data:{token}}`
- Request whoami SIMPEG: `GET /auth/whoami` header `Authorization: Bearer <token>`
- Response whoami SIMPEG: profil user (employee_id, full_name, email, telp,
  branch_name, unit_kerja, job_position, level, group_jabatan, kode)

---

## Struktur File

```
visitin-ao/
  .htaccess                       # Rewrite root: /api/* -> api/index.php
  index.php                       # Entry FE: redirect ke login / dashboard
  api/
    .env / .env.example           # Konfigurasi SIMPEG & cookie
    .htaccess
    index.php                     # Front-controller
    config/env.php                # Loader .env
    helpers/response.php          # sendResponse()
    helpers/http.php              # httpRequest() cURL wrapper
    helpers/cookie.php            # setAuthCookie() / clearAuthCookie()
    middlewares/AuthMiddleware.php
    controllers/AuthController.php
    routers/auth.php
  pages/login.php
  views/{header,navbar,script}.php
```

---

## Endpoint

### POST /api/auth/login
- Body: `{id_peg, password}` (server meng-inject `app=visitin-ao`)
- Forward ke SIMPEG `/auth/login`. Bila 200 → set cookie `sso_token`, return token.
- Lain-lain → passthrough status + message dari SIMPEG.

### GET /api/auth/whoami
- Token dari `Authorization: Bearer` atau cookie `sso_token` (header prioritas).
- Forward ke SIMPEG `/auth/whoami`. Passthrough response.
- Bila 401 dari SIMPEG → cookie di-clear.

### POST /api/auth/logout
- Clear cookie (JWT stateless → tidak perlu call SIMPEG).

---

## Cookie SSO

| Atribut | Nilai |
|---|---|
| Name | `sso_token` (disepakati lintas aplikasi) |
| Domain | `.bkkjateng.co.id` (share antar subdomain) |
| Path | `/` |
| HttpOnly | true |
| Secure | true (prod) |
| SameSite | Lax |
| Expires | dari klaim `exp` JWT (fallback 14 hari) |

Di dev lokal: `COOKIE_DOMAIN` kosong, `COOKIE_SECURE=false`.

---

## .env

```ini
SIMPEG_BASE_URL=https://apisso.bkkjateng.co.id
APP_NAME=visitin-ao
COOKIE_NAME=sso_token
COOKIE_DOMAIN=.bkkjateng.co.id
COOKIE_SECURE=true
COOKIE_SAMESITE=Lax
COOKIE_PATH=/
```

---

## Acceptance Criteria

- [x] `POST /api/auth/login` dengan kredensial valid → 200, token, cookie ter-set.
- [x] `POST /api/auth/login` kredensial invalid → passthrough error SIMPEG, cookie tidak di-set.
- [x] `GET /api/auth/whoami` dgn cookie valid → profil user.
- [x] `GET /api/auth/whoami` dgn Bearer valid → profil user.
- [x] `GET /api/auth/whoami` tanpa token → 401.
- [x] `POST /api/auth/logout` → hapus cookie.
- [x] `pages/login.php` bisa login & redirect ke root.
- [x] `index.php` root redirect belum-login ke login, sudah-login ke dashboard.
- [x] Struktur folder `api/*` konsisten dengan referensi `report-dpk`.

---

## Catatan / Risiko

- **CORS**: aktif di `api/index.php` bila FE beda origin (credential: include).
- **Cookie domain dev**: browser menolak `Domain=.bkkjateng.co.id` di localhost. Kosongkan `COOKIE_DOMAIN` untuk dev.
- **JWT exp**: decoded manual (base64url) untuk menentukan umur cookie. Signature tidak divalidasi di BE kita — itu tugas SIMPEG via `whoami`.
- **Smoke test lokal**: 5/6 cabang hijau. Cabang "login valid" tidak bisa di-test dari sandbox Kiro karena sandbox `INTEGRATIONS_ONLY` (CONNECT tunnel 403). Jalan normal di hosting Anda.
