# Deploy aaPanel

## 1. File `.env`

Letakkan `.env` di root project atau di folder `api/.env`. Keduanya akan dibaca aplikasi.

Contoh production:

```env
DB_HOST=127.0.0.1
DB_USER=nama_user_db
DB_PASS=password_db
DB_NAME=dpk
DB_PORT=3306

SIMPEG_DB_HOST=127.0.0.1
SIMPEG_DB_USER=nama_user_db
SIMPEG_DB_PASS=password_db
SIMPEG_DB_NAME=masq2971_simpeg_dummy
SIMPEG_DB_PORT=3306

SSO_BASE_URL=https://apisso.bkkjateng.co.id
SSO_APP=ims
SSO_AUTH_MODE=http
SSO_DB_FALLBACK=false
# SSO_DB_APP_COLUMN=ims
HTTP_TIMEOUT=30
HTTP_CONNECT_TIMEOUT=10
HTTP_SSL_VERIFY=true
HTTP_IP_RESOLVE=4
# HTTP_PROXY=http://proxy-host:proxy-port
LOCAL_LOGIN_FALLBACK=false

COOKIE_NAME=sso_token
COOKIE_DOMAIN=.bkkjateng.co.id
COOKIE_SECURE=true
COOKIE_SAMESITE=Lax
COOKIE_PATH=/
```

Jika server aaPanel belum punya CA bundle yang benar dan muncul error SSL certificate, install/update `ca-certificates`. Opsi sementara untuk diagnosis:

```env
HTTP_SSL_VERIFY=false
```

## 2. Rewrite Nginx aaPanel

Kalau website memakai Nginx/LNMP, `.htaccess` tidak dipakai. Tambahkan rewrite ini di menu Site > Rewrite:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Untuk Apache/OpenLiteSpeed, `.htaccess` bawaan project sudah cukup.

## 3. Permission folder upload

Pastikan user web server bisa menulis:

```bash
chmod -R 775 uploads
chown -R www:www uploads
```

Sesuaikan user/group `www:www` dengan user PHP-FPM di aaPanel.

## 4. Test koneksi SSO dari server

Jalankan dari terminal aaPanel:

```bash
curl -I --connect-timeout 10 https://apisso.bkkjateng.co.id/api/auth/whoami
curl -4 -I --connect-timeout 10 https://apisso.bkkjateng.co.id/api/auth/whoami
curl -X POST https://apisso.bkkjateng.co.id/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"id_peg":"102-119","password":"bkkjtg123","app":"ims"}'
```

Jika `curl` timeout, masalahnya ada di outbound network server: firewall, DNS, routing, proxy, atau IP server belum diizinkan mengakses SSO.

Jika `curl -4` berhasil tapi `curl` biasa timeout, pastikan `.env` berisi:

```env
HTTP_IP_RESOLVE=4
```

Jika Monbis memakai proxy internal, samakan proxy itu di:

```env
HTTP_PROXY=http://proxy-host:proxy-port
```

## 5. Mode SSO via DB SIMPEG

Repo SSO `rest_api_sso` melakukan login langsung ke database SIMPEG (`tb_apk`, `tb_pegawai`, `tb_jabatan`, `tb_master_jabatan`, `tb_kantor`). Jika dari server aaPanel request ke `https://apisso.bkkjateng.co.id` timeout, tetapi database SIMPEG bisa diakses, gunakan mode ini:

```env
SSO_AUTH_MODE=db
SSO_APP=ims
SSO_DB_APP_COLUMN=ims
```

Mode `db` mengecek password dari `tb_apk.pass` dengan `password_verify`, mengecek akses aplikasi dari kolom app, lalu mengambil profil pegawai aktif dari SIMPEG. Jika kolom app di database berbeda, isi `SSO_DB_APP_COLUMN` sesuai nama kolomnya.

Jika tetap ingin mencoba HTTP SSO dulu lalu fallback ke DB hanya saat koneksi timeout:

```env
SSO_AUTH_MODE=http
SSO_DB_FALLBACK=true
```

Setelah mengubah `.env`, restart PHP-FPM dari aaPanel agar konfigurasi terbaca ulang.

## 6. Fallback akun demo sementara

Jika SSO belum bisa diakses dari server, akun demo bisa diizinkan login lokal sementara:

```env
LOCAL_LOGIN_FALLBACK=true
```

Fallback ini hanya dipakai saat koneksi SSO gagal total dan password harus cocok dengan daftar akun demo di aplikasi. Untuk production penuh, set kembali:

```env
LOCAL_LOGIN_FALLBACK=false
```
