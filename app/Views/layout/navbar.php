<nav class="app-navbar">
    <div class="navbar-left">
        <button type="button" id="sidebarToggle" class="navbar-toggle" title="Buka/Tutup Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <div class="navbar-title">
            <strong>Monev TKA PROVINSI</strong>
            <span>Monitoring & Evaluasi</span>
        </div>
    </div>
    <div class="navbar-right">
        <button type="button" class="navbar-action" id="searchButton" title="Pencarian">
            <i class="fas fa-search"></i>
        </button>
        <div class="navbar-dropdown">
            <button type="button" id="notificationButton" class="navbar-action" title="Notifikasi">
                <i class="far fa-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            <div class="notification-menu" id="notificationMenu">
                <div class="dropdown-header">
                    Notifikasi
                </div>
                <div class="dropdown-item">
                    <i class="fas fa-school"></i>
                    <div>
                        <strong>Visitasi baru</strong>
                        <small>Ada sekolah yang perlu dimonev</small>
                    </div>
                </div>
                <div class="dropdown-item">
                    <i class="fas fa-file-alt"></i>
                    <div>
                        <strong>Instrumen</strong>
                        <small>Instrumen menunggu verifikasi</small>
                    </div>
                </div>
                <div class="dropdown-footer">
                    Lihat semua notifikasi
                </div>
            </div>
        </div>
        <div class="navbar-divider"></div>
        <div class="navbar-dropdown">
            <button type="button" id="profileButton" class="navbar-user" title="Profile">
                <div class="navbar-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="navbar-user-info">
                    <strong>Administrator</strong>
                    <small>Admin Monev</small>
                </div>
                <i class="fas fa-chevron-down navbar-chevron"></i>
            </button>
            <div class="user-menu" id="userMenu">
                <div class="user-menu-header">
                    <div class="navbar-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <strong>Administrator</strong>
                        <small>Admin Monev</small>
                    </div>
                </div>
                <a href="#">
                    <i class="fas fa-user"></i>
                    Profile
                </a>
                <a href="#">
                    <i class="fas fa-cog"></i>
                    Pengaturan
                </a>
                <div class="user-menu-divider"></div>
                <a href="#">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>