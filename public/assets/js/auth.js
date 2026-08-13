$.ajaxSetup({
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
});

$(document).ready(function () {
    $('#formLogin').on('submit', function (e) {
        e.preventDefault(); // MENCEGAH browser reload/membuka halaman JSON mentah

        const form = $(this);
        const url  = form.attr('action');
        const data = form.serialize();

        // Reset error state
        $('.invalid-feedback').text('');
        $('.form-control').removeClass('is-invalid');

        // Loading state
        $('#btnLogin').prop('disabled', true);
        $('#btnText').addClass('d-none');
        $('#btnSpinner').removeClass('d-none');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'JSON',
            success: function (response) {
                if (response.status === 'validation_error') {
                    $.each(response.errors, function (field, message) {
                        $('#' + field).addClass('is-invalid');
                        $('#error-' + field).text(message);
                    });
                } else if (response.status === 'error') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Login',
                        text: response.message,
                        confirmButtonColor: '#d33'
                    });
                } else if (response.status === 'success') {
                    // Tampilkan Notifikasi Pop-up
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Berhasil',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(function () {
                        // Baru lakukan redirect setelah alert selesai
                        window.location.href = response.redirect;
                    });
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Terjadi kesalahan (' + xhr.status + '). Silakan coba lagi.',
                    confirmButtonColor: '#d33'
                });
            },
            complete: function () {
                $('#btnLogin').prop('disabled', false);
                $('#btnText').removeClass('d-none');
                $('#btnSpinner').addClass('d-none');
            }
        });
    });
});