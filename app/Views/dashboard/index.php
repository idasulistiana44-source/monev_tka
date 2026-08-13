<div class="dashboard-content">
    <div class="dashboard-header">
        <div>
            <h1>Dashboard</h1>
            <p>Monitoring dan Evaluasi Pelaksanaan TKA SMA</p>
        </div>
    </div>
    <div class="dashboard-stats">
        <div class="stat-card stat-card-primary">
            <div class="stat-card-content">
                <div>
                    <div class="stat-card-label">Total Sekolah</div>
                    <div class="stat-card-value"><?= $totalSchools ?? 0 ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-school"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-card-success">
            <div class="stat-card-content">
                <div>
                    <div class="stat-card-label">Sudah Dikunjungi</div>
                    <div class="stat-card-value"><?= $visitedSchools ?? 0 ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-card-warning">
            <div class="stat-card-content">
                <div>
                    <div class="stat-card-label">Petugas Monev</div>
                    <div class="stat-card-value"><?= $totalOfficers ?? 0 ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    <?php
    $draft=$status['draft']??0;
    $berlangsung=$status['berlangsung']??0;
    $selesai=$status['selesai']??0;
    $totalStatus=$draft+$berlangsung+$selesai;
    $draftPercent=$totalStatus>0?round(($draft/$totalStatus)*100):0;
    $berlangsungPercent=$totalStatus>0?round(($berlangsung/$totalStatus)*100):0;
    $selesaiPercent=$totalStatus>0?round(($selesai/$totalStatus)*100):0;
    $sangatBaik=$readiness['Sangat Baik']??0;
    $baik=$readiness['Baik']??0;
    $cukup=$readiness['Cukup']??0;
    $kurangMemadai=$readiness['Kurang Memadai']??0;
    $readinessTotal=$sangatBaik+$baik+$cukup+$kurangMemadai;
    $readinessPercent=$readinessTotal>0?round((($sangatBaik+$baik)/$readinessTotal)*100):0;
    ?>
    <div class="dashboard-charts">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <h3 class="dashboard-panel-title">Sebaran Visitasi Monev</h3>
            </div>
            <div class="dashboard-panel-body chart-container">
                <canvas id="visitChart"></canvas>
            </div>
        </div>
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <h3 class="dashboard-panel-title">Status Visitasi Monev</h3>
            </div>
            <div class="dashboard-panel-body">
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Draft</span>
                        <span><?= $draft ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-secondary" style="width: <?= $draftPercent ?>%"></div>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Berlangsung</span>
                        <span><?= $berlangsung ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: <?= $berlangsungPercent ?>%"></div>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Selesai</span>
                        <span><?= $selesai ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?= $selesaiPercent ?>%"></div>
                    </div>
                </div>
                <div class="readiness-box">
                    <div class="readiness-header">
                        <span>Kesiapan Infrastruktur</span>
                        <strong><?= $readinessPercent ?>%</strong>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-primary" style="width: <?= $readinessPercent ?>%"></div>
                    </div>
                    <div class="readiness-description">
                        <?= $sangatBaik ?> sangat baik, <?= $baik ?> baik, <?= $cukup ?> cukup, dan <?= $kurangMemadai ?> kurang memadai dari <?= $readinessTotal ?> sekolah yang telah dinilai.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <h3 class="dashboard-panel-title">Visitasi Terbaru</h3>
                <a href="<?= site_url('visits') ?>" class="panel-link">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="dashboard-panel-body">
                <?php if (!empty($recentVisits)): ?>
                    <div class="visit-list">
                        <?php foreach ($recentVisits as $visit): ?>
                            <?php
                            $visitStatus=$visit['status']??'draft';
                            $statusLabels=[
                                'draft'=>'Draft',
                                'in_progress'=>'Berlangsung',
                                'completed'=>'Selesai',
                                'verified'=>'Terverifikasi'
                            ];
                            $statusLabel=$statusLabels[$visitStatus]??ucfirst(str_replace('_',' ',$visitStatus));
                            $visitDate=$visit['visit_date']??'';
                            if($visitDate && strtotime($visitDate)){
                                $visitDate=date('d/m/Y',strtotime($visitDate));
                            }
                            ?>
                            <div class="visit-item">
                                <div class="visit-icon">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div class="visit-info">
                                    <div class="visit-school">
                                        <?= esc($visit['school_name']??'Nama Sekolah') ?>
                                    </div>
                                    <div class="visit-meta">
                                        <span><i class="fas fa-id-card me-1"></i><?= esc($visit['npsn']??'-') ?></span>
                                        <span class="visit-meta-divider">•</span>
                                        <span><i class="far fa-calendar-alt me-1"></i><?= esc($visitDate?:'-') ?></span>
                                    </div>
                                </div>
                                <div class="visit-status">
                                    <span class="status-badge status-badge-<?= esc($visitStatus) ?>">
                                        <?= esc($statusLabel) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="empty-state-title">Belum ada data visitasi</div>
                        <div class="empty-state-text">Data visitasi akan muncul setelah kegiatan Monev dibuat.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div> 
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <h3 class="dashboard-panel-title">Informasi Monev</h3>
            </div>
            <div class="dashboard-panel-body">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <div>
                        <div class="info-title">Objek Monev</div>
                        <div class="info-text">Satuan pendidikan</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <div class="info-title">Metode Pelaksanaan</div>
                        <div class="info-text">Visitasi lapangan</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-random"></i>
                    </div>
                    <div>
                        <div class="info-title">Metode Pemilihan</div>
                        <div class="info-text">Random</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <div class="info-title">Instrumen Penilaian</div>
                        <div class="info-text">Instrumen Monev</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>