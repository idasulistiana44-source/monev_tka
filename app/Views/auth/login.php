<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Login - Sistem Monev TKA' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Auth CSS -->
    <link rel="stylesheet" href="<?= base_url('css/auth.css') ?>">
    <!-- Custom Notification CSS -->
    <link rel="stylesheet" href="<?= base_url('css/notification.css') ?>">
</head>
<body class="bg-light">

<!-- Tempat Kontainer Notifikasi (opsional jika notification.js membutuhkannya) -->
<div id="notification-container"></div>

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card card-login shadow-lg border-0 rounded-4" style="width: 100%; max-width: 400px;">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <div class="brand-icon mb-2">
                    <i class="fa-solid fa-graduation-cap fa-3x text-primary"></i>
                </div>
                <h4 class="fw-bold text-dark">Sistem Monev TKA</h4>
                <p class="text-muted small">Silakan login untuk mengakses aplikasi</p>
            </div>

            <form id="formLogin" method="POST" action="<?= base_url('login/process') ?>">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="login" class="form-label text-secondary small fw-bold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user"></i></span>
                        <input type="text" class="form-control" id="login" name="login" placeholder="Masukkan username" autocomplete="off">
                    </div>
                    <div class="invalid-feedback d-block" id="error-login"></div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label text-secondary small fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password">
                    </div>
                    <div class="invalid-feedback d-block" id="error-password"></div>
                </div>

                <button type="submit" id="btnLogin" class="btn btn-primary w-100 py-2 fw-bold text-uppercase">
                    <span id="btnText"><i class="fa-solid fa-right-to-bracket me-2"></i> Masuk</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- jQuery & SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom JS Files -->
<script src="<?= base_url('js/notification.js') ?>"></script>
<script src="<?= base_url('js/auth.js') ?>"></script>
</body>
</html>