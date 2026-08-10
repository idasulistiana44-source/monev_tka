<aside class="app-sidebar">
    <a
        href="<?= base_url('dashboard') ?>"
        class="sidebar-brand"
    >
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
             <a href="<?= base_url('assignments') ?>" class="<?= url_is('assignments*') ? 'active' : '' ?>">
                <i class="fas fa-tasks"></i>
                <span class="sidebar-menu-text">Assignments</span>
            </a>
       </li>
        <li>
            <a href="<?= base_url('instruments') ?>" class="nav-link <?= service('router')->getMatchedRoute()[0] === 'instruments' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span class="sidebar-menu-text">
                    Instrument
                </span>
            </a>
        </li>
        <li class="sidebar-menu-title">
            Monitoring
        </li>
        <li>
            <a href="<?= base_url('visits') ?>" class="nav-link <?= service('router')->getMatchedRoute()[0] === 'visits' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-check"></i>
                <span  class="sidebar-menu-text">Visitasi</span>
            </a>
        </li> 
        <li>
            <a href="#" title="Dokumentasi">
                <i class="fas fa-camera"></i>
                <span class="sidebar-menu-text">
                    Dokumentasi
                </span>
            </a>
        </li>
        <li class="sidebar-menu-title">
            Laporan
        </li>
        <li>
            <a href="#" title="Rekap Monev">
                <i class="fas fa-chart-bar"></i>
                <span class="sidebar-menu-text">
                    Rekap Monev
                </span>
            </a>
        </li>
        <li>
            <a href="#" title="Export">
                <i class="fas fa-file-export"></i>
                <span class="sidebar-menu-text">
                    Export
                </span>
            </a>
        </li>
    </ul>
</aside>