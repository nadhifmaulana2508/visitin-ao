# FE Components Visitin AO

Folder `assets/` berisi asset yang dipakai lintas halaman. Component tidak mengandung aturan bisnis prospek atau kunjungan; component hanya mengatur bentuk UI, interaksi umum, dan request helper.

## File Utama

| File | Isi |
|---|---|
| `css/components.css` | Token warna, layout, surface, button, field, tabs, badge, state, filter, metric, responsive data, pagination, dan modal konfirmasi. |
| `js/components.js` | `VisitinUI` namespace, API client, formatter, preference user, modal konfirmasi, filter panel, responsive data, tabs, state, debounce, dan toast wrapper. |
| `vendor/pdfjs/` | Library PDF.js untuk preview dokumen pipeline. |

## Aturan Komentar

Setiap component baru diberi komentar sesuai bahasa file agar developer berikutnya cepat menemukan tanggung jawabnya:

```html
<!-- # COMPONENT: Toolbar halaman -->
```

```css
/* COMPONENT: Primitive UI */
```

```javascript
// COMPONENT: HTTP client terpusat
```

```php
# COMPONENT: Variabel akses halaman
```

Gunakan prefix `ui-` untuk class reusable. Class yang spesifik pada satu page tetap diletakkan di page tersebut.

## Component CSS

### Surface dan layout

```html
<!-- # COMPONENT: Surface dan section reusable -->
<section class="ui-surface ui-section">
    <h2 class="ui-section-title"><i class="fa-solid fa-user"></i> Profil</h2>
    <p class="ui-kicker">Keterangan singkat</p>
    <strong class="ui-value">Isi data</strong>
</section>
```

### Button dan icon button

```html
<!-- # COMPONENT: Button aksi -->
<button type="button" class="ui-button ui-button--primary">
    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
    Simpan
</button>

<!-- # COMPONENT: Icon button -->
<button type="button" class="ui-icon-button" aria-label="Segarkan data" title="Segarkan data">
    <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
</button>
```

### Field form

```html
<!-- # COMPONENT: Field form -->
<div class="ui-field">
    <label class="ui-field-label" for="customer-name">Nama Nasabah</label>
    <input class="ui-input" id="customer-name" type="text" placeholder="Nama lengkap">
    <small class="ui-field-help">Nama akan tampil di daftar prospek.</small>
</div>
```

### Tabs

```html
<!-- # COMPONENT: Tabs generic; components.js mengatur aria-selected dan panel -->
<div data-ui-tabs>
    <div class="ui-tabs" role="tablist">
        <button type="button" class="ui-tab" data-ui-tab="list" aria-selected="true">Daftar</button>
        <button type="button" class="ui-tab" data-ui-tab="report" aria-selected="false">Report</button>
    </div>
    <section class="ui-tab-panel" data-ui-tab-panel="list">Konten daftar</section>
    <section class="ui-tab-panel" data-ui-tab-panel="report" hidden>Konten report</section>
</div>
```

### Badge dan state

```html
<!-- # COMPONENT: Badge status -->
<span class="ui-badge ui-badge--success">Aktif</span>
<span class="ui-badge ui-badge--warning">Menunggu</span>

<!-- # COMPONENT: State slot -->
<div id="data-slot">
    <div data-ui-state="loading" class="ui-loading-state" hidden>
        <span class="ui-loading-state-text">Memuat data...</span>
    </div>
    <div data-ui-state="empty" class="ui-empty-state" hidden>
        <i class="ui-empty-state-icon fa-solid fa-inbox"></i>
        <h3 class="ui-empty-state-title">Belum ada data</h3>
        <p class="ui-empty-state-text">Data akan tampil setelah tersedia.</p>
    </div>
    <div data-ui-state="content">Konten utama</div>
</div>
```

```javascript
// COMPONENT: Mengubah state slot dari page.
VisitinUI.setState(document.getElementById('data-slot'), 'loading');
```

## Component JavaScript

### API client

```javascript
// COMPONENT: GET request dengan response standar aplikasi.
const response = await VisitinUI.api.get('prospect_list', { page: 1, limit: 20 });
const items = response.data?.items || [];

// COMPONENT: POST JSON ke endpoint aplikasi.
await VisitinUI.api.post('prospect_follow_up', { prospect_id: 10, note: 'Dihubungi' });
```

### Formatter dan debounce

```javascript
// COMPONENT: Formatter lintas page.
VisitinUI.format.currency(1500000);
VisitinUI.format.date('2026-09-07 10:00:00');

// COMPONENT: Search dengan jeda request.
const search = VisitinUI.debounce(loadData, 350);
```

### Component yang sudah tersedia

- `VisitinUI.preferences`: menyimpan font dan ukuran teks per user.
- `VisitinUI.confirm`: modal konfirmasi reusable.
- `VisitinUI.bindFilterPanel`: panel filter buka/tutup.
- `VisitinUI.bindResponsiveDataView`: slot card mobile dan tabel desktop.
- `VisitinUI.bindTabs`: tabs dan panel konten generic.
- `VisitinUI.setState`: state loading, empty, error, atau content.
- `VisitinUI.api`: GET/POST API client dengan credentials dan parsing response.
- `VisitinUI.format`: currency, number, date, dan text.
- `VisitinUI.debounce`: pembatas request untuk input pencarian.
- `VisitinUI.notify`: wrapper toast global.

## Urutan Refactor Page

1. Refactor page baru menggunakan `ui-*` dan `VisitinUI` sejak awal.
2. Refactor page prospek yang paling sering dipakai: input, detail, daftar, dan pipeline.
3. Refactor mapping, nominatif, history, dan kunjungan setelah kontrak API existing selesai.
4. Hapus CSS/JS inline yang sudah tidak memiliki consumer.

Component asset ini sengaja dibuat kompatibel dengan Bootstrap dan Font Awesome yang sudah dipakai aplikasi, sehingga refactor dapat dilakukan bertahap tanpa mengganti seluruh UI sekaligus.
