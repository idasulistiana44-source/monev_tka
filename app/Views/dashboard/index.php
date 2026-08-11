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
                    <div class="stat-card-value"><?= $totalTeachers ?? 0 ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-card-danger">
            <div class="stat-card-content">
                <div>
                    <div class="stat-card-label">Total Visitasi</div>
                    <div class="stat-card-value"><?= $totalVisits ?? 0 ?></div>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>
        </div>
    </div>
    <?php
    $draft = $status['draft'] ?? 0;
    $submitted = $status['submitted'] ?? 0;
    $verified = $status['verified'] ?? 0;
    $totalStatus = $draft + $submitted + $verified;
    $draftPercent = $totalStatus > 0 ? round(($draft / $totalStatus) * 100) : 0;
    $submittedPercent = $totalStatus > 0 ? round(($submitted / $totalStatus) * 100) : 0;
    $verifiedPercent = $totalStatus > 0 ? round(($verified / $totalStatus) * 100) : 0;
    $readinessYes = $readiness['YA'] ?? 0;
    $readinessNo = $readiness['TIDAK'] ?? 0;
    $readinessTotal = $readinessYes + $readinessNo;
    $readinessPercent = $readinessTotal > 0 ? round(($readinessYes / $readinessTotal) * 100) : 0;
    ?>
    <div class="dashboard-charts">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <h3 class="dashboard-panel-title">Grafik Visitasi Monev</h3>
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
                        <span>Submitted</span>
                        <span><?= $submitted ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-warning" style="width: <?= $submittedPercent ?>%"></div>
                    </div>
                </div>
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Verified</span>
                        <span><?= $verified ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?= $verifiedPercent ?>%"></div>
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
                        <?= $readinessYes ?> sekolah siap dari <?= $readinessTotal ?> sekolah yang telah dinilai.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-panel">
            <div class="dashboard-panel-header">
                <h3 class="dashboard-panel-title">Visitasi Terbaru</h3>
                <a href="#" class="panel-link">Lihat Semua</a>
            </div>
            <div class="dashboard-panel-body">
                <?php if (!empty($recentVisits)): ?>
                    <ul class="visit-list">
                        <?php foreach ($recentVisits as $visit): ?>
                            <?php $visitStatus = $visit['status'] ?? 'draft'; ?>
                            <li class="visit-item">
                                <div class="visit-icon">
                                    <i class="fas fa-school"></i>
                                </div>
                                <div class="visit-info">
                                    <div class="visit-school">
                                        <?= esc($visit['school_name'] ?? 'Nama Sekolah') ?>
                                    </div>
                                    <div class="visit-meta">
                                        <?= esc($visit['npsn'] ?? '-') ?>
                                        &nbsp;•&nbsp;
                                        <?= esc($visit['visit_date'] ?? '-') ?>
                                        <?php if (!empty($visit['user_name'])): ?>
                                            &nbsp;•&nbsp;
                                            <?= esc($visit['user_name']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div>
                                    <span class="status-badge status-badge-<?= esc($visitStatus) ?>">
                                        <?= ucfirst(esc($visitStatus)) ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="empty-state-title">Belum ada data visitasi</div>
                        <div class="empty-state-text">Data visitasi akan muncul setelah Petugas melakukan monev.</div>
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
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <div class="info-title">Periode Monev</div>
                        <div class="info-text">Pelaksanaan TKA SMA</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <div>
                        <div class="info-title">Sekolah Sasaran</div>
                        <div class="info-text"><?= $totalSchools ?? 0 ?> sekolah terdaftar</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <div class="info-title">Tim Monev</div>
                        <div class="info-text"><?= $totalUsers ?? 0 ?> Petugas ditugaskan</div>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="info-title">Progress Visitasi</div>
                        <div class="info-text"><?= $visitedSchools ?? 0 ?> dari <?= $totalSchools ?? 0 ?> sekolah telah dikunjungi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
