<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>
<div class="page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= $user ? 'Edit User' : 'Tambah User' ?></h1>
            <p class="page-subtitle"><?= $user ? 'Perbarui data pengguna' : 'Tambahkan pengguna baru ke sistem Monev TKA' ?></p>
        </div>
        <a href="<?= base_url('users') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="form-card">
        <form method="post" action="<?= $user ? base_url('users/update/'.$user['id']) : base_url('users/store') ?>">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nama Lengkap <span>*</span></label>
                    <input type="text" id="name" name="name" value="<?= old('name',$user['name'] ?? '') ?>" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label for="email">Email <span>*</span></label>
                    <input type="email" id="email" name="email" value="<?= old('email',$user['email'] ?? '') ?>" placeholder="Masukkan email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password <?= $user ? '' : '<span>*</span>' ?></label>
                    <input type="password" id="password" name="password" placeholder="<?= $user ? 'Kosongkan jika tidak diubah' : 'Masukkan password' ?>" <?= $user ? '' : 'required' ?>>
                    <?php if ($user): ?>
                        <small>Kosongkan password jika tidak ingin mengubahnya.</small>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="role">Role <span>*</span></label>
                    <select id="role" name="role" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" <?= old('role',$user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="petugas" <?= old('role',$user['role'] ?? '') === 'petugas' ? 'selected' : '' ?>>Petugas Monev</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="is_active">Status <span>*</span></label>
                    <select id="is_active" name="is_active" required>
                        <option value="1" <?= old('is_active',$user['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= old('is_active',$user['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <a href="<?= base_url('users') ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    <?= $user ? 'Simpan Perubahan' : 'Simpan User' ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>