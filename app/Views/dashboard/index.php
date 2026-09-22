<div class="dashboard-content">
    <div class="dashboard-header">
        <div>
            <h1>Dashboard Monev TKA Provinsi</h1>
            <p>Monitoring dan Evaluasi Pelaksanaan TKAP</p>
        </div>
    </div>
    <div class="dashboard-filter">
        <div class="filter-group">
            <label>Tanggal Mulai</label>
            <input type="date" id="filterStartDate" class="form-control">
        </div>
        <div class="filter-group">
            <label>Tanggal Selesai</label>
            <input type="date" id="filterEndDate" class="form-control">
        </div>
        <div class="filter-group">
            <label>Jenjang</label>
            <select id="filterJenjang" class="form-select">
                <option value="">Semua Jenjang</option>
                <option value="SMA">SMA</option>
                <option value="SMK">SMK</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Wilayah</label>
            <select id="filterWilayah" class="form-select">
                <option value="">Semua Wilayah</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Kecamatan</label>
            <select id="filterKecamatan" class="form-select">
                <option value="">Semua Kecamatan</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="button" class="dashboard-btn" id="btnApplyFilter"><i class="fas fa-filter"></i>Tampilkan</button>
            <button type="button" class="dashboard-btn dashboard-btn-light" id="btnResetFilter"><i class="fas fa-sync-alt"></i>Reset</button>
        </div>
    </div>
    <div class="dashboard-stats">
        <!-- Jumlah Sekolah -->
        <div class="stat-card">
            <div class="stat-card-icon">
                <i class="fas fa-school"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-label">Jumlah Sekolah</div>
                <div class="stat-card-value" id="summaryTotalSchools">
                    <?= esc($totalSchools ?? 0) ?>
                </div>
                <div class="stat-card-note">Total sekolah terdaftar</div>
            </div>
        </div>
        
        <!-- Draft -->
        <div class="stat-card">
            <div class="stat-card-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-label">Draft</div>
                <div class="stat-card-value" id="summaryDraft">
                     <?= esc($draftSchools ?? 0) ?>
                </div>
                <div class="stat-card-note">Monev belum dimulai</div>
            </div>
        </div>

        <!-- Sedang Disurvei -->
        <div class="stat-card">
            <div class="stat-card-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-label">Sedang Disurvei</div>
                <div class="stat-card-value" id="summaryInProgress">
                    0
                </div>
                <div class="stat-card-note">Survey sedang berlangsung</div>
            </div>
        </div>

        <!-- Sudah Monev -->
        <div class="stat-card">
            <div class="stat-card-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-label">Sudah Di-Monev</div>
                <div class="stat-card-value" id="summaryCompleted">
                    <?= esc($visitedSchools ?? 0) ?>
                </div>
                <div class="stat-card-note">Monev selesai / terverifikasi</div>
            </div>
        </div>

        <!-- Kesiapan Infrastruktur -->
        <div class="stat-card">
            <div class="stat-card-icon">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-label">Kesiapan Infrastruktur</div>
                <div class="stat-card-value" id="summaryReadiness">
                    <?= esc($readinessPercent ?? 0) ?>%
                </div>
                <div class="stat-card-note">Baik / Sangat Baik</div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-charts">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Rekap Status Monev per Wilayah</h3>
                    <p class="dashboard-panel-subtitle">Rekap status pelaksanaan Monev berdasarkan wilayah sesuai filter yang dipilih.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="table-responsive">
                    <table class="table dashboard-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Wilayah</th>
                                <th>Sudah Monev</th>
                                <th>Sedang Berlangsung</th>
                                <th>Draft Monev</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody id="monevStatusTableBody">
                            <tr>
                                <td colspan="6" class="table-empty">Belum ada data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="monevStatusPagination"></div>
            </div>
        </div>
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Rekap Laporan Pelaksana Monev</h3>
                    <p class="dashboard-panel-subtitle">Rekap sasaran dan progres Monev berdasarkan pelaksana dan wilayah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="table-responsive">
                    <table class="monev-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pelaksana</th>
                                <th>Wilayah yang dikerjakan</th>
                                <th style="text-align:center;">Jumlah Sasaran</th>
                                <th style="text-align:center;">Sudah Monev</th>
                                <th style="text-align:center;">Sedang Berlangsung</th>
                                <th style="text-align:center;">Belum Monev</th>
                                <th style="text-align:center;">Progres</th>
                                <th style="text-align:center;">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="officerRecapTableBody">
                            <tr>
                                <td colspan="9" class="table-empty">Belum ada data.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="officerRecapPagination"></div>
            </div>
        </div>
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Permasalahan & Rekomendasi</h3>
                    <p class="dashboard-panel-subtitle">Permasalahan yang ditemukan pada hasil Monev dan rekomendasi tindak lanjut.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="table-responsive">
                    <table class="monev-table">
                        <thead>
                            <tr>
                                <th style="width:60px;">No</th>
                                <th>Permasalahan</th>
                                <th style="width:120px;text-align:center;">Jumlah Sekolah</th>
                                <th>Rekomendasi</th>
                                <th style="width:100px;text-align:center;">Detail</th>
                            </tr>
                        </thead>
                        <tbody id="problemRecommendationTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Infrastruktur dan Sarana</h3>
                    <p class="dashboard-panel-subtitle">Perbandingan jumlah perangkat dan fasilitas sekolah.</p>
                </div>
                <select id="infrastructureParameter" class="form-select dashboard-parameter">
                    <option value="INF-01">Komputer / PC Milik</option>
                    <option value="INF-02" selected>Laptop Milik</option>
                    <option value="INF-03">Laptop Bukan Milik</option>
                    <option value="INF-04">Labkom</option>
                    <option value="INF-05">Ruang yang Dipakai TKAP</option>
                    <option value="INF-06">Switch Hub</option>
                    <option value="INF-07">UPS</option>
                    <option value="INF-08">Access Point</option>
                </select>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container">
                    <canvas id="infrastructureChart"></canvas>
                </div>
                <div class="dashboard-summary">
                    <div><span>Terendah</span><strong id="infrastructureMin">0</strong></div>
                    <div><span>Rata-rata</span><strong id="infrastructureAverage">0</strong></div>
                    <div><span>Tertinggi</span><strong id="infrastructureMax">0</strong></div>
                    <div><span>Sekolah</span><strong id="infrastructureSchoolCount">0</strong></div>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <div>
                            <h4>Detail Sekolah</h4>
                            <span id="infrastructureTableInfo">Menampilkan 5 data</span>
                        </div>
                        <select id="infrastructureSort" class="form-select">
                            <option value="desc">Tertinggi → Terendah</option>
                            <option value="asc">Terendah → Tertinggi</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>NPSN</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody id="infrastructureTableBody">
                                <tr>
                                    <td colspan="4" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="infrastructurePagination"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-charts">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Daya Listrik</h3>
                    <p class="dashboard-panel-subtitle">Distribusi daya listrik yang digunakan sekolah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container chart-medium">
                    <canvas id="electricityChart"></canvas>
                </div>
                <div class="dashboard-highlight">
                    <i class="fas fa-bolt"></i>
                    <div>
                        <span>Daya yang paling banyak digunakan</span>
                        <strong id="electricityMostUsed">-</strong>
                        <small id="electricityMostUsedCount">0 sekolah</small>
                    </div>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <div>
                            <h4>Rekap Penggunaan Daya</h4>
                            <span>Jumlah sekolah berdasarkan kapasitas daya listrik.</span>
                        </div>
                         <select id="electricityCountSort" class="form-select" style="width:220px;">
                            <option value="desc">Tertinggi → Terendah</option>
                            <option value="asc">Terendah → Tertinggi</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Daya Listrik</th>
                                    <th>Jumlah Sekolah</th>
                                </tr>
                            </thead>
                            <tbody id="electricityCountTableBody">
                                <tr>
                                    <td colspan="3" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="electricityCountPagination"></div>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <div>
                            <h4>Detail Sekolah</h4>
                            <span id="electricityTableInfo">Pilih daya listrik untuk melihat sekolah.</span>
                        </div>
                        <div style="min-width:230px;">
                            <select id="electricityDetailFilter" class="form-select" style="width:100%;">
                                <option value="">Pilih Daya Listrik</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>NPSN</th>
                                    <th>Daya</th>
                                </tr>
                            </thead>
                            <tbody id="electricityTableBody">
                                <tr>
                                    <td colspan="4" class="table-empty">Pilih daya listrik untuk melihat sekolah.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="electricityPagination"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-charts">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Jaringan Internet</h3>
                    <p class="dashboard-panel-subtitle">Distribusi jenis jaringan internet yang digunakan sekolah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container chart-medium">
                    <canvas id="internetChart"></canvas>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <div>
                            <h4>Detail Sekolah</h4>
                            <span>Daftar sekolah berdasarkan jaringan.</span>
                        </div>
                        <select id="internetFilter" class="form-select">
                            <option value="">Semua Jaringan</option>
                            <option value="LAN">LAN</option>
                            <option value="WIFI">WiFi</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>NPSN</th>
                                    <th>Jaringan</th>
                                </tr>
                            </thead>
                            <tbody id="internetTableBody">
                                <tr>
                                    <td colspan="4" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="internetPagination"></div>
                </div>
            </div>
        </div>
    </div>
   <div class="dashboard-row">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Bandwidth ISP Utama</h3>
                    <p class="dashboard-panel-subtitle">Bandwidth ISP utama yang digunakan sekolah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container chart-small">
                    <canvas id="ispUtamaChart"></canvas>
                </div>
                <div class="dashboard-highlight">
                    <i class="fas fa-download"></i>
                    <div>
                        <span>Bandwidth ISP Utama terbanyak</span>
                        <strong id="ispUtamaMostUsed">-</strong>
                        <small id="ispUtamaMostUsedCount">0 sekolah</small>
                    </div>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <h4>Detail Sekolah</h4>
                        <select id="ispUtamaFilter" class="form-select">
                            <option value="">Semua Bandwidth</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>Bandwidth</th>
                                </tr>
                            </thead>
                            <tbody id="ispUtamaTableBody">
                                <tr>
                                    <td colspan="3" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="ispUtamaPagination"></div>
                </div>
            </div>
        </div>
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Bandwidth ISP Cadangan</h3>
                    <p class="dashboard-panel-subtitle">Bandwidth ISP cadangan yang digunakan sekolah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container chart-small">
                    <canvas id="ispCadanganChart"></canvas>
                </div>
                <div class="dashboard-highlight">
                    <i class="fas fa-upload"></i>
                    <div>
                        <span>Bandwidth ISP cadangan terbanyak</span>
                        <strong id="ispCadanganMostUsed">-</strong>
                        <small id="ispCadanganMostUsedCount">0 sekolah</small>
                    </div>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <h4>Detail Sekolah</h4>
                        <select id="ispCadanganFilter" class="form-select">
                            <option value="">Semua Bandwidth</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>Bandwidth</th>
                                </tr>
                            </thead>
                            <tbody id="ispCadanganTableBody">
                                <tr>
                                    <td colspan="3" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="ispCadanganPagination"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-charts">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Kesiapan Siswa TKAP</h3>
                    <p class="dashboard-panel-subtitle">Perbandingan siswa kelas 12 yang mengikuti dan tidak mengikuti TKAP.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container">
                    <canvas id="studentReadinessChart"></canvas>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <div>
                            <h4>Detail Sekolah</h4>
                            <span>Data siswa kelas 12 setiap sekolah.</span>
                        </div>
                        <select id="studentReadinessSort" class="form-select">
                            <option value="desc">Persentase Ikut Tertinggi</option>
                            <option value="asc">Persentase Ikut Terendah</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>Total</th>
                                    <th>Ikut</th>
                                    <th>Tidak Ikut</th>
                                    <th>% Ikut</th>
                                </tr>
                            </thead>
                            <tbody id="studentReadinessTableBody">
                                <tr>
                                    <td colspan="6" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="studentReadinessPagination"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Sesi TKAP</h3>
                    <p class="dashboard-panel-subtitle">Distribusi sesi TKAP yang digunakan sekolah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container chart-small">
                    <canvas id="sessionChart"></canvas>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <h4>Detail Sekolah</h4>
                        <select id="sessionFilter" class="form-select">
                            <option value="">Semua Sesi</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>Sesi</th>
                                </tr>
                            </thead>
                            <tbody id="sessionTableBody">
                                <tr>
                                    <td colspan="3" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="sessionPagination"></div>
                </div>
            </div>
        </div>
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Gelombang TKAP</h3>
                    <p class="dashboard-panel-subtitle">Distribusi jumlah gelombang yang diikuti sekolah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container chart-small">
                    <canvas id="waveChart"></canvas>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <h4>Detail Sekolah</h4>
                        <select id="waveFilter" class="form-select">
                            <option value="">Semua Gelombang</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>Gelombang</th>
                                </tr>
                            </thead>
                            <tbody id="waveTableBody">
                                <tr>
                                    <td colspan="3" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="wavePagination"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-charts">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Kesiapan Infrastruktur TKAP</h3>
                    <p class="dashboard-panel-subtitle">Distribusi penilaian kesiapan infrastruktur sekolah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container">
                    <canvas id="readinessChart"></canvas>
                </div>
                <div class="dashboard-summary readiness-summary">
                    <div><span>Sangat Baik</span><strong id="readinessExcellent">0</strong></div>
                    <div><span>Baik</span><strong id="readinessGood">0</strong></div>
                    <div><span>Cukup</span><strong id="readinessFair">0</strong></div>
                    <div><span>Kurang Memadai</span><strong id="readinessPoor">0</strong></div>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <div>
                            <h4>Detail Sekolah</h4>
                            <span>Daftar sekolah berdasarkan hasil penilaian.</span>
                        </div>
                        <select id="readinessFilter" class="form-select">
                            <option value="">Semua Kategori</option>
                            <option value="Sangat Baik">Sangat Baik</option>
                            <option value="Baik">Baik</option>
                            <option value="Cukup">Cukup</option>
                            <option value="Kurang Memadai">Kurang Memadai</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>NPSN</th>
                                    <th>Kesiapan</th>
                                </tr>
                            </thead>
                            <tbody id="readinessTableBody">
                                <tr>
                                    <td colspan="4" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="readinessPagination"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-export" style="display:none;">
        <div>
            <h3>Export Laporan Monev</h3>
            <p>Data mengikuti periode dan filter yang dipilih.</p>
        </div>
        <div>
            <button type="button" class="dashboard-btn dashboard-btn-outline" id="btnExportPDF"><i class="fas fa-file-pdf"></i>Export PDF</button>
        </div>
    </div>
</div>
<div class="modal fade" id="problemDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="problemDetailTitle">Detail Permasalahan</h5>
                    <small class="text-muted" id="problemDetailSubtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead id="problemDetailHead"></thead>
                        <tbody id="problemDetailBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<script>
window.dashboardConfig={
    dataUrl:"<?= site_url('dashboard/data') ?>",
    exportUrl:"<?= site_url('dashboard/export') ?>",
    regionsUrl:"<?= site_url('dashboard/regions') ?>",
    districtsUrl:"<?= site_url('dashboard/districts') ?>"
};
window.dashboardData=<?= json_encode([
    'summary'=>$summary??[],
    'infrastructure'=>$infrastructure??[],
    'electricity'=>$electricity??[],
    'internet'=>$internet??[],
    'ispUtama'    => $ispUtama ?? [],
    'ispCadangan' => $ispCadangan ?? [],
    'students'=>$students??[],
    'sessions'=>$sessions??[],
    'waves'=>$waves??[],
    'readiness'=>$readiness??[],
    'readinessData'=>$readinessData??[],
    'problemRecommendations'=>$problemRecommendations??[]
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>
<div class="dashboard-content">
    ...
</div>