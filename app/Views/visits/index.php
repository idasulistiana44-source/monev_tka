<script>
window.VISITS_BASE_URL='<?= rtrim(base_url(),'/') ?>/';
window.VISITS_CSRF_NAME='<?= csrf_token() ?>';
window.VISITS_CSRF_HASH='<?= csrf_hash() ?>';
</script>
<div class="visits-page">
    <div class="visits-page-header">
        <div>
            <h1 class="visits-page-title">Kegiatan Monev</h1>
            <p class="visits-page-subtitle">Kelola kegiatan monitoring dan evaluasi sekolah.</p>
        </div>
        <div class="visits-header-actions">
            <button type="button" class="btn btn-primary" id="btnAddVisit"><i class="fas fa-plus me-1"></i>Tambah Monev</button>
            <button type="button" class="btn btn-outline-secondary" id="btnRefreshVisit"><i class="fas fa-sync-alt"></i></button>
        </div>
    </div>
    <div class="visits-card">
        <div class="visits-card-header">
            <div class="visits-card-title"><i class="fas fa-clipboard-check"></i>Data Kegiatan Monev</div>
            <div class="visits-total"><span id="visitTotal">0</span> kegiatan</div>
        </div>
        <div class="visits-card-body">
            <div class="visits-toolbar">
                <div class="visits-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchVisit" class="form-control" placeholder="Cari NPSN atau nama sekolah..." autocomplete="off">
                </div>
                <select id="filterStatus" class="form-select visits-filter">
                    <option value="">Semua Status</option>
                    <option value="DRAFT">Draft</option>
                    <option value="IN_PROGRESS">Berlangsung</option>
                    <option value="COMPLETED">Selesai</option>
                    <option value="VERIFIED">Terverifikasi</option>
                </select>
            </div>
            <div class="visits-table-responsive">
                <table class="table visits-table">
                    <thead>
                        <tr>
                            <th width="55">No</th>
                            <th>NPSN</th>
                            <th>Sekolah</th>
                            <th>Level</th>
                            <th>Tanggal Monev</th>
                            <th>Tim Monev</th>
                            <th>Status</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="visitTableBody">
                        <tr>
                            <td colspan="8" class="visit-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="visitEmpty" class="visit-empty" style="display:none;">
                <div class="visit-empty-icon"><i class="fas fa-clipboard-check"></i></div>
                <h5>Belum ada kegiatan Monev</h5>
                <p>Buat kegiatan Monev dengan memilih sekolah dan satu atau beberapa petugas.</p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteVisitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash-alt text-danger me-2"></i>Hapus Monev</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="visit-delete-icon"><i class="fas fa-trash-alt"></i></div>
                <p class="mb-1">Yakin ingin menghapus kegiatan Monev ini?</p>
                <strong id="deleteVisitSchool">-</strong>
                <input type="hidden" id="deleteVisitId">
                <p class="small text-muted mt-2 mb-0">Tim dan jawaban Monev akan ikut dihapus.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDeleteVisit"><i class="fas fa-trash me-1"></i>Hapus</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="visitAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content visit-modal">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i>Buat Kegiatan Monev</h5>
                    <small class="text-muted">Pilih sekolah dan petugas yang akan melakukan Monev.</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="visitAddForm">
                <div class="modal-body">
                    <div class="visit-form-section">
                        <div class="visit-section-title"><i class="fas fa-school"></i><span>1. Sekolah</span></div>
                        <div class="mb-3">
                            <label class="form-label">Sekolah <span class="text-danger">*</span></label>
                            <select id="visitSchool" name="school_id" class="form-select" required>
                                <option value="">Memuat sekolah...</option>
                            </select>
                        </div>
                        <div id="visitSchoolInfo" class="visit-school-info" style="display:none;">
                            <div class="visit-school-icon"><i class="fas fa-school"></i></div>
                            <div>
                                <strong id="visitSchoolName">-</strong>
                                <div id="visitSchoolMeta">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="visit-form-section">
                        <div class="visit-section-title"><i class="fas fa-users"></i><span>2. Tim Monev</span></div>
                        <div class="visit-team-help">Pilih petugas satu per satu. Petugas yang dipilih akan menjadi anggota tim Monev untuk sekolah ini.</div>
                        <div class="mb-3">
                            <label class="form-label">Pilih Petugas <span class="text-danger">*</span></label>
                            <select id="visitOfficerSelect" class="form-select">
                                <option value="">Pilih Petugas</option>
                            </select>
                        </div>
                        <div class="visit-selected-team">
                            <div class="visit-selected-title">Petugas Terpilih <span id="visitSelectedCount">0</span></div>
                            <div id="visitSelectedTeam" class="visit-selected-list">
                                <span class="visit-no-team">Belum ada petugas dipilih.</span>
                            </div>
                        </div>
                    </div>
                    <div class="visit-form-section">
                        <div class="visit-section-title"><i class="fas fa-calendar-alt"></i><span>3. Pelaksanaan</span></div>
                        <div class="mb-0">
                            <label class="form-label">Tanggal Monev <span class="text-danger">*</span></label>
                            <input type="date" id="visitDate" name="visit_date" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveVisit"><i class="fas fa-save me-1"></i>Buat Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
window.VISITS_URLS={
    data:'<?= site_url('visits/data') ?>',
    schools:'<?= site_url('visits/schools') ?>',
    officers:'<?= site_url('visits/officers') ?>',
    create:'<?= site_url('visits/create') ?>',
    delete:'<?= site_url('visits/delete') ?>'
};
window.VISITS_CSRF_NAME='<?= csrf_token() ?>';
window.VISITS_CSRF_HASH='<?= csrf_hash() ?>';
</script>