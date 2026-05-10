<style>
    .debitur-header { background: var(--color-primary); color: white; padding: 30px 20px; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; }
    .timeline { position: relative; padding-left: 20px; list-style: none; }
    .timeline::before { content: ''; position: absolute; left: 0; top: 5px; bottom: 0; width: 2px; background: #E2E8F0; }
    .timeline-item { position: relative; margin-bottom: 25px; }
    .timeline-dot { position: absolute; left: -24px; top: 5px; width: 10px; height: 10px; border-radius: 50%; background: var(--color-accent); border: 2px solid white; box-shadow: 0 0 0 3px #FFE5E5; }
    .history-count-badge { background: rgba(255,255,255,0.2); border-radius: 10px; padding: 5px 15px; font-size: 0.8rem; }
</style>

<div class="debitur-header">
    <div class="d-flex align-items-center mb-3">
        <a href="<?= BASE_APP ?>/history" class="text-white me-3"><i class="fa-solid fa-arrow-left"></i></a>
        <h5 class="fw-bold mb-0">Riwayat Per Debitur</h5>
    </div>
    <div class="text-center">
        <h4 class="fw-bold mb-1">Bpk. Haryanto</h4>
        <div class="history-count-badge d-inline-block">
            <i class="fa-solid fa-person-walking-arrow-right me-1"></i> <b>6 Kali</b> Dikunjungi
        </div>
    </div>
</div>

<div class="container px-4 mt-4 mb-5 pb-5">
    <ul class="timeline">
        <?php for($i=6; $i>=1; $i--): ?>
        <li class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <small class="text-muted fw-bold"><?= $i ?> April 2026</small>
                        <span class="badge rounded-pill bg-light text-primary border" style="font-size: 0.6rem;">PTP</span>
                    </div>
                    <p class="small text-dark mb-2 fw-bold">Janji bayar Rp 500rb, alasan anak sekolah.</p>
                    <a href="<?= BASE_APP ?>/kunjungan-detail" class="text-accent text-decoration-none small fw-bold">Lihat Detail <i class="fa-solid fa-chevron-right ms-1"></i></a>
                </div>
            </div>
        </li>
        <?php endfor; ?>
    </ul>
</div>