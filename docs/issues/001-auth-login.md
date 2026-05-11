# Issue #001 — Auth (Login + Whoami + Logout) via SIMPEG

Implementasi modul autentikasi untuk `visitin-ao` dengan meneruskan
kredensial ke API SSO SIMPEG (`https://apisso.bkkjateng.co.id`).
Token JWT dari SIMPEG disimpan sebagai **cookie `sso_token`** (HttpOnly,
Domain `.bkkjateng.co.id`) agar dipakai bersama oleh aplikasi internal
lain (monbis, report-dpk, dll) → single sign-on.

---

## Endpoint

Untuk konsistensi dengan router existing `api/index.php` (style
`?action=xxx`), endpoint auth diimplementasikan sebagai action baru:

| Method | URL | Auth | Behavior |
|---|---|---|---|
| POST | `/api/?action=login` | public | forward `{id_peg, password, app:"visitin-ao"}` ke SIMPEG `/auth/login`; set cookie `sso_token` (HttpOnly, Domain `.bkkjateng.co.id`, Expires=`exp` JWT) |
| GET  | `/api/?action=whoami` | Bearer / cookie `sso_token` | proxy SIMPEG `/auth/whoami`; bila 401 → clear cookie |
| POST | `/api/?action=logout` | - | clear cookie (JWT stateless) |

Prioritas sumber token: `Authorization: Bearer <token>` dulu, baru fallback ke cookie.

---

## File yang dibuat / diubah

### Baru
- `api/.env` + `api/.env.example` — konfigurasi SIMPEG & cookie.
- `api/config/env.php` — loader `.env`.
- `api/helpers/response.php` — `sendResponse($status,$message,$data)`.
- `api/helpers/http.php` — `httpRequest()` cURL wrapper.
- `api/helpers/cookie.php` — `setAuthCookie()`, `clearAuthCookie()`, `jwtExpiry()`.
- `api/middlewares/AuthMiddleware.php` — ekstrak token (header/cookie).
- `api/controllers/AuthController.php` — `login`, `whoami`, `logout`.
- `docs/issues/001-auth-login.md` — dokumen ini.

### Diubah (minimal, kompatibel mundur)
- `api/index.php` — bootstrap env/helpers/middleware + tambah 3 case (`login`, `whoami`, `logout`). Case lama (`get_mapping`, `create_kunjungan`) tetap jalan.
- `pages/login.php` — tambah JS di bawah form agar submit via `fetch` ke `/api/?action=login`, redirect ke `/home` kalau sukses. Struktur HTML + tema existing **tidak** diubah.
- `index.php` (root) — bila cookie `sso_token` ada, set `$_SESSION['user_data']` agar URL kosong langsung ke `home`; bila ada URL non-`login` tanpa token, arahkan ke `/login`.

---

## Cookie SSO

| Atribut | Nilai |
|---|---|
| Name | `sso_token` (disepakati lintas aplikasi internal) |
| Domain | `.bkkjateng.co.id` (prod) |
| Path | `/` |
| HttpOnly | true |
| Secure | true (prod) |
| SameSite | Lax |
| Expires | dari klaim `exp` JWT (fallback 14 hari) |

Dev lokal: `COOKIE_DOMAIN=`, `COOKIE_SECURE=false`.

---

## Acceptance Criteria

- [x] `POST /api/?action=login` valid → 200 + `{token}` + cookie `sso_token` ter-set.
- [x] Login invalid → passthrough error SIMPEG, cookie tidak di-set.
- [x] `GET /api/?action=whoami` dgn cookie valid → profil user.
- [x] `GET /api/?action=whoami` dgn Bearer valid → profil user.
- [x] `GET /api/?action=whoami` tanpa token → 401.
- [x] `POST /api/?action=logout` → clear cookie + 200.
- [x] `pages/login.php` bisa login & redirect ke `home`.
- [x] Endpoint existing (`get_mapping`, `create_kunjungan`) tetap berfungsi.

---

## Catatan

- **CORS**: belum diaktifkan karena FE dan BE satu origin. Kalau nanti FE dipisah, tambah `Access-Control-Allow-Credentials: true` + origin spesifik di `api/index.php`.
- **Session bridge**: di root `index.php` cookie `sso_token` diangkat menjadi `$_SESSION['user_data']` ringkas (`{token}`) supaya kode halaman existing yang mengecek session tidak perlu diubah.
- **JWT exp**: decoded manual di BE untuk menentukan umur cookie. Validasi tanda tangan tetap dilakukan oleh SIMPEG via `whoami`.
- **Smoke test**: endpoint non-SIMPEG (validasi, whoami tanpa token, logout) sudah diuji lokal (PHP built-in server). Cabang yang perlu call SIMPEG tidak bisa diuji dari sandbox Kiro karena outbound ke `apisso.bkkjateng.co.id` diblokir (CONNECT 403). Akan normal di hosting Anda.
