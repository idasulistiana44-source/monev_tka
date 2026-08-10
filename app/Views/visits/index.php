<main class="app-main">
    <div class="visits-page">
        <div class="visits-page-header">
            <div>
                <h1 class="visits-page-title">Visitasi</h1>
                <p class="visits-page-subtitle">Kelola pelaksanaan visitasi berdasarkan assignment petugas.</p>
            </div>
            <div class="visits-header-actions">
                <button type="button" class="btn btn-primary" id="btnAddVisit"><i class="fas fa-plus me-1"></i>Tambah Visitasi</button>
                <button type="button" class="btn btn-outline-secondary" id="btnRefreshVisit"><i class="fas fa-sync-alt"></i></button>
            </div>
        </div>
        <div class="visits-card">
            <div class="visits-card-header">
                <div class="visits-card-title"><i class="fas fa-clipboard-check"></i>Data Visitasi</div>
                <div class="visits-total"><span id="visitTotal">0</span> visitasi</div>
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
                                <th>Tanggal Visitasi</th>
                                <th>Petugas</th>
                                <th>Status</th>
                                <th width="150">Aksi</th> 
                            </tr>
                        </thead>
                        <tbody id="visitTableBody">
                            <tr>
                                <td colspan="8" class="visit-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<div class="modal fade" id="visitAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content visit-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i>Tambah Visitasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="visitAddForm">
                    <div class="mb-3">
                        <label class="form-label">Assignment <span class="text-danger">*</span></label>
                        <select id="visitAssignment" class="form-select" required>
                            <option value="">Memuat assignment...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Visitasi <span class="text-danger">*</span></label>
                        <input type="date" id="visitDate" class="form-control" required>
                    </div>
                    <div id="visitAssignmentInfo" class="visit-assignment-info"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSaveVisit"><i class="fas fa-save me-1"></i>Simpan</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteVisitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash-alt text-danger me-2"></i>Hapus Visitasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Apakah Anda yakin ingin menghapus data visitasi ini?</p>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Data jawaban instrumen visitasi juga akan dihapus dan tidak dapat dikembalikan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDeleteVisit">
                    <i class="fas fa-trash me-1"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>