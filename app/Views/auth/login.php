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
    <link rel="stylesheet" href="<?= base_url('assets/css/notification.css') ?>">
</head>
<body class="bg-light">

<!-- Tempat Kontainer Notifikasi Kustom -->
<div id="notification-container"></div>

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div id="loginAlert"></div>
    <div class="card card-login shadow-lg border-0 rounded-4" style="width: 100%; max-width: 400px;">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <div class="brand-icon mb-2">
                    <i class="fa-solid fa-graduation-cap fa-3x text-primary"></i>
                </div>
                <h4 class="fw-bold text-dark">Sistem Monev TKA</h4>
                <p class="text-muted small">Silakan login untuk mengakses aplikasi</p>
            </div>
            <form id="formLogin" action="<?= base_url('login') ?>" method="POST" autocomplete="off">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan Username" autocomplete="username" autofocus>
                    <div id="error-username" class="invalid-feedback"></div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan Password" autocomplete="current-password">
                    <div id="error-password" class="invalid-feedback"></div>
                </div>

                <button type="submit" id="btnLogin" class="btn btn-primary w-100 py-2">
                    <span id="btnText">Login</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- jQuery & SweetAlert2 CDN -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Custom JS Files -->
<script src="<?= base_url('assets/js/notification.js') ?>"></script>
<script src="<?= base_url('assets/js/auth.js') ?>"></script>
</body>
</html>