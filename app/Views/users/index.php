
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
                        <label class="form-label">
                            User
                        </label>
                        <div class="form-control bg-light" id="resetPasswordName"></div>
                    </div>

                    <div class="mb-3">
                        <label for="resetPassword" class="form-label">
                            Password Baru
                        </label>

                        <div class="input-group">
                            <input type="password" name="password" id="resetPassword" class="form-control" placeholder="Minimal 6 karakter" autocomplete="new-password" required>

                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#resetPassword" title="Lihat password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="resetPasswordConfirm" class="form-label">
                            Konfirmasi Password
                        </label>

                        <div class="input-group">
                            <input type="password" id="resetPasswordConfirm" class="form-control" placeholder="Ulangi password baru" autocomplete="new-password" required>

                            <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#resetPasswordConfirm" title="Lihat password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
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
                        <label for="editName" class="form-label">
                            Nama Lengkap
                        </label>
                        <input type="text" name="name" id="editName" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label for="editUsername" class="form-label">
                            Username
                        </label>
                        <input type="text" name="username" id="editUsername" class="form-control" placeholder="Masukkan username" autocomplete="username" required>
                    </div>

                    <div class="mb-3">
                        <label for="editRole" class="form-label">
                            Role
                        </label>
                        <select name="role" id="editRole" class="form-select" required>
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                        </select>
                    </div>

                    <div>
                        <label for="editStatus" class="form-label">
                            Status
                        </label>
                        <select name="is_active" id="editStatus" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered user-modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteUserModalLabel">
                    Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="deleteUserAlert" class="modal-alert"></div>
            <div class="modal-body text-center py-4">
                <div class="text-danger mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x"></i>
                </div>
                <p class="mb-1">
                    Are you sure you want to delete this user?
                </p>
                <p class="text-muted small mb-2">
                    This action cannot be undone.
                </p>
                <strong id="deleteUserName" class="d-block"></strong>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    Cancel
                </button>

                <button type="button" id="confirmDeleteUser" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
