<div class="container-fluid reports-page">
    <div class="reports-header">
        <div class="reports-title">
            <div class="reports-title-icon"><i class="fas fa-file-alt"></i></div>
            <div>
                <h3>Laporan Monev</h3>
                <p>Rekap dan laporan hasil pelaksanaan monitoring dan evaluasi</p>
            </div>
        </div>
    </div>
    <div class="reports-filter-card">
        <div class="reports-filter-header">
            <div>
                <i class="fas fa-filter"></i>
                <span>Filter Laporan</span>
            </div>
            <button type="button" class="btn btn-light btn-sm" id="btnResetReport">
                <i class="fas fa-sync-alt me-1"></i>Reset
            </button>
        </div>
        <div class="reports-filter-body">
            <div class="row g-3">
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <label for="reportKeyword">Cari</label>
                    <div class="reports-input-icon">
                        <i class="fas fa-search"></i>
                        <input type="text" id="reportKeyword" class="form-control" placeholder="Sekolah, NPSN, atau wilayah">
                    </div>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="reportRegion">Wilayah</label>
                    <select id="reportRegion" class="form-select">
                        <option value="">Semua Wilayah</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="reportStatus">Status</label>
                    <select id="reportStatus" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="DRAFT">Draft</option>
                        <option value="IN_PROGRESS">Sedang Berjalan</option>
                        <option value="COMPLETED">Selesai</option>
                    </select>
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="reportDateFrom">Dari Tanggal</label>
                    <input type="date" id="reportDateFrom" class="form-control">
                </div>
                <div class="col-xl-2 col-lg-3 col-md-6">
                    <label for="reportDateTo">Sampai Tanggal</label>
                    <input type="date" id="reportDateTo" class="form-control">
                </div>
                <div class="col-xl-1 col-lg-2 col-md-6 d-flex align-items-end">
                    <button type="button" id="btnSearchReport" class="btn btn-primary reports-search-btn w-100">
                        <i class="fas fa-search me-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="reports-toolbar">
        <div class="reports-summary">
            <div class="reports-summary-icon"><i class="fas fa-list"></i></div>
            <div>
                <h5>Rekap Pelaksanaan Monev</h5>
                <span><strong id="reportTotal">0</strong> kegiatan ditemukan</span>
            </div>
        </div>
    </div>
    <div class="reports-table-card">
        <div class="table-responsive">
            <table class="table reports-table mb-0" id="reportsTable">
                <thead>
                    <tr>
                        <th width="55">No</th>
                        <th>Wilayah</th>
                        <th>Sekolah</th>
                        <th>NPSN</th>
                        <th>Tanggal</th>
                        <th>Petugas</th>
                        <th>Status</th>
                        <th width="130">Laporan</th>
                    </tr>
                </thead>
                <tbody id="reportsTableBody">
                    <tr>
                        <td colspan="8" class="reports-loading">
                            <i class="fas fa-spinner fa-spin me-2"></i>Memuat data laporan...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>