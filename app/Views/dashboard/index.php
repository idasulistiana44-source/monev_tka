<div class="dashboard-content">
    <div class="dashboard-header">
        <div>
            <h1>Dashboard Monev TKA-P</h1>
            <p>Monitoring dan Evaluasi Pelaksanaan TKA-P</p>
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
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-school"></i></div>
            <div>
                <div class="stat-card-label">Sekolah Disurvei</div>
                <div class="stat-card-value" id="summaryTotalSchools">0</div>
                <div class="stat-card-note">Sekolah Monev</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-clipboard-check"></i></div>
            <div>
                <div class="stat-card-label">Visitasi Selesai</div>
                <div class="stat-card-value" id="summaryCompleted">0</div>
                <div class="stat-card-note">Sudah dilakukan Monev</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-desktop"></i></div>
            <div>
                <div class="stat-card-label">Kesiapan Infrastruktur</div>
                <div class="stat-card-value" id="summaryReadiness">0%</div>
                <div class="stat-card-note">Baik / Sangat Baik</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-file-alt"></i></div>
            <div>
                <div class="stat-card-label">Kelengkapan Monev</div>
                <div class="stat-card-value" id="summaryDocuments">0%</div>
                <div class="stat-card-note">Berkas & dokumentasi</div>
            </div>
        </div>
    </div>
    <div class="dashboard-charts">
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
                    <option value="INF-05">Ruang yang Dipakai TKA-P</option>
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
                            <option value="asc">Terendah → Tertinggi</option>
                            <option value="desc">Tertinggi → Terendah</option>
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
                            <h4>Detail Sekolah</h4>
                            <span>Daftar sekolah berdasarkan daya listrik.</span>
                        </div>
                        <select id="electricityFilter" class="form-select">
                            <option value="">Semua Daya</option>
                        </select>
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
                                    <td colspan="4" class="table-empty">Belum ada data.</td>
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
                            <option value="LAN_WIFI">LAN + WiFi</option>
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
                    <h3 class="dashboard-panel-title">Bandwidth Upload</h3>
                    <p class="dashboard-panel-subtitle">Distribusi bandwidth upload sekolah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container chart-small">
                    <canvas id="uploadChart"></canvas>
                </div>
                <div class="dashboard-highlight">
                    <i class="fas fa-upload"></i>
                    <div>
                        <span>Bandwidth upload terbanyak</span>
                        <strong id="uploadMostUsed">-</strong>
                        <small id="uploadMostUsedCount">0 sekolah</small>
                    </div>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <h4>Detail Sekolah</h4>
                        <select id="uploadFilter" class="form-select">
                            <option value="">Semua Bandwidth</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>Upload</th>
                                </tr>
                            </thead>
                            <tbody id="uploadTableBody">
                                <tr>
                                    <td colspan="3" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="uploadPagination"></div>
                </div>
            </div>
        </div>
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Bandwidth Download</h3>
                    <p class="dashboard-panel-subtitle">Distribusi bandwidth download sekolah.</p>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="chart-container chart-small">
                    <canvas id="downloadChart"></canvas>
                </div>
                <div class="dashboard-highlight">
                    <i class="fas fa-download"></i>
                    <div>
                        <span>Bandwidth download terbanyak</span>
                        <strong id="downloadMostUsed">-</strong>
                        <small id="downloadMostUsedCount">0 sekolah</small>
                    </div>
                </div>
                <div class="dashboard-table">
                    <div class="dashboard-table-header">
                        <h4>Detail Sekolah</h4>
                        <select id="downloadFilter" class="form-select">
                            <option value="">Semua Bandwidth</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="monev-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Sekolah</th>
                                    <th>Download</th>
                                </tr>
                            </thead>
                            <tbody id="downloadTableBody">
                                <tr>
                                    <td colspan="3" class="table-empty">Belum ada data.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="monev-pagination" id="downloadPagination"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-charts">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <h3 class="dashboard-panel-title">Kesiapan Siswa TKA-P</h3>
                    <p class="dashboard-panel-subtitle">Perbandingan siswa kelas 12 yang mengikuti dan tidak mengikuti TKA-P.</p>
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
                    <h3 class="dashboard-panel-title">Sesi TKA-P</h3>
                    <p class="dashboard-panel-subtitle">Distribusi sesi TKA-P yang digunakan sekolah.</p>
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
                    <h3 class="dashboard-panel-title">Gelombang TKA-P</h3>
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
                    <h3 class="dashboard-panel-title">Kesiapan Infrastruktur TKA-P</h3>
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
    <div class="dashboard-export">
        <div>
            <h3>Export Laporan Monev</h3>
            <p>Data mengikuti periode dan filter yang dipilih.</p>
        </div>
        <div>
            <button type="button" class="dashboard-btn dashboard-btn-outline" id="btnExportPDF"><i class="fas fa-file-pdf"></i>Export PDF</button>
            <button type="button" class="dashboard-btn dashboard-btn-success" id="btnExportExcel"><i class="fas fa-file-excel"></i>Export Excel</button>
        </div>
    </div>
</div>
<script>
window.dashboardConfig={
    dataUrl:"<?= site_url('dashboard/data') ?>",
    exportUrl:"<?= site_url('dashboard/export') ?>"
};
window.dashboardData=<?= json_encode([
    'summary'=>$summary??[],
    'infrastructure'=>$infrastructure??[],
    'electricity'=>$electricity??[],
    'internet'=>$internet??[],
    'upload'=>$upload??[],
    'download'=>$download??[],
    'students'=>$students??[],
    'sessions'=>$sessions??[],
    'waves'=>$waves??[],
    'readiness'=>$readiness??[],
    'readinessData'=>$readinessData??[]
],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>
<div class="dashboard-content">
    ...
</div>