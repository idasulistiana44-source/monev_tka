<aside class="app-sidebar">
    <a href="<?= base_url('dashboard') ?>" class="sidebar-brand">
        <span class="sidebar-brand-icon">
            <i class="fas fa-chart-line"></i>
        </span>
        <span class="sidebar-brand-text">Monev TKA</span>
    </a>
    <ul class="sidebar-menu">
        <li class="sidebar-menu-title">Menu Utama</li>
        <li class="<?= url_is('dashboard') || url_is('/') ? 'active' : '' ?>">
            <a href="<?= base_url('dashboard') ?>">
                <i class="fas fa-home"></i>
                <span class="sidebar-menu-text">Dashboard</span>
            </a>
        </li>
        <?php if (session()->get('role') === 'admin') : ?>
            <li class="sidebar-menu-title">Master Data</li>
            <li class="<?= url_is('users') || url_is('users/*') ? 'active' : '' ?>">
                <a href="<?= base_url('users') ?>">
                    <i class="fas fa-users"></i>
                    <span class="sidebar-menu-text">User</span>
                </a>
            </li>
            <li class="<?= url_is('schools') || url_is('schools/*') ? 'active' : '' ?>">
                <a href="<?= base_url('schools') ?>">
                    <i class="fas fa-school"></i>
                    <span class="sidebar-menu-text">Sekolah</span>
                </a>
            </li>
            <li class="<?= url_is('instruments') || url_is('instruments/*') ? 'active' : '' ?>">
                <a href="<?= base_url('instruments') ?>">
                    <i class="fas fa-file-alt"></i>
                    <span class="sidebar-menu-text">Instrument</span>
                </a>
            </li>
        <?php endif; ?>
        <li class="sidebar-menu-title">Monitoring</li>
        <li class="<?= url_is('visits') || url_is('visits/*') ? 'active' : '' ?>">
            <a href="<?= base_url('visits') ?>">
                <i class="fas fa-clipboard-check"></i>
                <span class="sidebar-menu-text">Pelaksanaan Monev</span>
            </a>
        </li>
        <li class="sidebar-menu-title">Laporan</li>
        <?php
        $isTemplateReport = service('uri')->getSegment(1) === 'template-report';
        $isEditorTemplate = service('uri')->getSegment(2) === 'editor';
        ?>
        <li class="<?= ($isTemplateReport && !$isEditorTemplate) ? 'active' : '' ?>">
            <a href="<?= site_url('template-report') ?>">
                <i class="fas fa-file-alt"></i>
                <span class="sidebar-menu-text">Template Report</span>
            </a>
        </li>
        <li class="<?= $isEditorTemplate ? 'active' : '' ?>">
            <a href="<?= site_url('template-report/editor') ?>">
                <i class="fas fa-edit"></i>
                <span class="sidebar-menu-text">Editor Template</span>
            </a>
        </li>
        <li class="<?= url_is('reports') || url_is('reports/*') ? 'active' : '' ?>">
            <a href="<?= base_url('reports') ?>">
                <i class="fas fa-chart-bar"></i>
                <span class="sidebar-menu-text">Laporan Monev</span>
            </a>
        </li>
    </ul>
</aside>