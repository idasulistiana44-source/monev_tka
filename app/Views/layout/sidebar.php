<aside class="app-sidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-between p-3">
        <a href="<?= base_url('dashboard') ?>" class="sidebar-brand d-flex align-items-center text-decoration-none">
            <span class="sidebar-brand-icon me-2">
                <i class="fas fa-chart-line"></i>
            </span>
            <span class="sidebar-brand-text">Monev TKAP</span>
        </a>
        <!-- Tombol Close (Khusus Mobile/Tablet) -->
        <button type="button" class="btn-sidebar-close d-lg-none bg-transparent border-0 text-white p-0" id="sidebarCloseBtn" style="font-size: 1.25rem; cursor: pointer;">
            <i class="fas fa-times"></i>
        </button>
    </div>

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
        <?php if (strtolower((string) session()->get('role')) === 'admin'): ?>
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
        <?php endif; ?>
        <li class="<?= url_is('reports') || url_is('reports/*') ? 'active' : '' ?>">
            <a href="<?= base_url('reports') ?>">
                <i class="fas fa-chart-bar"></i>
                <span class="sidebar-menu-text">Laporan Monev</span>
            </a>
        </li>
    </ul>
</aside>

<!-- Script penanganan klik tombol close -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const sidebar = document.querySelector('.app-sidebar');
    const body = document.body;

    // Cari kontainer utama yang terdorong
    const mainContent = document.querySelector('.main-content, .app-content, .content-wrapper, main, .wrapper');

    // Fungsi Kusus Tombol CLOSE (X): Mengembalikan posisi kontainer ke awal secara mutlak
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // 1. Sembunyikan Sidebar
            if (sidebar) {
                sidebar.classList.remove('show', 'active', 'open');
            }

            // 2. Hapus class pendorong di body/wrapper
            body.classList.remove('sidebar-open', 'sidebar-mobile-open', 'toggled', 'sidebar-enable');

            // 3. Paksa reset margin/transform kontainer ke posisi semula (0)
            if (mainContent) {
                mainContent.style.marginLeft = '0px';
                mainContent.style.transform = 'none';
                mainContent.style.setProperty('margin-left', '0px', 'important');
                mainContent.style.setProperty('transform', 'none', 'important');
            }
        });
    }
});document.addEventListener('DOMContentLoaded', function () {
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const sidebar = document.querySelector('.app-sidebar');
    const body = document.body;

    // Cari kontainer utama yang terdorong
    const mainContent = document.querySelector('.main-content, .app-content, .content-wrapper, main, .wrapper');

    // Fungsi Kusus Tombol CLOSE (X): Mengembalikan posisi kontainer ke awal secara mutlak
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // 1. Sembunyikan Sidebar
            if (sidebar) {
                sidebar.classList.remove('show', 'active', 'open');
            }

            // 2. Hapus class pendorong di body/wrapper
            body.classList.remove('sidebar-open', 'sidebar-mobile-open', 'toggled', 'sidebar-enable');

            // 3. Paksa reset margin/transform kontainer ke posisi semula (0)
            if (mainContent) {
                mainContent.style.marginLeft = '0px';
                mainContent.style.transform = 'none';
                mainContent.style.setProperty('margin-left', '0px', 'important');
                mainContent.style.setProperty('transform', 'none', 'important');
            }
        });
    }
});
</script>