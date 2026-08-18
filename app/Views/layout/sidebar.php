<aside class="app-sidebar">
    <a href="<?= base_url('dashboard') ?>" class="sidebar-brand">
        <span class="sidebar-brand-icon">
            <i class="fas fa-chart-line"></i>
        </span>
        <span class="sidebar-brand-text">
            Monev TKA
        </span>
    </a>
    <ul class="sidebar-menu">
        <li class="sidebar-menu-title">
            Menu Utama
        </li>
        <li class="<?= url_is('dashboard') || url_is('/') ? 'active' : '' ?>">
            <a href="<?= base_url('dashboard') ?>">
                <i class="fas fa-home"></i>
                <span class="sidebar-menu-text">Dashboard</span>
            </a>
        </li>

        <!-- KHUSUS ADMIN: Master Data -->
        <?php if (session()->get('role') === 'admin') : ?>
            <li class="sidebar-menu-title">
                Master Data
            </li>
            <li class="<?= url_is('users') || url_is('users/*') ? 'active' : '' ?>">
                <a href="<?= base_url('users') ?>">
                    <i class="fas fa-users"></i>
                    <span class="sidebar-menu-text">User</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="<?= base_url('schools') ?>" class="sidebar-menu-link <?= url_is('schools*') ? 'active' : '' ?>">
                    <i class="fas fa-school sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Sekolah</span>
                </a>
            </li>
            <li>
                <a href="<?= base_url('instruments') ?>" class="nav-link <?= (service('router')->getMatchedRoute()[0] ?? '') === 'instruments' ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i>
                    <span class="sidebar-menu-text">
                        Instrument
                    </span>
                </a>
            </li>
        <?php endif; ?>

        <!-- PETUGAS & ADMIN: Monitoring & Laporan -->
        <li class="sidebar-menu-title">
            Monitoring
        </li>
        <li class="<?= url_is('visits') || url_is('visits/*') ? 'active' : '' ?>">
            <a href="<?= base_url('visits') ?>">
                <i class="fas fa-clipboard-check"></i>
                <span class="sidebar-menu-text">Pelaksanaan Monev</span>
            </a>
        </li>

        <li class="sidebar-menu-title">
            Laporan
        </li>
        <li class="<?= url_is('reports') || url_is('reports/*') ? 'active' : '' ?>">
            <a href="<?= base_url('reports') ?>">
                <i class="fas fa-chart-bar"></i>
                <span class="sidebar-menu-text">Laporan Monev</span>
            </a>
        </li>
    </ul>
</aside>