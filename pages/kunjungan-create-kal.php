<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    /* COMPACT HEADER STYLING */
    .header-compact {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 25px 20px 45px 20px; 
        border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
    }
    
    .form-container { margin: -25px 15px 80px 15px; position: relative; z-index: 10; }

    /* FORM CARD SECTION */
    .form-card {
        background-color: #ffffff; border-radius: 16px; margin-bottom: 15px; 
        padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); border: 1px solid #F1F5F9;
    }
    .section-title { font-size: 0.85rem; font-weight: 800; color: var(--color-primary); text-transform: uppercase; margin-bottom: 0; }
    .section-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #F4F7F6; padding-bottom: 10px; margin-bottom: 15px; }

    /* CUSTOM INPUT STYLING */
    .form-label-custom { font-size: 0.7rem; font-weight: 700; color: #64748B; margin-bottom: 5px; display: block; }
    .input-custom { background-color: #ffffff; border: 1px solid #CBD5E1; border-radius: 8px; padding: 10px 15px; font-size: 0.85rem; font-weight: 600; color: #1E293B; width: 100%; }
    .input-custom:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(10, 25, 49, 0.1); outline: none; }
    .input-custom:disabled, .input-custom[readonly] { background-color: #F8FAFC; color: #64748B; cursor: not-allowed;}
    .input-group-text-custom { background-color: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 8px 0 0 8px; font-weight: 700; color: #475569; font-size: 0.85rem;}
    
    .btn-copy-va { background-color: #F1F5F9; border: 1px solid #CBD5E1; border-radius: 0 8px 8px 0; color: var(--color-primary); transition: 0.2s; }
    .btn-copy-va:hover, .btn-copy-va:active { background-color: var(--color-primary); color: white; border-color: var(--color-primary);}
    .btn-icon-action { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 1rem; transition: transform 0.2s; border: none; }
    .btn-icon-action:active { transform: scale(0.9); }
    .btn-icon-va { background-color: #E3F2FD; color: #1976D2; }
    .btn-icon-wa { background-color: #E8F5E9; color: #25D366; }

    /* POTENSI BISNIS */
    .card-topup { background: #FFF9E6; border: 1px solid #FFE0B2; }
    .title-topup { color: #E65100; border-bottom: 2px solid #FFE0B2; padding-bottom: 10px; margin-bottom: 15px;}
    .input-topup { border: 1px solid #FFCC80; }

    /* LOKASI & FOTO */
    .btn-pin { background-color: #10B981; color: white; border: none; border-radius: 8px; padding: 10px 15px; font-size: 1rem; transition: 0.2s; height: 100%; }
    .btn-pin:active { transform: scale(0.95); }
    
    .btn-action-foto { border-radius: 8px; font-weight: 700; font-size: 0.85rem; padding: 8px 15px; transition: 0.2s; }
    .btn-upload-foto { background-color: #F8FAFC; border: 1px solid #CBD5E1; color: #1E293B; }
    .btn-camera-foto { background-color: #2563EB; border: 1px solid #2563EB; color: white; }
    .btn-download-foto { background-color: #F8FAFC; border: 1px solid #CBD5E1; color: #1E293B; }

    /* MODAL KAMERA */
    #videoElement { width: 100%; border-radius: 12px; background-color: #000; max-height: 400px; object-fit: cover; }
    .btn-capture { background-color: #10B981; color: white; font-weight: bold; border-radius: 8px; padding: 10px; width: 100px; }

    .btn-submit { background-color: var(--color-accent); color: white; border: none; border-radius: 12px; font-weight: 800; font-size: 1rem; padding: 14px; width: 100%; box-shadow: 0 8px 15px rgba(255, 123, 84, 0.3); transition: 0.2s;}
</style>

<?php
// SIMULASI LOGIC BACKEND
$kolektibilitas = "L"; 
$tgl_realisasi = "2024-01-15"; 
$tenor_kredit = 36; 

$d1 = new DateTime($tgl_realisasi);
$d2 = new DateTime();
$diff = $d1->diff($d2);
$bulan_berjalan = ($diff->y * 12) + $diff->m; 

$is_potensi_topup = false;
if ($kolektibilitas == "L" && ($bulan_berjalan > 12 || $bulan_berjalan >= ($tenor_kredit / 2))) {
    $is_potensi_topup = true;
}

$nama_debitur = "Bapak Supriyadi";
$no_hp = "6281234567890"; 
$total_tunggakan = "Rp 5.000.000";
$va_permata = "85591029384756";
$va_mandiri = "89021029384756";
?>

<div class="header-compact">
    <div class="d-flex align-items-center mb-1">
        <a href="javascript:history.back()" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <h5 class="fw-bold mb-0">Create Kunjungan</h5>
    </div>
    <p class="small text-white-50 mb-0" style="font-size: 0.7rem;">Lengkapi data, ambil foto/unggah, dan sistem akan menempel waktu & lokasi.</p>
</div>

<div class="form-container">
    <form action="" method="POST" enctype="multipart/form-data">
        
        <div class="form-card">
            <div class="section-header">
                <h6 class="section-title"><i class="fa-solid fa-user-tag me-2 text-muted"></i>Data Debitur</h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-icon-action btn-icon-va shadow-sm" data-bs-toggle="modal" data-bs-target="#modalVA">
                        <i class="fa-solid fa-money-check-dollar"></i>
                    </button>
                    <button type="button" class="btn-icon-action btn-icon-wa shadow-sm" onclick="kirimWA()">
                        <i class="fa-brands fa-whatsapp" style="font-size: 1.2rem;"></i>
                    </button>
                </div>
            </div>
            
            <div class="row g-2 mb-2">
                <div class="col-6">
                    <label class="form-label-custom">No. Rekening</label>
                    <input type="text" class="input-custom" value="1029384756" readonly>
                </div>
                <div class="col-6">
                    <label class="form-label-custom">Nama Debitur</label>
                    <input type="text" class="input-custom" value="<?= $nama_debitur ?>" readonly>
                </div>
            </div>

            <div class="row g-2 mb-2">
                <div class="col-4">
                    <label class="form-label-custom">Kolek</label>
                    <input type="text" class="input-custom text-primary text-center px-1" value="<?= $kolektibilitas ?>" readonly>
                </div>
                <div class="col-4">
                    <label class="form-label-custom">Baki Debet</label>
                    <input type="text" class="input-custom px-1 text-center" value="45 Juta" readonly>
                </div>
                <div class="col-4">
                    <label class="form-label-custom">Menunggak</label>
                    <input type="text" class="input-custom text-center px-1" value="0 Hari" readonly>
                </div>
            </div>

            <div class="row g-2 mb-1">
                <div class="col-6">
                    <label class="form-label-custom">Tunggakan Pokok</label>
                    <input type="text" class="input-custom text-danger" value="<?= $total_tunggakan ?>" readonly>
                </div>
                <div class="col-6">
                    <label class="form-label-custom">Tunggakan Bunga</label>
                    <input type="text" class="input-custom text-danger" value="Rp 0" readonly>
                </div>
            </div>
        </div>

        <div class="form-card">
            <h6 class="section-title mb-3"><i class="fa-solid fa-person-walking-arrow-right me-2 text-accent"></i>Tindakan</h6>
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label-custom">Kode Tindakan</label>
                    <select class="input-custom" id="kode_tindakan" name="kode_tindakan" onchange="handleSmartForm()" required>
                        <option value="" selected>— Pilih —</option>
                        <option value="PTP">PTP - Promise to Pay</option>
                        <option value="PET">PET - Pick up Promise Taken</option>
                        <option value="PPK">PPK - Pick up Payment Collected</option>
                        <option value="LNS">LNS - Pelunasan</option>
                        <option value="RKS">RKS - Rumah Kosong</option>
                        <option value="SKP">SKP - Skip</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label-custom">Jenis Tindakan</label>
                    <select class="input-custom" id="jenis_tindakan" name="jenis_tindakan" required>
                        <option value="Kunjungan" selected>Kunjungan</option>
                        <option value="Telepon">Telepon</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label-custom">Lokasi Tindakan</label>
                    <select class="input-custom" id="lokasi_tindakan" name="lokasi_tindakan" required>
                        <option value="" selected>— Pilih —</option>
                        <option value="Rumah">Rumah Debitur</option>
                        <option value="Tempat Usaha">Tempat Usaha</option>
                        <option value="Kantor">Kantor Bank</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label-custom">Orang Ditemui</label>
                    <select class="input-custom" id="orang_ditemui" name="orang_ditemui" required>
                        <option value="" selected>— Pilih —</option>
                        <option value="Debitur">Debitur</option>
                        <option value="Ibu">Ibu</option>
                        <option value="Bapak">Bapak</option>
                        <option value="Pasangan">Pasangan</option>
                        <option value="Tetangga">Tetangga</option>
                        <option value="Tidak Ada">Tidak Ada</option> </select>
                </div>

                <div class="col-6">
                    <label class="form-label-custom">Nominal Janji Bayar</label>
                    <input type="number" class="input-custom" id="nominal_janji" name="nominal_janji" placeholder="0" disabled>
                </div>
                <div class="col-6">
                    <label class="form-label-custom">Tanggal Janji Bayar</label>
                    <input type="date" class="input-custom" id="tanggal_janji" name="tanggal_janji" disabled>
                </div>

                <div class="col-12">
                    <label class="form-label-custom">Keterangan</label>
                    <textarea class="input-custom" rows="2" placeholder="Catatan singkat" name="keterangan" required></textarea>
                </div>
            </div>
        </div>

        <?php if($is_potensi_topup): ?>
        <div class="form-card card-topup">
            <h6 class="section-title title-topup fw-bold"><i class="fa-solid fa-chart-line me-2"></i>POTENSI TOP-UP / PERPANJANGAN</h6>
            <div class="row g-3 mt-1">
                <div class="col-12">
                    <label class="form-label-custom text-dark fw-bold">Minat Top-Up?</label>
                    <select class="input-custom input-topup" id="minat_topup" name="minat_topup" onchange="toggleTopUp()">
                        <option value="Tidak">Tidak / Belum Minat</option>
                        <option value="Ya">Ya, Berminat</option>
                    </select>
                </div>
                <div class="col-12 topup-fields" style="display: none;">
                    <label class="form-label-custom fw-bold">Plafon Pengajuan (Rp)</label>
                    <input type="number" class="input-custom input-topup" name="plafon_pengajuan" placeholder="0">
                </div>
                <div class="col-12 topup-fields" style="display: none;">
                    <label class="form-label-custom fw-bold">Jenis Produk</label>
                    <select class="input-custom input-topup" name="jenis_produk">
                        <option value="Kredit Modal Kerja">Kredit Modal Kerja</option>
                        <option value="Kredit Multiguna">Kredit Multiguna</option>
                    </select>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-card">
            <h6 class="section-title mb-3"><i class="fa-solid fa-location-dot me-2 text-accent"></i>Lokasi & Foto</h6>
            
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label-custom">Alamat (otomatis dari koordinat)</label>
                    <div class="d-flex gap-2">
                        <input type="text" class="input-custom flex-grow-1 text-primary fw-bold" id="alamat_koordinat" placeholder="Klik tombol pin hijau..." readonly style="font-size: 0.8rem;">
                        <button type="button" class="btn-pin" onclick="getLokasiGPS()" id="btnPinGPS">
                            <i class="fa-solid fa-location-pin"></i>
                        </button>
                    </div>
                    <input type="hidden" name="latitude" id="lat_input">
                    <input type="hidden" name="longitude" id="lon_input">
                </div>

                <div class="col-12">
                    <label class="form-label-custom">Foto Kunjungan</label>
                    <input type="text" class="input-custom text-muted" id="teks_foto_kunjungan" value="Belum dipilih" readonly>
                    <input type="file" id="input_file_foto" class="d-none" accept="image/*" onchange="previewFileFoto(this)">
                    <input type="hidden" name="foto_base64" id="foto_base64">
                </div>

                <div class="col-12 text-center mt-1">
                    <button type="button" class="btn btn-action-foto btn-upload-foto" onclick="document.getElementById('input_file_foto').click()">
                        <i class="fa-solid fa-upload"></i> Upload
                    </button>
                    <button type="button" class="btn btn-action-foto btn-camera-foto mx-1" data-bs-toggle="modal" data-bs-target="#modalKameraInApp">
                        <i class="fa-solid fa-camera"></i> Kamera
                    </button>
                    <button type="button" class="btn btn-action-foto btn-download-foto">
                        <i class="fa-solid fa-download"></i> Unduh
                    </button>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label-custom">Waktu Buat</label>
                    <input type="text" class="input-custom" value="<?= date('Y-m-d H:i:s') ?>" readonly>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-submit mt-2">
            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Kunjungan
        </button>
    </form>
</div>

<div class="modal fade" id="modalKameraInApp" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="margin: 15px;">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header border-bottom-0 pb-2">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-camera text-primary me-2"></i> Kamera</h6>
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 me-1" id="btnSwitchCam">Switch</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
            <div class="modal-body text-center pt-0">
                <video id="videoElement" autoplay playsinline></video>
                <canvas id="canvasElement" class="d-none"></canvas>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-capture" id="btnCapture">
                        <i class="fa-solid fa-camera"></i> Capture
                    </button>
                    <span class="small text-muted" id="kamera_status">Kamera siap.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="margin: 20px;">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header pb-0">
                <h6 class="modal-title fw-bold text-dark"><i class="fa-solid fa-money-check-dollar me-2 text-primary"></i> Virtual Account</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2 pb-4">
                <p class="small text-muted mb-3">Nomor VA tagihan atas nama <b><?= $nama_debitur ?></b>:</p>
                <div class="mb-3">
                    <label class="form-label-custom">Bank Permata</label>
                    <div class="d-flex">
                        <input type="text" class="input-custom fw-bold text-primary bg-light flex-grow-1" id="va_permata" value="<?= $va_permata ?>" readonly style="border-radius: 8px 0 0 8px;">
                        <button class="btn-copy-va px-3" type="button" onclick="copyVA('va_permata')"><i class="fa-regular fa-copy"></i></button>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label-custom">Bank Mandiri</label>
                    <div class="d-flex">
                        <input type="text" class="input-custom fw-bold text-primary bg-light flex-grow-1" id="va_mandiri" value="<?= $va_mandiri ?>" readonly style="border-radius: 8px 0 0 8px;">
                        <button class="btn-copy-va px-3" type="button" onclick="copyVA('va_mandiri')"><i class="fa-regular fa-copy"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // --- SMART FORM LOGIC (BARU) ---
    function handleSmartForm() {
        const kode = document.getElementById("kode_tindakan").value;
        
        // 1. Logic Janji Bayar Aktif/Disable
        const nominal = document.getElementById("nominal_janji");
        const tanggal = document.getElementById("tanggal_janji");
        if (["PTP", "PET", "PPK", "LNS", "RES"].includes(kode)) {
            nominal.disabled = false; tanggal.disabled = false;
            nominal.required = true; tanggal.required = true;
            nominal.style.backgroundColor = "#ffffff"; tanggal.style.backgroundColor = "#ffffff";
        } else {
            nominal.disabled = true; tanggal.disabled = true;
            nominal.required = false; tanggal.required = false;
            nominal.value = ""; tanggal.value = "";
            nominal.style.backgroundColor = "#F8FAFC"; tanggal.style.backgroundColor = "#F8FAFC";
        }

        // 2. Logic "RKS" (Rumah Kosong) -> Otomatis Orang Ditemui = "Tidak Ada"
        const orangDitemui = document.getElementById("orang_ditemui");
        if (kode === "RKS") {
            orangDitemui.value = "Tidak Ada";
            orangDitemui.disabled = true; // Kunci inputan biar ga diganti
            orangDitemui.style.backgroundColor = "#F8FAFC";
        } else {
            orangDitemui.disabled = false; // Buka kuncian kalau pilih kode lain
            orangDitemui.style.backgroundColor = "#ffffff";
            // Kalau sebelumnya kekunci RKS, balikin ke kosong biar dia milih
            if(orangDitemui.value === "Tidak Ada") {
                orangDitemui.value = ""; 
            }
        }
    }

    // --- LOKASI (GEOLOCATION & OSM) ---
    function getLokasiGPS() {
        const btn = document.getElementById('btnPinGPS');
        const alamatInput = document.getElementById('alamat_koordinat');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        alamatInput.value = "Melacak lokasi...";
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    document.getElementById('lat_input').value = lat;
                    document.getElementById('lon_input').value = lon;
                    alamatInput.value = `📍 ${lat}, ${lon} (Mencari jalan...)`;
                    
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`, {
                        headers: { 'Accept-Language': 'id-ID,id;q=0.9', 'User-Agent': 'AplikasiAO/1.0' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data && data.display_name) { alamatInput.value = data.display_name; }
                        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                        btn.style.backgroundColor = "#2563EB"; 
                    })
                    .catch(err => {
                        alamatInput.value = `Koordinat: ${lat}, ${lon}`;
                        btn.innerHTML = '<i class="fa-solid fa-location-pin"></i>';
                    });
                },
                function(error) {
                    alamatInput.value = "Gagal melacak. Pastikan GPS aktif.";
                    btn.innerHTML = '<i class="fa-solid fa-location-pin"></i>';
                },
                { enableHighAccuracy: true, timeout: 15000 }
            );
        } else {
            alamatInput.value = "Browser tidak mendukung GPS.";
        }
    }

    // --- KAMERA IN-APP (WebRTC) ---
    let currentFacingMode = 'environment'; 
    let videoStream = null;

    function startCamera() {
        const video = document.getElementById('videoElement');
        const status = document.getElementById('kamera_status');
        if (videoStream) { videoStream.getTracks().forEach(track => track.stop()); }
        status.innerText = "Membuka kamera...";
        
        navigator.mediaDevices.getUserMedia({ video: { facingMode: currentFacingMode } })
        .then(stream => {
            videoStream = stream; video.srcObject = stream; status.innerText = "Kamera siap.";
        }).catch(err => {
            status.innerHTML = "<span class='text-danger'>Akses kamera gagal.</span>";
        });
    }

    const modalKamera = document.getElementById('modalKameraInApp');
    modalKamera.addEventListener('show.bs.modal', function () {
        startCamera();
        if(document.getElementById('alamat_koordinat').value === "") { getLokasiGPS(); }
    });
    modalKamera.addEventListener('hidden.bs.modal', function () {
        if (videoStream) { videoStream.getTracks().forEach(track => track.stop()); }
    });

    document.getElementById('btnSwitchCam').addEventListener('click', function() {
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        startCamera();
    });

    document.getElementById('btnCapture').addEventListener('click', function() {
        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('canvasElement');
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        document.getElementById('foto_base64').value = canvas.toDataURL('image/jpeg', 0.8);
        
        const teksFoto = document.getElementById('teks_foto_kunjungan');
        teksFoto.value = "Kunjungan_" + new Date().getTime() + ".jpg";
        teksFoto.classList.replace('text-muted', 'text-primary');
        teksFoto.style.fontWeight = 'bold';
        
        bootstrap.Modal.getInstance(modalKamera).hide();
    });

    function previewFileFoto(input) {
        if (input.files && input.files[0]) {
            const teksFoto = document.getElementById('teks_foto_kunjungan');
            teksFoto.value = input.files[0].name;
            teksFoto.classList.replace('text-muted', 'text-primary');
            teksFoto.style.fontWeight = 'bold';
            document.getElementById('foto_base64').value = "";
        }
    }

    // --- LAINNYA (WA, VA, Topup) ---
    function copyVA(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select(); copyText.setSelectionRange(0, 99999); 
        navigator.clipboard.writeText(copyText.value);
    }
    function kirimWA() {
        const hp = "<?= $no_hp ?>"; 
        const pesan = `Halo Bapak/Ibu <?= $nama_debitur ?>,\nMengingatkan tagihan fasilitas kredit Anda sebesar <?= $total_tunggakan ?> telah memasuki jatuh tempo.\nAbaikan pesan ini jika sudah membayar.`;
        window.open(`https://wa.me/${hp}?text=${encodeURIComponent(pesan)}`, '_blank');
    }
    function toggleTopUp() {
        const minat = document.getElementById("minat_topup").value;
        document.querySelectorAll(".topup-fields").forEach(f => f.style.display = (minat === "Ya" ? "block" : "none"));
    }
</script>