<script>
window.REPORT_URLS = {
    data: '<?= base_url('reports/data') ?>',
    updateStatus: '<?= base_url('reports/update-followup-status') ?>',
    exportExcel: '<?= base_url('reports/export-excel') ?>',
    exportPdf: '<?= base_url('reports/export-pdf') ?>'
};
</script>
<div class="reports-page">
    <div class="reports-page-header">
        <div>
            <h1 class="reports-page-title">Laporan Rekapitulasi Monev</h1>
            <p class="reports-page-subtitle">Analisis kesiapan per aspek dan tindak lanjut lapangan.</p>
        </div>
        <div class="reports-header-actions">
            <button type="button" class="btn btn-outline-success" id="btnExportExcel"><i class="fas fa-file-excel me-1"></i>Ekspor Excel</button>
            <button type="button" class="btn btn-outline-danger" id="btnExportPdf"><i class="fas fa-file-pdf me-1"></i>Ekspor PDF</button>
        </div>
    </div>
    <div class="reports-card mb-4">
        <div class="reports-card-header"><i class="fas fa-filter me-2"></i>Filter Laporan</div>
        <div class="reports-card-body">
            <form id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="filterStartDate" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="filterEndDate" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kecamatan</label>
                       <select name="district_id" id="filterDistrict" class="form-select">
                            <option value="">Semua Kecamatan</option>
                            <?php foreach($filters['districts'] as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= $d['district_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenjang</label>
                        <select name="level" id="filterLevel" class="form-select">
                            <option value="">Semua Jenjang</option>
                            <?php foreach($filters['levels'] as $l): ?>
                                <option value="<?= $l['level'] ?>"><?= $l['level'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sekolah</label>
                        <select name="school_id" id="filterSchool" class="form-select">
                            <option value="">Semua Sekolah</option>
                            <?php foreach($filters['schools'] as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Visitasi</label>
                        <select name="status" id="filterStatus" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="DRAFT">Draft</option>
                            <option value="IN_PROGRESS">Berlangsung</option>
                            <option value="COMPLETED">Selesai</option>
                            <option value="VERIFIED">Terverifikasi</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Petugas Monev</label>
                        <select name="officer_id" id="filterOfficer" class="form-select">
                            <option value="">Semua Petugas</option>
                            <?php foreach($filters['officers'] as $o): ?>
                                <option value="<?= $o['id'] ?>"><?= $o['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btnApplyFilter"><i class="fas fa-search me-1"></i>Terapkan</button>
                        <button type="button" class="btn btn-light" id="btnResetFilter"><i class="fas fa-undo"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="reports-card mb-4">
        <div class="reports-card-header"><i class="fas fa-chart-pie me-2"></i>Rekap Kesiapan TKA per Aspek</div>
        <div class="reports-card-body p-0">
            <div class="table-responsive">
                <table class="table reports-table align-middle">
                    <thead>
                        <tr>
                            <th>Aspek</th>
                            <th width="140" class="text-center">Sangat Memadai</th>
                            <th width="120" class="text-center">Baik</th>
                            <th width="120" class="text-center">Cukup</th>
                            <th width="140" class="text-center">Kurang Memadai</th>
                            <th width="160" class="text-center">Status Dominan</th>
                        </tr>
                    </thead>
                    <tbody id="aspectTableBody">
                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="reports-card">
        <div class="reports-card-header d-flex justify-content-between align-items-center">
            <div><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Sekolah yang Perlu Tindak Lanjut</div>
            <select id="filterFollowupStatus" class="form-select form-select-sm w-auto">
                <option value="">Semua Status Tindak Lanjut</option>
                <option value="BELUM">Belum</option>
                <option value="PROSES">Proses</option>
                <option value="SELESAI">Selesai</option>
            </select>
        </div>
        <div class="reports-card-body p-0">
            <div class="table-responsive">
                <table class="table reports-table align-middle">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Sekolah</th>
                            <th>Aspek</th>
                            <th>Temuan</th>
                            <th>Rekomendasi</th>
                            <th width="140">Status</th>
                        </tr>
                    </thead>
                    <tbody id="followupTableBody">
                        <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>