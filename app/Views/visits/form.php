<div class="visit-form-page">
    <div class="visit-form-header">
        <div>
            <div class="visit-back"><a href="<?= base_url('visits') ?>"><i class="fas fa-arrow-left me-1"></i>Kembali ke Kegiatan Monev</a></div>
            <h1 class="visit-form-title" id="formSchoolName"><?= esc($visit['school_name']??'Kegiatan Monev') ?></h1>
            <p class="visit-form-subtitle">NPSN <?= esc($visit['npsn']??'-') ?> · <?= esc($visit['level']??'-') ?> · <?= esc($visit['district']??'-') ?></p>
        </div>
        <div class="visit-form-header-actions">
            <span id="formStatus" class="visit-form-status"><?= esc($visit['status']??'DRAFT') ?></span>
        </div>
    </div>
    
    <div class="visit-form-info-card">
        <div class="visit-info-item">
            <span class="visit-info-label">Tanggal Monev</span>
            <strong><?= esc($visit['visit_date']??'-') ?></strong>
        </div>
        <div class="visit-info-item">
            <span class="visit-info-label">Tim Monev</span>
            <div class="visit-form-team">
                <?php foreach(($visit['members']??[]) as $member): ?>
                <span><i class="fas fa-user me-1"></i><?= esc($member['name']??'-') ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="visitProgress" class="visit-progress-card">
        <div class="visit-progress-top">
            <strong>Progress Pengisian</strong>
            <span id="progressText">0%</span>
        </div>
        <div class="visit-progress-bar">
            <div id="progressBar"></div>
        </div>
        <div class="visit-progress-bottom">
            <span id="answeredCount">0</span> dari <span id="requiredCount">0</span> instrumen wajib terisi
        </div>
    </div>
    <div id="instrumentContainer">
        <div class="visit-form-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat instrumen...</div>
    </div>
    <div class="card mt-4 mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <a href="<?= base_url('visits') ?>" class="btn btn-outline-secondary mb-0">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <div class="d-flex gap-2">
                <!-- Tombol Simpan Draft disembunyikan jika status sudah COMPLETED -->
                <?php if (($visit['status'] ?? '') !== 'COMPLETED'): ?>
                <button type="button" class="btn btn-outline-primary mb-0" id="btnSaveDraft">
                    <i class="fas fa-save me-1"></i> Simpan Draft
                </button>
                <button type="button" class="btn btn-primary mb-0" id="btnCompleteVisit">
                    <i class="fas fa-check me-1"></i> Selesaikan Monev
                </button>
                <?php else: ?>
                <span class="badge bg-success p-2 fs-6"><i class="fas fa-check-circle me-1"></i> Monev Telah Selesai</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="submitVisitModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-check-circle text-success me-2"></i>Selesaikan Monev</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="visit-success-icon mb-2">
                        <i class="fas fa-paper-plane fa-2x text-success"></i>
                    </div>
                    <p class="mb-1">Apakah Anda yakin ingin menyelesaikan kegiatan ini?</p>
                    <strong id="submitVisitSchool">-</strong>
                    <input type="hidden" id="submitVisitId">
                    <p class="small text-muted mt-2 mb-0">Jawaban yang diselesaikan tidak dapat diubah kembali.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btnConfirmSubmitVisit">
                        <i class="fas fa-check me-1"></i>Selesaikan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.VISIT_ID = <?= (int)($visit['id'] ?? 0) ?>;
window.baseUrl = '<?= rtrim(base_url(),'/') ?>';
window.VISITS_FORM_BASE_URL = '<?= rtrim(base_url(),'/') ?>/';
window.VISITS_FORM_CSRF_NAME = '<?= csrf_token() ?>';
window.VISITS_FORM_CSRF_HASH = '<?= csrf_hash() ?>';
window.VISIT_STATUS = '<?= esc($visit['status'] ?? 'DRAFT') ?>';
</script>