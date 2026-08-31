if (typeof BASE_URL === 'undefined') window.BASE_URL = '/';
if (typeof CSRF_TOKEN_NAME === 'undefined') window.CSRF_TOKEN_NAME = 'csrf_test_name';
if (typeof CSRF_HASH === 'undefined') window.CSRF_HASH = '';

let users = [];
let deleteUserId = null;
function initRegionSelect() {
    if (!$.fn.select2) {
        return;
    }
    $('#addRegionId').select2({
        width: '100%',
        placeholder: 'Pilih wilayah verifikasi...',
        allowClear: true,
        closeOnSelect: false,
        dropdownParent: $('#addUserModal')
    });
    $('#editRegionId').select2({
        width: '100%',
        placeholder: 'Pilih wilayah verifikasi...',
        allowClear: true,
        closeOnSelect: false,
        dropdownParent: $('#editUserModal')
    });
}

$(document).ready(function () {
    initRegionSelect();
    function getCsrfData() {
        const token = $('input[name="' + CSRF_TOKEN_NAME + '"]').first();
        if (token.length) {
            return {
                [CSRF_TOKEN_NAME]: token.val()
            };
        }
        return {
            [CSRF_TOKEN_NAME]: CSRF_HASH
        };
    }

    function updateCsrf(response) {
        if (response && response.csrfHash) {
            $('input[name="' + CSRF_TOKEN_NAME + '"]').val(response.csrfHash);
        }
    }

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function escapeAttribute(value) {
        return $('<div>').text(value ?? '').html().replace(/"/g, '&quot;');
    }

    function showGlobalAlert(type, message) {
        let alert = $('#globalUserAlert');
        if (!alert.length) {
            alert = $('<div id="globalUserAlert"></div>');
            $('body').append(alert);
        }
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        alert.removeClass('success error warning');
        alert.addClass(type);
        alert.html('<i class="fas ' + icon + '"></i><span>' + escapeHtml(message) + '</span>');
        alert.stop(true, true).fadeIn(200);
        clearTimeout(alert.data('timer'));
        const timer = setTimeout(function () {
            alert.fadeOut(300);
        }, 3000);

        alert.data('timer', timer);
    }

    function showModalAlert(selector, type, message) {
        const alert = $(selector);
        if (!alert.length) {
            return;
        }
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        alert.removeClass('success error warning');
        alert.addClass(type);
        alert.html('<i class="fas ' + icon + '"></i><span>' + escapeHtml(message) + '</span>');
        alert.addClass('show');
    }

    function hideModalAlert(selector) {
        $(selector).removeClass('show success error warning').html('');
    }

    function loadRegions(selectedEditIds = []) {

        return $.ajax({
            url: BASE_URL + 'regions/data',
            type: 'GET',
            dataType: 'json',

            success: function (response) {

                updateCsrf(response);

                if (response && response.success) {

                    let options = '';

                    $.each(response.data || [], function (index, region) {

                        options += `
                            <option value="${escapeAttribute(region.id)}">
                                ${escapeHtml(region.name)}
                            </option>
                        `;

                    });

                    // ADD
                    $('#addRegionId')
                        .html(options)
                        .val(null)
                        .trigger('change');

                    // EDIT
                    $('#editRegionId')
                        .html(options)
                        .val(null)
                        .trigger('change');

                    // Pastikan array
                    if (!Array.isArray(selectedEditIds)) {

                        selectedEditIds = selectedEditIds
                            ? [selectedEditIds]
                            : [];

                    }

                    // Pilih wilayah milik user
                    if (selectedEditIds.length) {

                        $('#editRegionId')
                            .val(selectedEditIds.map(String))
                            .trigger('change');

                    }
                }
            },

            error: function (xhr) {

                console.error(
                    'Gagal memuat daftar wilayah:',
                    xhr.responseText || xhr
                );

            }
        });
    }

    $(document).on('click', '.status-user-btn', function (e) {
        e.preventDefault();
        const button = $(this);
        const id = Number(button.data('id'));
        const user = users.find(function (item) {
            return Number(item.id) === id;
        });

        if (!user) {
            showGlobalAlert('error', 'Data user tidak ditemukan.');
            return;
        }

        button.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'users/toggleStatus',
            type: 'POST',
            data: $.extend(
                {},
                getCsrfData(),
                { id: id }
            ),
            dataType: 'json',
            success: function (response) {
                updateCsrf(response);

                if (response && response.success) {
                    showGlobalAlert(
                        'success',
                        response.message || 'Status user berhasil diperbarui.'
                    );
                    loadUsers();
                } else {
                    const message = getErrorMessage(response, 'Gagal mengubah status user.');
                    showGlobalAlert('error', message);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                updateCsrf(response);

                const message = getErrorMessage(response, 'Terjadi kesalahan saat mengubah status user.');
                showGlobalAlert('error', message);
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });

    function getErrorMessage(response, fallback) {
        if (!response) {
            return fallback;
        }
        if (response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
            const errors = [];
            $.each(response.data, function (key, value) {
                if (value && value !== response.message) {
                    errors.push(value);
                }
            });
            if (errors.length) {
                return errors.join(' ');
            }
        }

        if (response.message) {
            return response.message;
        }
        return fallback;
    }

    function openModal(id) {
        const element = document.getElementById(id);
        if (!element) {
            console.error('Modal tidak ditemukan:', id);
            return;
        }
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap belum dimuat.');
            return;
        }
        const modal = bootstrap.Modal.getOrCreateInstance(element);
        modal.show();
    }

    function closeModal(id) {
        const element = document.getElementById(id);
        if (!element || typeof bootstrap === 'undefined') {
            return;
        }
        const modal = bootstrap.Modal.getInstance(element);
        if (modal) {
            modal.hide();
        }
    }

    function renderUsers() {
        if ($.fn.DataTable.isDataTable('#userTable')) {
            $('#userTable').DataTable().clear().destroy();
        }
        const tbody = $('#userTable tbody');
        tbody.empty();
        if (!users.length) {
            tbody.html(`
                <tr>
                    <td colspan="8" class="text-center">
                        Belum ada data user.
                    </td>
                </tr>
            `);
        } else {
            $.each(users, function (index, user) {
                const active = Number(user.is_active) === 1;
                const role = String(user.role || '').toLowerCase();
                const roleText = role === 'admin' ? 'Admin' : 'Petugas';
                const roleClass = role === 'admin' ? 'bg-primary' : 'bg-info';
                const statusText = active ? 'Active' : 'Inactive';
                const statusClass = active ? 'bg-success' : 'bg-secondary';
                const statusIcon = active ? 'fa-user-slash' : 'fa-user-check';
                const statusButtonClass = active ? 'btn-outline-success' : 'btn-outline-secondary';
                const institutionText = user.institution ? escapeHtml(user.institution) : '-';
                const regionName = user.region_name ? escapeHtml(user.region_name) : '-';

                tbody.append(`
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>
                            <div class="user-name-cell">
                                <div class="user-table-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <strong>${escapeHtml(user.name)}</strong>
                            </div>
                        </td>
                        <td>${escapeHtml(user.username)}</td>
                        <td>${institutionText}</td>
                        <td>${regionName}</td>
                        <td>
                            <span class="badge ${roleClass}">
                                ${roleText}
                            </span>
                        </td>
                        <td>
                            <span class="badge ${statusClass}">
                                ${statusText}
                            </span>
                        </td>
                        <td>
                            <div class="user-action-buttons">
                                <button type="button" class="btn btn-outline-primary edit-user-btn" data-id="${escapeAttribute(user.id)}" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-warning reset-password-btn" data-id="${escapeAttribute(user.id)}" title="Reset Password">
                                    <i class="fas fa-key"></i>
                                </button>
                               <button type="button" class="btn ${statusButtonClass} status-user-btn" data-id="${escapeAttribute(user.id)}" data-status="${active ? 1 : 0}" title="${active ? 'Nonaktifkan User' : 'Aktifkan User'}">
                                    <i class="fas ${statusIcon}"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger delete-user-btn" data-id="${escapeAttribute(user.id)}" title="Hapus User">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
            });
        }

        $('#userTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            autoWidth: false,
            responsive: false,
            language: {
                search: 'Search:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                zeroRecords: 'No matching records found',
                emptyTable: 'Belum ada data user.',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: '›',
                    previous: '‹'
                }
            },
            columnDefs: [
                {
                    targets: 0,
                    width: '55px',
                    orderable: false
                },
                {
                    targets: 7, 
                    width: '155px',
                    orderable: false
                }
            ]
        });
    }

    function loadUsers() {
        $('#userTable tbody').html(`
            <tr>
                <td colspan="10" class="school-loading"> 
                    <div class="school-spinner"></div>Loading data users...
                </td>
            </tr>
        `);

        $.ajax({
            url: BASE_URL + 'users/data',
            type: 'GET',
            dataType: 'json',

            success: function (response) {
                updateCsrf(response);

                if (!response || !response.success) {
                    showGlobalAlert(
                        'error',
                        getErrorMessage(response, 'Gagal memuat data user.')
                    );
                    return;
                }

                users = response.data || [];
                renderUsers();
            },

            error: function (xhr) {
                const response = xhr.responseJSON;
                updateCsrf(response);

                showGlobalAlert(
                    'error',
                    getErrorMessage(response, 'Gagal memuat data user.')
                );
            }
        });
    }

    $('#addUserModal').on('show.bs.modal', function () {
        hideModalAlert('#addUserAlert');
    });

    $('#editUserModal').on('show.bs.modal', function () {
        hideModalAlert('#editUserAlert');
    });

    $('#resetPasswordModal').on('show.bs.modal', function () {
        hideModalAlert('#resetPasswordAlert');
    });

    $('#statusUserModal').on('show.bs.modal', function () {
        hideModalAlert('#statusUserAlert');
    });

    $('#deleteUserModal').on('show.bs.modal', function () {
        hideModalAlert('#deleteUserAlert');
    });

    $('#addUserModal').on('hidden.bs.modal', function () {
        const form = $('#addUserForm')[0];
        if (form) {
            form.reset();
        }
        hideModalAlert('#addUserAlert');
    });

    $('#editUserModal').on('hidden.bs.modal', function () {
        hideModalAlert('#editUserAlert');
    });

    $('#resetPasswordModal').on('hidden.bs.modal', function () {
        const form = $('#resetPasswordForm')[0];
        if (form) {
            form.reset();
        }
        hideModalAlert('#resetPasswordAlert');
    });

    $('#deleteUserModal').on('hidden.bs.modal', function () {
        hideModalAlert('#deleteUserAlert');
        deleteUserId = null;
    });

    $(document).on('click', '.edit-user-btn', function (e) {
        e.preventDefault();
        const id = Number($(this).data('id'));
        const user = users.find(function (item) {
            return Number(item.id) === id;
        });

        if (!user) {
            showGlobalAlert('error', 'Data user tidak ditemukan.');
            return;
        }

        hideModalAlert('#editUserAlert');
        $('#editUserId').val(user.id);
        $('#editName').val(user.name);
        $('#editUsername').val(user.username);
        $('#editRole').val(user.role);
        $('#editStatus').val(String(user.is_active));
        $('#editInstitution').val(user.institution || '');

            let targetRegionIds = [];

        if (Array.isArray(user.region_ids)) {
            targetRegionIds = user.region_ids.map(String);
        } else if (
            user.region_id !== null &&
            user.region_id !== undefined &&
            user.region_id !== ''
        ) {
            targetRegionIds = [String(user.region_id)];
        }

        $('#editRegionId')
            .val(targetRegionIds)
            .trigger('change');
            openModal('editUserModal');
    });

    $(document).on('click', '.reset-password-btn', function (e) {
        e.preventDefault();
        const id = Number($(this).data('id'));
        const user = users.find(function (item) {
            return Number(item.id) === id;
        });

        if (!user) {
            showGlobalAlert('error', 'Data user tidak ditemukan.');
            return;
        }

        $('#resetPasswordId').val(user.id);
        $('#resetPasswordName').text(user.name);
        $('#resetPassword').val('');
        hideModalAlert('#resetPasswordAlert');
        openModal('resetPasswordModal');
    });


    $(document).on('click', '.delete-user-btn', function (e) {
        e.preventDefault();
        const id = Number($(this).data('id'));
        const user = users.find(function (item) {
            return Number(item.id) === id;
        });

        if (!user) {
            showGlobalAlert('error', 'Data user tidak ditemukan.');
            return;
        }

        deleteUserId = id;
        $('#deleteUserId').val(user.id);
        $('#deleteUserName').text(user.name);
        hideModalAlert('#deleteUserAlert');
        openModal('deleteUserModal');
    });


    $('#confirmDeleteUser').on('click', function (e) {
        e.preventDefault();
        const button = $(this);
        const idToDelete = $('#deleteUserId').val() || deleteUserId;

        if (!idToDelete) {
            showGlobalAlert('error', 'User tidak ditemukan.');
            return;
        }

        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');

        $.ajax({
            url: BASE_URL + 'users/delete',
            type: 'POST',
            data: $.extend({}, getCsrfData(), { id: idToDelete }),
            dataType: 'json',
            success: function (response) {
                updateCsrf(response);

                if (response && response.success) {
                    closeModal('deleteUserModal');
                    showGlobalAlert('success', response.message || 'User berhasil dihapus.');
                    loadUsers();
                } else {
                    const message = getErrorMessage(response, 'Gagal menghapus user.');
                    showModalAlert('#deleteUserAlert', 'error', message);
                    showGlobalAlert('error', message);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                updateCsrf(response);

                const message = getErrorMessage(response, 'Terjadi kesalahan saat menghapus user.');
                showModalAlert('#deleteUserAlert', 'error', message);
                showGlobalAlert('error', message);
            },
            complete: function () {
                button.prop('disabled', false).html('<i class="fas fa-trash me-1"></i> Delete');
            }
        });
    });

    $('#addUserForm').on('submit', function (e) {
        e.preventDefault();

        const form = this;
        const button = $(form).find('button[type="submit"]');
        hideModalAlert('#addUserAlert');
        button.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'users/store',
            type: 'POST',
            data: $(form).serialize(),
            dataType: 'json',
            success: function (response) {
                updateCsrf(response);

                if (response && response.success) {
                    closeModal('addUserModal');
                    showGlobalAlert('success', response.message || 'User berhasil ditambahkan.');
                    loadUsers();
                } else {
                    const message = getErrorMessage(response, 'Periksa kembali data yang diisi.');
                    showModalAlert('#addUserAlert', 'error', message);
                    showGlobalAlert('error', message);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                updateCsrf(response);
                const message = getErrorMessage(response, 'Terjadi kesalahan saat menyimpan user.');
                showModalAlert('#addUserAlert', 'error', message);
                showGlobalAlert('error', message);
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });

    $('#editUserForm').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const button = $(form).find('button[type="submit"]');
        hideModalAlert('#editUserAlert');
        button.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'users/update',
            type: 'POST',
            data: $(form).serialize(),
            dataType: 'json',
            success: function (response) {
                updateCsrf(response);

                if (response && response.success) {
                    closeModal('editUserModal');
                    showGlobalAlert('success', response.message || 'User berhasil diperbarui.');
                    loadUsers();
                } else {
                    const message = getErrorMessage(response, 'Periksa kembali data yang diisi.');
                    showModalAlert('#editUserAlert', 'error', message);
                    showGlobalAlert('error', message);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                updateCsrf(response);
                const message = getErrorMessage(response, 'Terjadi kesalahan saat memperbarui user.');
                showModalAlert('#editUserAlert', 'error', message);
                showGlobalAlert('error', message);
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });

    $('#resetPasswordForm').on('submit', function (e) {
        e.preventDefault();

        const form = this;
        const button = $(form).find('button[type="submit"]');
        const password = $('#resetPassword').val();
        const confirmPassword = $('#resetPasswordConfirm').val();

        hideModalAlert('#resetPasswordAlert');

        if (password === '') {
            showModalAlert('#resetPasswordAlert', 'error', 'Password baru wajib diisi.');
            showGlobalAlert('error', 'Password baru wajib diisi.');
            return;
        }

        if (password.length < 6) {
            showModalAlert('#resetPasswordAlert', 'error', 'Password minimal 6 karakter.');
            showGlobalAlert('error', 'Password minimal 6 karakter.');
            return;
        }

        if (confirmPassword === '') {
            showModalAlert('#resetPasswordAlert', 'error', 'Konfirmasi password wajib diisi.');
            showGlobalAlert('error', 'Konfirmasi password wajib diisi.');
            return;
        }

        if (password !== confirmPassword) {
            showModalAlert('#resetPasswordAlert', 'error', 'Konfirmasi password tidak sesuai.');
            showGlobalAlert('error', 'Konfirmasi password tidak sesuai.');
            return;
        }

        button.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'users/resetPassword',
            type: 'POST',
            data: $(form).serialize(),
            dataType: 'json',
            success: function (response) {
                updateCsrf(response);

                if (response && response.success) {
                    closeModal('resetPasswordModal');
                    form.reset();
                    showGlobalAlert('success', response.message || 'Password berhasil direset.');
                    loadUsers();
                } else {
                    const message = getErrorMessage(response, 'Password gagal direset.');
                    showModalAlert('#resetPasswordAlert', 'error', message);
                    showGlobalAlert('error', message);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                updateCsrf(response);
                const message = getErrorMessage(response, 'Terjadi kesalahan saat mereset password.');
                showModalAlert('#resetPasswordAlert', 'error', message);
                showGlobalAlert('error', message);
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });

    $('#confirmDeleteUser').on('click', function (e) {
        e.preventDefault();

        const button = $(this);

        if (!deleteUserId) {
            showGlobalAlert('error', 'User tidak ditemukan.');
            return;
        }

        button.prop('disabled', true);

        $.ajax({
            url: BASE_URL + 'users/delete',
            type: 'POST',
            data: $.extend(
                {},
                getCsrfData(),
                { id: deleteUserId }
            ),
            dataType: 'json',
            success: function (response) {
                updateCsrf(response);

                if (response && response.success) {
                    closeModal('deleteUserModal');
                    showGlobalAlert('success', response.message || 'User berhasil dihapus.');
                    loadUsers();
                } else {
                    const message = getErrorMessage(response, 'Gagal menghapus user.');
                    showModalAlert('#deleteUserAlert', 'error', message);
                    showGlobalAlert('error', message);
                }
            },
            error: function (xhr) {
                const response = xhr.responseJSON;
                updateCsrf(response);
                const message = getErrorMessage(response, 'Terjadi kesalahan saat menghapus user.');
                showModalAlert('#deleteUserAlert', 'error', message);
                showGlobalAlert('error', message);
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });

    loadRegions();
    loadUsers();

    $(document).on('click', '.toggle-password', function () {
        const button = $(this);
        const target = $(button.data('target'));
        const icon = button.find('i');

        if (target.attr('type') === 'password') {
            target.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
            button.attr('title', 'Sembunyikan password');
        } else {
            target.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
            button.attr('title', 'Lihat password');
        }
    });

});