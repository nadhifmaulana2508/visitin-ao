<?php
$is_ao = in_array($user_role, ['ao_kredit', 'ao_dana', 'ao_remedial', 'developer']);
$is_ao_kredit = in_array('AO_KREDIT', $user_permissions) || $user_role === 'developer';
$is_ao_dana = in_array('AO_DANA', $user_permissions) || $user_role === 'developer';
$is_ao_remedial = (in_array('AO_REMEDIAL_FE', $user_permissions) || in_array('AO_REMEDIAL_BE', $user_permissions)) || $user_role === 'developer';
$is_pusat = ($user_kode_kantor === '000');
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .form-container { margin: -30px 16px 20px 16px; position: relative; z-index: 10; }
    @media (min-width: 768px) { .form-container { margin: -35px 24px 24px 24px; } }
    @media (min-width: 1024px) { .form-container { margin: -40px 32px 28px 32px; } }

    .form-card {
        background: #ffffff; border-radius: 16px; margin-bottom: 16px;
        padding: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #F1F5F9;
    }
    @media (min-width: 768px) { .form-card { padding: 22px; } }

    /* Chip jenis prospek */
    .chip-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .chip-radio { display: none; }
    .chip-label {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 9px 14px; border-radius: 20px; font-size: 0.72rem; font-weight: 700;
        background: #F1F5F9; color: #475569; border: 2px solid transparent;
        cursor: pointer; transition: all 0.2s; user-select: none;
    }
    .chip-label:active { transform: scale(0.95); }
    .chip-radio:checked + .chip-label {
        background: rgba(10,25,49,0.07); color: var(--color-primary); border-color: var(--color-primary);
    }
    @media (min-width: 768px) { .chip-label { padding: 10px 16px; font-size: 0.78rem; } }

    /* Auto delegasi badge */
    .badge-auto-delegasi {
        background: #E8F5E9; color: #388E3C; font-size: 0.65rem;
        padding: 5px 12px; border-radius: 8px; font-weight: 700;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .info-box {
        background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px;
        padding: 10px 14px; font-size: 0.75rem; color: #92400E;
        display: flex; align-items: flex-start; gap: 8px;
    }

    /* Foto section */
    .foto-preview {
        width: 100%; max-height: 200px; object-fit: cover;
        border-radius: 12px; display: none; margin-top: 10px;
        border: 2px solid #E2E8F0;
    }
    .foto-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-foto {
        flex: 1; min-width: 100px; padding: 10px 12px; border-radius: 10px;
        font-weight: 700; font-size: 0.78rem; border: 1px solid #CBD5E1;
        background: #F8FAFC; color: #1E293B; cursor: pointer; transition: 0.2s;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .btn-foto:active { transform: scale(0.96); }
    .btn-foto-camera { background: #1565C0; color: white; border-color: #1565C0; }

    /* Map */
    #map-container { width: 100%; height: 200px; border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0; }
    @media (min-width: 768px) { #map-container { height: 260px; } }

    .btn-submit-prospek {
        background: var(--color-accent); color: white; border: none;
        border-radius: 12px; font-weight: 800; font-size: 0.95rem;
        padding: 15px; width: 100%; box-shadow: 0 8px 20px rgba(255,123,84,0.3); transition: 0.2s;
    }
    .btn-submit-prospek:active { transform: scale(0.98); }
    .btn-submit-prospek:disabled { opacity: 0.6; }
</style>


<div class="header-compact">
    <div class="d-flex align-items-center mb-1">
        <a href="<?= BASE_APP ?>/daftar-prospek" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <div>
            <h5 class="fw-bold mb-0">Input Prospek Baru</h5>
            <p class="small text-white-50 mb-0" style="font-size:0.7rem;">Lengkapi data calon nasabah</p>
        </div>
    </div>
</div>

<div class="form-container">
<form id="form-prospek">

    <!-- JENIS PROSPEK -->
    <div class="form-card">
        <h6 class="section-title"><i class="fa-solid fa-tag me-2 text-accent"></i>Jenis Prospek</h6>
        <div class="chip-group">
            <input type="radio" name="prospect_type" value="KREDIT" id="t_kredit" class="chip-radio" required>
            <label for="t_kredit" class="chip-label"><i class="fa-solid fa-money-bill-transfer"></i> Kredit</label>
            <input type="radio" name="prospect_type" value="TABUNGAN" id="t_tabungan" class="chip-radio">
            <label for="t_tabungan" class="chip-label"><i class="fa-solid fa-piggy-bank"></i> Tabungan</label>
            <input type="radio" name="prospect_type" value="DEPOSITO" id="t_deposito" class="chip-radio">
            <label for="t_deposito" class="chip-label"><i class="fa-solid fa-vault"></i> Deposito</label>
            <input type="radio" name="prospect_type" value="PEMBELI_ASET" id="t_aset" class="chip-radio">
            <label for="t_aset" class="chip-label"><i class="fa-solid fa-building"></i> Pembeli Aset</label>
            <input type="radio" name="prospect_type" value="DEBITUR_EXISTING" id="t_exist" class="chip-radio">
            <label for="t_exist" class="chip-label"><i class="fa-solid fa-user-check"></i> Debitur Existing</label>
        </div>
        <?php if ($is_ao): ?>
        <div class="mt-3" id="auto-delegasi-info" style="display:none;">
            <span class="badge-auto-delegasi"><i class="fa-solid fa-bolt"></i> Auto-delegasi: langsung masuk pipeline Anda</span>
        </div>
        <?php else: ?>
        <div class="info-box mt-3">
            <i class="fa-solid fa-circle-info mt-1"></i>
            <span>Prospek Anda akan masuk daftar tunggu delegasi. Superuser akan mendelegasikan ke AO yang tepat.</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- CABANG TUJUAN -->
    <div class="form-card">
        <h6 class="section-title"><i class="fa-solid fa-building-columns me-2 text-accent"></i>Cabang Tujuan</h6>
        <div class="row g-3">
            <div class="col-sm-6">
                <label class="form-label-custom">Korwil</label>
                <select class="input-custom" id="sel-korwil">
                    <option value="all">Semua Korwil</option>
                    <option value="semarang">Semarang (001-007)</option>
                    <option value="solo">Solo (008-014)</option>
                    <option value="banyumas">Banyumas (015-021)</option>
                    <option value="pekalongan">Pekalongan (022-028)</option>
                </select>
            </div>
            <div class="col-sm-6">
                <label class="form-label-custom">Cabang <span class="text-danger">*</span></label>
                <select class="input-custom" name="kode_kantor" id="sel-cabang" required>
                    <option value="">-- Pilih Cabang --</option>
                </select>
            </div>
        </div>
        <?php if (!$is_pusat): ?>
        <small class="text-muted d-block mt-2" style="font-size:0.65rem;"><i class="fa-solid fa-info-circle me-1"></i>Default: cabang Anda (<?= $user_kode_kantor ?>). Bisa diubah jika prospek di cabang lain.</small>
        <?php else: ?>
        <small class="text-muted d-block mt-2" style="font-size:0.65rem;"><i class="fa-solid fa-info-circle me-1"></i>Anda dari Pusat. Pilih cabang tujuan prospek (misal kerabat di cabang tertentu).</small>
        <?php endif; ?>
    </div>

    <!-- DATA NASABAH -->
    <div class="form-card">
        <h6 class="section-title"><i class="fa-solid fa-user-plus me-2 text-accent"></i>Data Calon Nasabah</h6>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label-custom">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="input-custom" name="customer_name" placeholder="Nama calon nasabah" required>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label-custom">No. Identitas (KTP)</label>
                <input type="text" class="input-custom" name="identity_number" placeholder="16 digit" maxlength="16">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label-custom">No. HP / WA <span class="text-danger">*</span></label>
                <input type="tel" class="input-custom" name="phone_number" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label-custom">Produk yang Diminati</label>
                <input type="text" class="input-custom" name="product_interest" placeholder="KMK, Deposito 12bln, dll">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label-custom">Nominal Estimasi (Rp)</label>
                <input type="number" class="input-custom" name="estimated_amount" placeholder="0" min="0">
            </div>
        </div>
    </div>


    <!-- ALAMAT -->
    <div class="form-card">
        <h6 class="section-title"><i class="fa-solid fa-map-location-dot me-2 text-accent"></i>Alamat</h6>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <label class="form-label-custom">Provinsi <span class="text-danger">*</span></label>
                <select class="input-custom" name="provinsi" id="sel-provinsi" required>
                    <option value="">-- Pilih --</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label-custom">Kab / Kota <span class="text-danger">*</span></label>
                <select class="input-custom" name="kab_kota" id="sel-kabkota" disabled>
                    <option value="">-- Pilih Provinsi --</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label-custom">Kecamatan <span class="text-danger">*</span></label>
                <select class="input-custom" name="kecamatan" id="sel-kecamatan" disabled>
                    <option value="">-- Pilih Kab --</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label-custom">Desa / Kelurahan <span class="text-danger">*</span></label>
                <select class="input-custom" name="desa" id="sel-desa" disabled>
                    <option value="">-- Pilih Kec --</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Alamat Lengkap</label>
                <textarea class="input-custom" name="address" rows="2" placeholder="Jalan, RT/RW, No. Rumah..."></textarea>
            </div>
        </div>
    </div>

    <!-- FOTO & GEOTAGGING -->
    <div class="form-card">
        <h6 class="section-title"><i class="fa-solid fa-camera-retro me-2 text-accent"></i>Foto & Lokasi</h6>

        <!-- Foto -->
        <label class="form-label-custom mb-2">Foto Prospek (opsional)</label>
        <div class="foto-actions mb-2">
            <button type="button" class="btn-foto" onclick="document.getElementById('input-file-foto').click()">
                <i class="fa-solid fa-upload"></i> Upload Foto
            </button>
            <button type="button" class="btn-foto btn-foto-camera" id="btn-open-camera">
                <i class="fa-solid fa-camera"></i> Ambil Foto
            </button>
        </div>
        <input type="file" id="input-file-foto" class="d-none" accept="image/*" onchange="previewUpload(this)">
        <input type="hidden" name="foto_base64" id="foto-base64">
        <img id="foto-preview" class="foto-preview" alt="Preview">
        <small class="text-muted d-block mt-1" style="font-size:0.6rem;" id="foto-status">Belum ada foto</small>

        <!-- Geotagging -->
        <label class="form-label-custom mt-3 mb-2">Lokasi GPS (Geotagging)</label>
        <div class="row g-2 mb-2">
            <div class="col">
                <div class="d-flex gap-2">
                    <input type="text" class="input-custom flex-grow-1" id="geo-alamat" placeholder="Klik Pin untuk ambil lokasi..." readonly style="font-size:0.8rem;">
                    <button type="button" class="btn-foto btn-foto-camera" id="btn-get-loc" style="min-width:50px; flex:none;">
                        <i class="fa-solid fa-location-crosshairs"></i>
                    </button>
                </div>
            </div>
        </div>
        <input type="hidden" name="latitude" id="input-lat">
        <input type="hidden" name="longitude" id="input-lng">
        <input type="hidden" name="geo_address" id="input-geo-addr">
        <div id="map-container" style="display:none;"></div>
    </div>

    <!-- KETERANGAN -->
    <div class="form-card">
        <h6 class="section-title"><i class="fa-solid fa-note-sticky me-2 text-accent"></i>Keterangan</h6>
        <textarea class="input-custom" name="description" rows="3" placeholder="Informasi tambahan tentang calon nasabah..."></textarea>
    </div>

    <!-- SUBMIT -->
    <button type="submit" class="btn-submit-prospek" id="btn-submit">
        <i class="fa-solid fa-paper-plane me-2"></i> Simpan Prospek
    </button>

</form>
</div>

<!-- Modal Kamera -->
<div class="modal fade" id="modalKamera" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="margin:15px;">
        <div class="modal-content" style="border-radius:16px;">
            <div class="modal-header border-0 pb-1">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-camera text-primary me-2"></i>Ambil Foto</h6>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 me-1" id="btn-switch-cam">Flip</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
            <div class="modal-body text-center pt-0">
                <video id="cam-video" autoplay playsinline style="width:100%;border-radius:12px;background:#000;max-height:350px;object-fit:cover;"></video>
                <canvas id="cam-canvas" class="d-none"></canvas>
                <button type="button" class="btn btn-success fw-bold mt-3 px-4 py-2" id="btn-capture" style="border-radius:10px;">
                    <i class="fa-solid fa-camera me-2"></i> Capture
                </button>
            </div>
        </div>
    </div>
</div>


<script>
(function() {
    const BASE_APP = <?= json_encode(BASE_APP) ?>;
    const isAO = <?= $is_ao ? 'true' : 'false' ?>;
    const isAOKredit = <?= $is_ao_kredit ? 'true' : 'false' ?>;
    const isAODana = <?= $is_ao_dana ? 'true' : 'false' ?>;
    const isAORemedial = <?= $is_ao_remedial ? 'true' : 'false' ?>;
    const userKodeKantor = '<?= $user_kode_kantor ?>';

    // =========================================
    // CABANG DROPDOWN (dari API master_kode_kantor)
    // =========================================
    const selKorwil = document.getElementById('sel-korwil');
    const selCabang = document.getElementById('sel-cabang');
    let allCabang = [];

    async function loadCabang() {
        try {
            const res = await fetch(BASE_APP + '/api/?action=master_kode_kantor', { credentials: 'include' });
            const body = await res.json();
            if (body.status === 200 && body.data && body.data.all) {
                allCabang = body.data.all;
            }
        } catch(e) {
            // Fallback hardcoded
            allCabang = [
                {kode_kantor:'000',nama_kantor:'Pusat',korwil:'pusat'},
                {kode_kantor:'001',nama_kantor:'Kc. Utama',korwil:'semarang'},
                {kode_kantor:'002',nama_kantor:'Kc. Rembang',korwil:'semarang'},
                {kode_kantor:'003',nama_kantor:'Kc. Pati',korwil:'semarang'},
                {kode_kantor:'004',nama_kantor:'Kc. Demak',korwil:'semarang'},
                {kode_kantor:'005',nama_kantor:'Kc. Kendal',korwil:'semarang'},
                {kode_kantor:'006',nama_kantor:'Kc. Salatiga',korwil:'semarang'},
                {kode_kantor:'007',nama_kantor:'Kc. Kab. Semarang',korwil:'semarang'},
                {kode_kantor:'008',nama_kantor:'Kc. Wonogiri',korwil:'solo'},
                {kode_kantor:'009',nama_kantor:'Kc. Kota Surakarta',korwil:'solo'},
                {kode_kantor:'010',nama_kantor:'Kc. Karanganyar',korwil:'solo'},
                {kode_kantor:'011',nama_kantor:'Kc. Sukoharjo',korwil:'solo'},
                {kode_kantor:'012',nama_kantor:'Kc. Sragen',korwil:'solo'},
                {kode_kantor:'013',nama_kantor:'Kc. Boyolali',korwil:'solo'},
                {kode_kantor:'014',nama_kantor:'Kc. Magelang',korwil:'solo'},
                {kode_kantor:'015',nama_kantor:'Kc. Wonosobo',korwil:'banyumas'},
                {kode_kantor:'016',nama_kantor:'Kc. Purworejo',korwil:'banyumas'},
                {kode_kantor:'017',nama_kantor:'Kc. Kebumen',korwil:'banyumas'},
                {kode_kantor:'018',nama_kantor:'Kc. Banjarnegara',korwil:'banyumas'},
                {kode_kantor:'019',nama_kantor:'Kc. Purbalingga',korwil:'banyumas'},
                {kode_kantor:'020',nama_kantor:'Kc. Banyumas',korwil:'banyumas'},
                {kode_kantor:'021',nama_kantor:'Kc. Cilacap',korwil:'banyumas'},
                {kode_kantor:'022',nama_kantor:'Kc. Kab. Tegal',korwil:'pekalongan'},
                {kode_kantor:'023',nama_kantor:'Kc. Brebes',korwil:'pekalongan'},
                {kode_kantor:'024',nama_kantor:'Kc. Kota Tegal',korwil:'pekalongan'},
                {kode_kantor:'025',nama_kantor:'Kc. Pemalang',korwil:'pekalongan'},
                {kode_kantor:'026',nama_kantor:'Kc. Kota Pekalongan',korwil:'pekalongan'},
                {kode_kantor:'027',nama_kantor:'Kc. Kab. Pekalongan',korwil:'pekalongan'},
                {kode_kantor:'028',nama_kantor:'Kc. Batang',korwil:'pekalongan'},
            ];
        }
        renderCabang('all');
    }

    function renderCabang(korwil) {
        const filtered = korwil === 'all' ? allCabang.filter(c => c.kode_kantor !== '000') : allCabang.filter(c => c.korwil === korwil);
        selCabang.innerHTML = '<option value="">-- Pilih Cabang --</option>';
        filtered.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.kode_kantor;
            opt.textContent = c.kode_kantor + ' - ' + c.nama_kantor;
            if (c.kode_kantor === userKodeKantor && userKodeKantor !== '000') opt.selected = true;
            selCabang.appendChild(opt);
        });
    }

    selKorwil.addEventListener('change', () => renderCabang(selKorwil.value));
    loadCabang();

    // =========================================
    // AUTO-DELEGASI INFO
    // =========================================
    document.querySelectorAll('.chip-radio').forEach(r => {
        r.addEventListener('change', function() {
            if (!isAO) return;
            const el = document.getElementById('auto-delegasi-info');
            if (!el) return;
            const val = this.value;
            let show = (val==='KREDIT'&&isAOKredit)||(val==='TABUNGAN'&&isAODana)||(val==='DEPOSITO'&&isAODana)||(val==='PEMBELI_ASET'&&isAORemedial)||(val==='DEBITUR_EXISTING'&&isAOKredit);
            el.style.display = show ? 'block' : 'none';
        });
    });

    // =========================================
    // DROPDOWN WILAYAH (emsifa API)
    // =========================================
    const API_W = 'https://www.emsifa.com/api-wilayah-indonesia/api';
    const selProv = document.getElementById('sel-provinsi');
    const selKab = document.getElementById('sel-kabkota');
    const selKec = document.getElementById('sel-kecamatan');
    const selDesa = document.getElementById('sel-desa');

    async function loadProv() {
        try {
            const r = await fetch(API_W + '/provinces.json'); const d = await r.json();
            d.forEach(p => { const o=document.createElement('option'); o.value=p.id; o.textContent=p.name; o.dataset.name=p.name; selProv.appendChild(o); });
        } catch(e) {
            ['JAWA TENGAH','JAWA BARAT','DI YOGYAKARTA'].forEach((n,i) => { const o=document.createElement('option'); o.value=30+i; o.textContent=n; o.dataset.name=n; selProv.appendChild(o); });
        }
    }
    selProv.addEventListener('change', async function() {
        selKab.innerHTML='<option value="">Memuat...</option>'; selKab.disabled=true;
        selKec.innerHTML='<option value="">--</option>'; selKec.disabled=true;
        selDesa.innerHTML='<option value="">--</option>'; selDesa.disabled=true;
        if(!this.value) return;
        try { const r=await fetch(API_W+'/regencies/'+this.value+'.json'); const d=await r.json(); selKab.innerHTML='<option value="">-- Pilih --</option>'; d.forEach(k=>{const o=document.createElement('option');o.value=k.id;o.textContent=k.name;o.dataset.name=k.name;selKab.appendChild(o);}); selKab.disabled=false; } catch(e){ selKab.innerHTML='<option value="">Gagal</option>'; }
    });
    selKab.addEventListener('change', async function() {
        selKec.innerHTML='<option value="">Memuat...</option>'; selKec.disabled=true;
        selDesa.innerHTML='<option value="">--</option>'; selDesa.disabled=true;
        if(!this.value) return;
        try { const r=await fetch(API_W+'/districts/'+this.value+'.json'); const d=await r.json(); selKec.innerHTML='<option value="">-- Pilih --</option>'; d.forEach(k=>{const o=document.createElement('option');o.value=k.id;o.textContent=k.name;o.dataset.name=k.name;selKec.appendChild(o);}); selKec.disabled=false; } catch(e){ selKec.innerHTML='<option value="">Gagal</option>'; }
    });
    selKec.addEventListener('change', async function() {
        selDesa.innerHTML='<option value="">Memuat...</option>'; selDesa.disabled=true;
        if(!this.value) return;
        try { const r=await fetch(API_W+'/villages/'+this.value+'.json'); const d=await r.json(); selDesa.innerHTML='<option value="">-- Pilih --</option>'; d.forEach(k=>{const o=document.createElement('option');o.value=k.id;o.textContent=k.name;o.dataset.name=k.name;selDesa.appendChild(o);}); selDesa.disabled=false; } catch(e){ selDesa.innerHTML='<option value="">Gagal</option>'; }
    });
    loadProv();


    // =========================================
    // FOTO: Upload & Kamera
    // =========================================
    const fotoPreview = document.getElementById('foto-preview');
    const fotoBase64 = document.getElementById('foto-base64');
    const fotoStatus = document.getElementById('foto-status');

    window.previewUpload = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fotoPreview.src = e.target.result;
                fotoPreview.style.display = 'block';
                fotoBase64.value = e.target.result;
                fotoStatus.textContent = input.files[0].name;
                fotoStatus.style.color = '#388E3C';
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    // Kamera in-app
    let camStream = null;
    let facingMode = 'environment';
    const video = document.getElementById('cam-video');
    const canvas = document.getElementById('cam-canvas');
    const modalKamera = document.getElementById('modalKamera');

    document.getElementById('btn-open-camera').addEventListener('click', () => {
        new bootstrap.Modal(modalKamera).show();
    });

    modalKamera.addEventListener('show.bs.modal', () => startCam());
    modalKamera.addEventListener('hidden.bs.modal', () => stopCam());

    document.getElementById('btn-switch-cam').addEventListener('click', () => {
        facingMode = facingMode === 'environment' ? 'user' : 'environment';
        startCam();
    });

    document.getElementById('btn-capture').addEventListener('click', () => {
        const ctx = canvas.getContext('2d');
        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);
        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
        fotoBase64.value = dataUrl;
        fotoPreview.src = dataUrl;
        fotoPreview.style.display = 'block';
        fotoStatus.textContent = 'Foto dari kamera (' + new Date().toLocaleTimeString() + ')';
        fotoStatus.style.color = '#388E3C';
        bootstrap.Modal.getInstance(modalKamera).hide();
    });

    function startCam() {
        stopCam();
        navigator.mediaDevices.getUserMedia({ video: { facingMode } })
            .then(stream => { camStream = stream; video.srcObject = stream; })
            .catch(() => { fotoStatus.textContent = 'Kamera tidak tersedia'; fotoStatus.style.color='#D32F2F'; });
    }
    function stopCam() { if (camStream) { camStream.getTracks().forEach(t => t.stop()); camStream = null; } }

    // =========================================
    // GEOTAGGING: Leaflet Map
    // =========================================
    let map = null, marker = null;

    document.getElementById('btn-get-loc').addEventListener('click', getLocation);

    function getLocation() {
        const btn = document.getElementById('btn-get-loc');
        const alamatEl = document.getElementById('geo-alamat');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        alamatEl.value = 'Mencari lokasi...';

        if (!navigator.geolocation) {
            alamatEl.value = 'GPS tidak didukung browser';
            btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                document.getElementById('input-lat').value = lat;
                document.getElementById('input-lng').value = lng;

                // Show map
                const mapDiv = document.getElementById('map-container');
                mapDiv.style.display = 'block';
                if (!map) {
                    map = L.map('map-container').setView([lat, lng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19, attribution: '&copy; OpenStreetMap'
                    }).addTo(map);
                } else {
                    map.setView([lat, lng], 16);
                }
                if (marker) map.removeLayer(marker);
                marker = L.marker([lat, lng]).addTo(map).bindPopup('Lokasi Anda').openPopup();

                // Reverse geocoding
                fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, {
                    headers: { 'Accept-Language': 'id' }
                }).then(r => r.json()).then(data => {
                    if (data && data.display_name) {
                        alamatEl.value = data.display_name;
                        document.getElementById('input-geo-addr').value = data.display_name;
                    } else {
                        alamatEl.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    }
                    btn.innerHTML = '<i class="fa-solid fa-check text-white"></i>';
                    btn.style.background = '#388E3C'; btn.style.borderColor = '#388E3C';
                }).catch(() => {
                    alamatEl.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
                });

                // Map click to reposition
                map.on('click', (e) => {
                    const newLat = e.latlng.lat, newLng = e.latlng.lng;
                    document.getElementById('input-lat').value = newLat;
                    document.getElementById('input-lng').value = newLng;
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([newLat, newLng]).addTo(map);
                    alamatEl.value = `${newLat.toFixed(6)}, ${newLng.toFixed(6)} (manual pin)`;
                });
            },
            (err) => {
                alamatEl.value = 'Gagal. Aktifkan GPS.';
                btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
            },
            { enableHighAccuracy: true, timeout: 15000 }
        );
    }

    // =========================================
    // FORM SUBMIT
    // =========================================
    document.getElementById('form-prospek').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        const typeEl = this.querySelector('input[name="prospect_type"]:checked');
        if (!typeEl) { showToast('<i class="fa-solid fa-triangle-exclamation me-2"></i>Pilih jenis prospek', 'warning'); return; }

        btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menyimpan...';
        const fd = new FormData(this);
        const payload = {
            prospect_type: fd.get('prospect_type'),
            customer_name: fd.get('customer_name'),
            identity_number: fd.get('identity_number') || null,
            phone_number: fd.get('phone_number'),
            product_interest: fd.get('product_interest') || null,
            estimated_amount: parseInt(fd.get('estimated_amount') || '0'),
            kode_kantor: fd.get('kode_kantor'),
            provinsi: selProv.options[selProv.selectedIndex]?.dataset?.name || '',
            kab_kota: selKab.options[selKab.selectedIndex]?.dataset?.name || '',
            kecamatan: selKec.options[selKec.selectedIndex]?.dataset?.name || '',
            desa: selDesa.options[selDesa.selectedIndex]?.dataset?.name || '',
            address: fd.get('address') || '',
            latitude: document.getElementById('input-lat').value || null,
            longitude: document.getElementById('input-lng').value || null,
            geo_address: document.getElementById('input-geo-addr').value || null,
            foto_url: null,
            description: fd.get('description') || '',
        };

        // Upload foto dulu jika ada
        const fotoData = fotoBase64.value;
        if (fotoData) {
            try {
                const fRes = await fetch(BASE_APP + '/api/?action=upload_foto', {
                    method:'POST', credentials:'include',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({ foto_base64: fotoData, prefix: 'prospek' })
                });
                const fBody = await fRes.json();
                if (fBody.status === 200 && fBody.data) payload.foto_url = fBody.data.path;
            } catch(e) { /* skip foto error */ }
        }

        try {
            const res = await fetch(BASE_APP + '/api/?action=prospect_create', {
                method:'POST', credentials:'include',
                headers:{'Content-Type':'application/json'},
                body: JSON.stringify(payload)
            });
            const body = await res.json();
            if (res.ok && (body.status === 200 || body.status === 201)) {
                showToast('<i class="fa-solid fa-check-circle me-2"></i>Prospek berhasil disimpan!', 'success');
                setTimeout(() => { window.location.href = BASE_APP + '/daftar-prospek'; }, 800);
            } else {
                showToast('<i class="fa-solid fa-xmark me-2"></i>' + (body.message || 'Gagal'), 'danger');
            }
        } catch(err) {
            showToast('<i class="fa-solid fa-wifi me-2"></i>Tidak bisa terhubung ke server', 'danger');
        } finally {
            btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Simpan Prospek';
        }
    });
})();
</script>
