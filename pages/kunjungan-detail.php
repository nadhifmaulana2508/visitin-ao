<style>
    /* COMPACT HEADER */
    .header-compact {
        background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
        color: white; padding: 25px 20px 45px 20px; 
        border-bottom-left-radius: 25px; border-bottom-right-radius: 25px;
    }
    
    .detail-container { margin: -25px 15px 80px 15px; position: relative; z-index: 10; }

    /* FOTO BUKTI DENGAN WATERMARK STYLE */
    .photo-wrapper {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        background: #000;
        margin-bottom: 20px;
    }
    .photo-main { width: 100%; display: block; opacity: 0.9; }
    
    /* Overlay ala Kamera Dashboard AO */
    .photo-watermark {
        position: absolute; bottom: 0; left: 0; right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        color: white; padding: 15px; font-family: 'Courier New', Courier, monospace;
    }
    .watermark-text { font-size: 0.65rem; line-height: 1.4; text-shadow: 1px 1px 2px rgba(0,0,0,0.8); }

    /* INFO CARD */
    .info-card {
        background-color: #ffffff; border-radius: 16px; padding: 20px;
        margin-bottom: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .info-label { font-size: 0.65rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; margin-bottom: 2px; }
    .info-value { font-size: 0.9rem; font-weight: 700; color: #1E293B; margin-bottom: 12px; }
    
    .status-pill { padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; }
    
    .btn-maps {
        background-color: #E3F2FD; color: #1976D2; border: none; border-radius: 10px;
        padding: 12px; width: 100%; font-weight: 700; font-size: 0.85rem; transition: 0.2s;
    }
    .btn-maps:active { transform: scale(0.98); }
</style>

<?php
// Dummy Data Hasil Kunjungan
$visit = [
    'nama_debitur' => 'Bapak Supriyadi',
    'no_rekening'  => '1029384756',
    'kode'         => 'PTP',
    'keterangan'   => 'Debitur berjanji akan membayar tunggakan lewat Indomaret pada tanggal 15.',
    'nominal'      => '5.000.000',
    'tgl_janji'    => '2026-04-15',
    'waktu'        => '2026-04-08 09:30:12',
    'latitude'     => '-6.9932',
    'longitude'    => '110.4215',
    'alamat'       => 'Jl. Pemuda No.12, Pandansari, Kec. Semarang Tengah, Kota Semarang, Jawa Tengah 50139',
    'ao'           => 'Budi Santoso',
    'foto'         => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80' // Dummy foto rumah
];
?>

<div class="header-compact">
    <div class="d-flex align-items-center mb-1">
        <a href="javascript:history.back()" class="text-white me-3"><i class="fa-solid fa-arrow-left fs-5"></i></a>
        <h5 class="fw-bold mb-0">Detail Bukti Kunjungan</h5>
    </div>
</div>

<div class="detail-container">
    <div class="photo-wrapper">
        <img src="<?= $visit['foto'] ?>" class="photo-main" alt="Bukti Kunjungan">
        <div class="photo-watermark">
            <div class="watermark-text">
                <div><i class="fa-solid fa-user me-1"></i> AO: <?= $visit['ao'] ?></div>
                <div><i class="fa-solid fa-calendar-check me-1"></i> <?= $visit['waktu'] ?></div>
                <div><i class="fa-solid fa-location-crosshairs me-1"></i> <?= $visit['latitude'] ?>, <?= $visit['longitude'] ?></div>
                <div style="font-size: 0.55rem; opacity: 0.8; margin-top: 4px;">
                    <?= $visit['alamat'] ?>
                </div>
            </div>
        </div>
    </div>

    <div class="info-card">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="info-label">Nama Nasabah</div>
                <div class="info-value mb-0"><?= $visit['nama_debitur'] ?></div>
                <small class="text-muted">Rek: <?= $visit['no_rekening'] ?></small>
            </div>
            <span class="status-pill bg-warning text-dark">CODE: <?= $visit['kode'] ?></span>
        </div>

        <div class="row border-top pt-3">
            <div class="col-6">
                <div class="info-label">Janji Bayar (PTP)</div>
                <div class="info-value text-success">Rp <?= $visit['nominal'] ?></div>
            </div>
            <div class="col-6">
                <div class="info-label">Tgl Janji Bayar</div>
                <div class="info-value"><?= date('d M Y', strtotime($visit['tgl_janji'])) ?></div>
            </div>
        </div>

        <div class="mt-2">
            <div class="info-label">Catatan AO</div>
            <div class="p-2 rounded-3 bg-light" style="font-size: 0.85rem; font-style: italic; color: #475569; border-left: 3px solid #CBD5E1;">
                "<?= $visit['keterangan'] ?>"
            </div>
        </div>
    </div>

    <div class="info-card">
        <h6 class="section-title mb-3" style="font-size: 0.75rem;"><i class="fa-solid fa-map-marked-alt me-2 text-primary"></i>Lokasi Presisi</h6>
        <p class="small text-muted mb-3" style="font-size: 0.75rem; line-height: 1.5;">
            <?= $visit['alamat'] ?>
        </p>
        
        <a href="https://www.google.com/maps?q=<?= $visit['latitude'] ?>,<?= $visit['longitude'] ?>" target="_blank" class="text-decoration-none">
            <button class="btn-maps shadow-sm">
                <i class="fa-solid fa-route me-2"></i> Buka di Google Maps
            </button>
        </a>
    </div>

    <button class="btn btn-outline-secondary w-100 py-3 fw-bold" onclick="window.print()" style="border-radius: 12px; border-style: dashed;">
        <i class="fa-solid fa-print me-2"></i> Cetak Laporan Detail
    </button>
</div>