$(document).ready(function () {
    $('#formLogin').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const form = $(this);
        const btnLogin = $('#btnLogin');
        const btnText = $('#btnText');
        const btnSpinner = $('#btnSpinner');

        clearErrors();

        const username = $.trim($('#username').val());
        const password = $('#password').val();

        if (username === '') {
            showFieldError('username', 'Username tidak boleh kosong.');
            $('#username').focus();
            return false;
        }

        if (password === '') {
            showFieldError('password', 'Password tidak boleh kosong.');
            $('#password').focus();
            return false;
        }

        btnLogin.prop('disabled', true);
        btnText.text('Memproses...');
        btnSpinner.removeClass('d-none');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function (response) {
                if (response.status === 'success') {
                    if (typeof window.showGlobalAlert === 'function') {
                        window.showGlobalAlert(
                            response.message || 'Login berhasil!',
                            'success'
                        );
                    }

                    setTimeout(function () {
                        window.location.href = response.redirect;
                    }, 1200);

                    return;
                }

                if (response.status === 'validation_error') {
                    if (response.errors) {
                        if (response.errors.username) {
                            showFieldError(
                                'username',
                                response.errors.username
                            );
                        }

                        if (response.errors.password) {
                            showFieldError(
                                'password',
                                response.errors.password
                            );
                        }
                    }

                    return;
                }

                if (typeof window.showGlobalAlert === 'function') {
                    window.showGlobalAlert(
                        response.message || 'Username atau Password salah!',
                        'error'
                    );
                }
            },
            error: function (xhr) {
                let message = 'Terjadi kesalahan pada server. Silakan coba lagi.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                if (typeof window.showGlobalAlert === 'function') {
                    window.showGlobalAlert(message, 'error');
                }
            },
            complete: function () {
                btnLogin.prop('disabled', false);
                btnText.text('Login');
                btnSpinner.addClass('d-none');
            }
        });

        return false;
    });

    $('#username, #password').on('input', function () {
        const field = $(this).attr('id');

        $(this).removeClass('is-invalid');
        $('#error-' + field).text('');
    });

    function showFieldError(field, message) {
        $('#' + field).addClass('is-invalid');
        $('#error-' + field).text(message);
    }

    function clearErrors() {
        $('#username').removeClass('is-invalid');
        $('#password').removeClass('is-invalid');
        $('#error-username').text('');
        $('#error-password').text('');
    }
});