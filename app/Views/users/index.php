<div class="users-page">
    <div class="users-page-header">
        <div>
            <h1 class="users-page-title">Users</h1>
            <p class="users-page-subtitle">User Management and TKA Monev Staff</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus"></i>
            Add User
        </button>
    </div>

    <div class="card users-card">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-users"></i>
                Data User
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="userTable" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Institusi</th>
                            <th>Wilayah Verifikasi</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content user-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="fas fa-user-plus"></i>
                    Tambah User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="addUserAlert" class="modal-alert"></div>
            <form id="addUserForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addName" class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" id="addName" class="form-control" placeholder="Masukkan nama lengkap" autocomplete="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="addUsername" class="form-label">Username</label>
                        <input type="text" name="username" id="addUsername" class="form-control" placeholder="Masukkan username" autocomplete="username" required>
                    </div>

                    <!-- Perubahan: Tambahan Field Institusi & Region -->
                    <div class="mb-3">
                        <label for="addInstitution" class="form-label">Institusi / Instansi</label>
                        <input type="text" name="institution" id="addInstitution" class="form-control" placeholder="Masukkan nama instansi" required>
                    </div>

                    <div class="mb-3">
                        <label for="addRegionId" class="form-label"> Wilayah Verifikasi</label>
                        <select name="region_id" id="addRegionId" class="form-select" required>
                            <option value="">-- Pilih Wilayah Verifikasi --</option>
                            <?php if (!empty($regions)): ?>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region['id'] ?>"><?= esc($region['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="addPassword" class="form-label">Password</label>
                        <input type="password" name="password" id="addPassword" class="form-control" placeholder="Minimal 6 karakter" autocomplete="new-password" required>
                    </div>

                    <div class="mb-3">
                        <label for="addRole" class="form-label">Role</label>
                        <select name="role" id="addRole" class="form-select" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                        </select>
                    </div>

                    <div>
                        <label for="addStatus" class="form-label">Status</label>
                        <select name="is_active" id="addStatus" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered user-modal-dialog">
        <div class="modal-content user-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">
                    <i class="fas fa-user-edit"></i>
                    Edit User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div id="editUserAlert" class="modal-alert"></div>

            <form id="editUserForm">
                <?= csrf_field() ?>

                <input type="hidden" name="id" id="editUserId">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" id="editName" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username</label>
                        <input type="text" name="username" id="editUsername" class="form-control" placeholder="Masukkan username" autocomplete="username" required>
                    </div>

                    <!-- Perubahan: Tambahan Field Institusi & Region -->
                    <div class="mb-3">
                        <label for="editInstitution" class="form-label">Institusi / Instansi</label>
                        <input type="text" name="institution" id="editInstitution" class="form-control" placeholder="Masukkan nama instansi" required>
                    </div>

                    <div class="mb-3">
                        <label for="editRegionId" class="form-label">Wilayah Verifikasi</label>
                       <select name="region_id" id="editRegionId" class="form-select" required>
                            <option value="">-- Pilih Wilayah Verifikasi --</option>
                            <?php if (!empty($regions)): ?>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region['id'] ?>"><?= esc($region['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select name="role" id="editRole" class="form-select" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                        </select>
                    </div>

                    <div>
                        <label for="editStatus" class="form-label">Status</label>
                        <select name="is_active" id="editStatus" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered user-modal-dialog">
        <div class="modal-content user-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">
                    <i class="fas fa-key"></i>
                    Reset Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div id="resetPasswordAlert" class="modal-alert"></div>

            <form id="resetPasswordForm">
                <?= csrf_field() ?>

                <input type="hidden" name="id" id="resetPasswordId">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <div class="form-control bg-light" id="resetPasswordName"></div>
                    </div>

                    <div class="mb-3">
                        <label for="resetPassword" class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password" id="resetPassword" class="form-control" placeholder="Minimal 6 karakter" autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#resetPassword" title="Lihat password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="resetPasswordConfirm" class="form-label">Konfirmasi Password</label>
                        <div class="input-group">
                            <input type="password" id="resetPasswordConfirm" class="form-control" placeholder="Ulangi password baru" autocomplete="new-password" required>
                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#resetPasswordConfirm" title="Lihat password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Delete User -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content user-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i> Delete User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div id="deleteUserAlert" class="modal-alert px-3 pt-3 mb-0"></div>

            <div class="modal-body">
                <div class="user-delete-content text-center py-2">
                    <div class="user-delete-icon text-danger fs-1 mb-3">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <p class="mb-2">Are you sure you want to delete this user?</p>
                    <strong id="deleteUserName" class="fs-5 text-dark d-block mb-3">-</strong>
                    <input type="hidden" id="deleteUserId">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteUser">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>