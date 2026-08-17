(function () {
    'use strict';

    const BASE_URL = window.VISITS_FORM_BASE_URL || window.baseUrl || (window.location.origin + '/');
    const VISIT_ID = window.VISIT_ID;
    let sections = [];
    let saving = false;

    function ajax(url, options) {
        options = options || {};
        const data = options.data || {};

        if (window.VISITS_FORM_CSRF_NAME && window.VISITS_FORM_CSRF_HASH) {
            data[window.VISITS_FORM_CSRF_NAME] = window.VISITS_FORM_CSRF_HASH;
        }

        return $.ajax({
            url: BASE_URL + url.replace(/^\/+/, ''),
            type: options.type || 'GET',
            data: data,
            dataType: 'json'
        }).done(function (res) {
            if (res && res.csrf_hash) {
                window.VISITS_FORM_CSRF_HASH = res.csrf_hash;
            }
            if (res && res.token) {
                window.VISITS_FORM_CSRF_HASH = res.token;
            }
        });
    }

    function loadForm() {
        $('#instrumentContainer').html('<div class="visit-form-loading p-4 text-center"><i class="fas fa-spinner fa-spin me-2"></i>Memuat instrumen...</div>');
        
        ajax('visits/instruments/' + VISIT_ID).done(function (res) {
            if (!res || res.status === false) {
                showError(res && res.message ? res.message : 'Instrumen gagal dimuat.');
                return;
            }
            sections = res.sections || [];
            renderSections();
            updateProgress();
            if (res.visit && res.visit.status) {
                updateStatus(res.visit.status);
            }
        }).fail(function (xhr) {
            handleAjaxError(xhr, 'Gagal memuat instrumen.');
        });
    }

    function renderSections() {
        if (!sections.length) {
            $('#instrumentContainer').html('<div class="visit-no-instrument p-4 text-center"><i class="fas fa-clipboard-list fa-2x mb-2 text-muted"></i><h5>Belum ada instrumen aktif</h5><p class="text-muted">Instrumen visitasi belum tersedia.</p></div>');
            return;
        }

        let html = '';
        $.each(sections, function (sectionIndex, section) {
            html += '<div class="instrument-section mb-4">';
            html += '<div class="instrument-section-header card-header bg-light mb-3 p-3">';
            html += '<div class="d-flex align-items-center">';
            html += '<div class="instrument-section-number badge bg-primary me-2 fs-6">' + (sectionIndex + 1) + '</div>';
            html += '<div><h4 class="mb-0">' + escapeHtml(section.name || '-') + '</h4>';
            if (section.description) {
                html += '<small class="text-muted">' + escapeHtml(section.description) + '</small>';
            }
            html += '</div></div></div>';
            html += '<div class="instrument-section-body">';
            
            $.each(section.instruments || [], function (index, instrument) {
                html += renderInstrument(instrument);
            });
            
            html += '</div></div>';
        });

        $('#instrumentContainer').html(html);
        bindAnswerEvents();
        updateProgress();
    }

    function renderInstrument(item) {
        const id = escapeAttr(item.id);
        const isReq = (item.required == 1 || item.required === true || item.is_required == 1 || item.is_required === true);
        const answer = item.answer == null ? '' : item.answer;

        let html = '<div class="instrument-item card mb-3 p-3" data-instrument="' + id + '" data-required="' + (isReq ? '1' : '0') + '">';
        html += '<div class="instrument-question d-flex justify-content-between align-items-center mb-2">';
        html += '<span class="instrument-code fw-bold">' + escapeHtml(item.code || '') + '</span>';
        if (isReq) {
            html += '<span class="instrument-required text-danger fw-bold small">*Wajib</span>';
        }
        html += '</div>';
        html += '<div class="instrument-question-text mb-2">' + escapeHtml(item.question || '') + '</div>';
        if (item.description) {
            html += '<div class="instrument-description text-muted small mb-2">' + escapeHtml(item.description) + '</div>';
        }
        html += renderInput(item, answer);
        html += '</div>';

        return html;
    }

    function renderInput(item, answer) {
        const id = escapeAttr(item.id);
        const name = 'instrument_' + id;
        const type = item.answer_type || 'text';
        let html = '';

        if (type === 'textarea') {

            html = '<textarea class="form-control instrument-answer" ' +
                'data-id="' + id + '" rows="4">' +
                escapeHtml(answer) +
                '</textarea>';

        } else if (type === 'number') {

            html = '<input type="number" class="form-control instrument-answer" ' +
                'data-id="' + id + '" ' +
                'value="' + escapeAttr(answer) + '">';

        } else if (type === 'date') {

            html = '<input type="date" class="form-control instrument-answer" ' +
                'data-id="' + id + '" ' +
                'value="' + escapeAttr(answer) + '">';

        } else if (type === 'yesno') {

            html += '<div class="instrument-options d-flex gap-3">';

            html += '<label class="form-check-label">' +
                '<input type="radio" name="' + name + '" ' +
                'class="form-check-input instrument-answer" ' +
                'data-id="' + id + '" value="Ya" ' +
                (answer === 'Ya' ? 'checked' : '') +
                '> <span>Ya</span></label>';

            html += '<label class="form-check-label">' +
                '<input type="radio" name="' + name + '" ' +
                'class="form-check-input instrument-answer" ' +
                'data-id="' + id + '" value="Tidak" ' +
                (answer === 'Tidak' ? 'checked' : '') +
                '> <span>Tidak</span></label>';

            html += '</div>';

        } else if (type === 'radio') {

            html += '<div class="instrument-options d-flex flex-column gap-2">';

            $.each(item.options || [], function (index, option) {
                html += '<label class="form-check-label">' +
                    '<input type="radio" name="' + name + '" ' +
                    'class="form-check-input instrument-answer" ' +
                    'data-id="' + id + '" ' +
                    'value="' + escapeAttr(option) + '" ' +
                    (String(answer) === String(option) ? 'checked' : '') +
                    '> <span>' + escapeHtml(option) + '</span></label>';
            });

            html += '</div>';

        } else if (type === 'checkbox') {

            let values = [];

            if (Array.isArray(answer)) {
                values = answer;
            } else if (typeof answer === 'string' && answer.trim() !== '') {
                try {
                    values = JSON.parse(answer);

                    if (!Array.isArray(values)) {
                        values = [answer];
                    }

                } catch (e) {
                    values = [answer];
                }
            }

            html += '<div class="instrument-options d-flex flex-column gap-2">';

            $.each(item.options || [], function (index, option) {
                html += '<label class="form-check-label">' +
                    '<input type="checkbox" ' +
                    'class="form-check-input instrument-answer-checkbox" ' +
                    'data-id="' + id + '" ' +
                    'value="' + escapeAttr(option) + '" ' +
                    (values.indexOf(option) !== -1 ? 'checked' : '') +
                    '> <span>' + escapeHtml(option) + '</span></label>';
            });

            html += '</div>';

            } else if (type === 'select') {

                html = '<select class="form-select instrument-answer" data-id="' + id + '">' +
                    '<option value="">Pilih jawaban</option>';

                $.each(item.options || [], function (index, option) {
                    html += '<option value="' + escapeAttr(option) + '" ' +
                        (String(answer) === String(option) ? 'selected' : '') +
                        '>' + escapeHtml(option) + '</option>';
                });

                html += '</select>';

            } else if (type === 'pdf') {
                    html='<input type="file" class="form-control instrument-answer" data-id="'+id+'" accept=".pdf,application/pdf">';
                    html+='<div class="mt-2 pdf-preview" style="display:none;">';
                    html+='<div class="d-flex align-items-center gap-2 mb-2">';
                    html+='<i class="fas fa-file-pdf text-danger"></i>';
                    html+='<span class="pdf-name small text-muted"></span>';
                    html+='</div>';
                    html+='<iframe class="pdf-preview-frame" style="width:100%;height:400px;border:1px solid #ddd;border-radius:8px;display:none;"></iframe>';
                    html+='</div>';
                    if(answer){
                        html+='<div class="mt-2 uploaded-file">';
                        html+='<a href="'+escapeAttr(answer)+'" target="_blank" class="btn btn-sm btn-outline-danger">';
                        html+='<i class="fas fa-file-pdf me-1"></i>Lihat Berkas PDF';
                        html+='</a>';
                        html+='</div>';
                    }

            } else if (type === 'photo') {
                    html='<input type="file" class="form-control instrument-answer" data-id="'+id+'" accept=".jpg,.jpeg,.png,image/jpeg,image/png">';
                    html+='<div class="mt-2 photo-preview" style="display:none;">';
                    html+='<img class="photo-preview-img" src="" alt="Preview Foto" style="width:220px;height:160px;object-fit:cover;border-radius:8px;border:1px solid #ddd;">';
                    html+='</div>';
                    if(answer){
                        html+='<div class="mt-2 uploaded-photo">';
                        html+='<a href="'+escapeAttr(answer)+'" target="_blank" title="Klik untuk melihat foto ukuran penuh">';
                        html+='<img src="'+escapeAttr(answer)+'" alt="Foto dokumentasi" style="width:220px;height:160px;object-fit:cover;border-radius:8px;border:1px solid #ddd;cursor:pointer;">';
                        html+='</a>';
                        html+='</div>';
            }

            } else {

                html = '<input type="text" class="form-control instrument-answer" ' +
                    'data-id="' + id + '" ' +
                    'value="' + escapeAttr(answer) + '">';
            }

        return html;
    }

    function bindAnswerEvents() {
        $('.instrument-answer, .instrument-answer-checkbox')
            .on('change input', updateProgress);
        $(document).off('change.instrumentFile','.instrument-answer[type="file"]').on('change.instrumentFile','.instrument-answer[type="file"]',function(){
            const input=this;
            const file=input.files&&input.files[0];
            if(!file){
                return;
            }
            const accept=$(input).attr('accept')||'';
            const isPdf=accept.includes('.pdf');
            const maxSize=isPdf?5*1024*1024:3*1024*1024;
            const maxMb=isPdf?5:3;
            if(file.size>maxSize){
                showError('Ukuran file terlalu besar. Maksimal '+maxMb+' MB.');
                $(input).val('');
                updateProgress();
                return;
            }
            if(isPdf){
                if(file.type!=='application/pdf'){
                    showError('File harus berupa PDF.');
                    $(input).val('');
                    updateProgress();
                    return;
                }

                const item=$(input).closest('.instrument-item');

                // Hapus PDF lama
                item.find('.uploaded-file').remove();

                const url=URL.createObjectURL(file);

                item.find('.pdf-name').text(file.name);
                item.find('.pdf-preview-frame')
                    .attr('src',url)
                    .show();

                item.find('.pdf-preview').show();
            }else{
                if(!file.type.match(/^image\/(jpeg|png)$/)){
                    showError('File harus berupa JPG, JPEG, atau PNG.');
                    $(input).val('');
                    updateProgress();
                    return;
                }

            const item=$(input).closest('.instrument-item');
            item.find('.uploaded-photo').remove();

            const reader=new FileReader();

            reader.onload=function(e){
                item.find('.photo-preview-img')
                    .attr('src',e.target.result)
                    .show();

                item.find('.photo-preview').show();
            };

            reader.readAsDataURL(file);
            }
            updateProgress();
        });

        $(document).off('keydown', 'input[type="number"]')
            .on('keydown', 'input[type="number"]', function (e) {

                if ([
                    'e',
                    'E',
                    '-',
                    '+',
                    '.',
                    ',',
                    'ArrowUp',
                    'ArrowDown'
                ].includes(e.key)) {

                    e.preventDefault();
                }
            });

        $(document).off('wheel', 'input[type="number"]')
            .on('wheel', 'input[type="number"]', function () {

                $(this).blur();

            });

        $(document).off('input', 'input[type="number"]')
            .on('input', 'input[type="number"]', function () {

                this.value = this.value.replace(/[^0-9]/g, '');

            });
    }

   function collectAnswers() {

        const answers = {};
        const files = {};

        $('.instrument-answer').each(function () {

            const questionId = $(this).data('id');

            if (!questionId) return;

            if ($(this).is(':file')) {

                const file = this.files && this.files.length
                    ? this.files[0]
                    : null;

                if (file) {
                    files[questionId] = file;
                }

            } else if ($(this).is(':radio')) {

                if ($(this).is(':checked')) {
                    answers[questionId] = $(this).val();
                }

            } else {

                answers[questionId] = $(this).val();
            }
        });

        $('.instrument-answer-checkbox').each(function () {

            const questionId = $(this).data('id');

            if (!questionId) return;

            if (!answers[questionId]) {
                answers[questionId] = [];
            }

            if ($(this).is(':checked')) {
                answers[questionId].push($(this).val());
            }
        });

        return {
            answers: answers,
            files: files
        };
    }

    function updateProgress() {

        let total = 0;
        let answered = 0;

        const items = $('.instrument-item[data-required="1"]').length > 0
            ? $('.instrument-item[data-required="1"]')
            : $('.instrument-item');

        items.each(function () {

            total++;

            const item = $(this);

            let hasValue = false;

            // CHECKBOX
            const checkbox = item.find('.instrument-answer-checkbox');

            if (checkbox.length) {

                hasValue = checkbox.filter(':checked').length > 0;

            } else {

                // RADIO
                const radio = item.find('.instrument-answer:radio');

                if (radio.length) {

                    hasValue = radio.filter(':checked').length > 0;

                } else {

                    const input = item.find('.instrument-answer').first();

                    // =========================
                    // FILE PDF / FOTO
                    // =========================
                    if (input.is(':file')) {

                        // File baru dipilih
                        if (
                            input[0] &&
                            input[0].files &&
                            input[0].files.length > 0
                        ) {
                            hasValue = true;
                        }

                        // File lama sudah tersimpan
                        if (!hasValue) {

                            if (
                                item.find('.uploaded-file').length > 0 ||
                                item.find('.uploaded-photo').length > 0
                            ) {
                                hasValue = true;
                            }
                        }

                    } else {

                        const val = input.val();

                        hasValue =
                            val !== null &&
                            String(val).trim() !== '';
                    }
                }
            }

            if (hasValue) {
                answered++;
            }
        });

        const percent = total
            ? Math.round((answered / total) * 100)
            : 0;

        $('#answeredCount').text(answered);
        $('#requiredCount').text(total);
        $('#progressText').text(percent + '%');
        $('#progressBar').css('width', percent + '%');
    }
   

  function saveDraft(callback) {

        if (saving) return;

        saving = true;

        const collected = collectAnswers();

        const answers = collected.answers || {};
        const files = collected.files || {};

        const btn = $('#btnSaveDraft');
        const old = btn.html();

        btn.prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');

        const formData = new FormData();

        // Jawaban biasa
        $.each(answers, function (questionId, answer) {

            if (Array.isArray(answer)) {

                formData.append(
                    'answers[' + questionId + ']',
                    JSON.stringify(answer)
                );

            } else {

                formData.append(
                    'answers[' + questionId + ']',
                    answer
                );
            }
        });

        // File
        $.each(files, function (questionId, file) {

            formData.append(
                'files[' + questionId + ']',
                file
            );
        });

        // CSRF
        if (
            window.VISITS_FORM_CSRF_NAME &&
            window.VISITS_FORM_CSRF_HASH
        ) {

            formData.append(
                window.VISITS_FORM_CSRF_NAME,
                window.VISITS_FORM_CSRF_HASH
            );
        }

        $.ajax({

            url: BASE_URL + 'visits/save-answers/' + VISIT_ID,

            type: 'POST',

            data: formData,

            dataType: 'json',

            processData: false,

            contentType: false

        })
        .done(function (res) {
            if (res && res.csrf_hash) {
                window.VISITS_FORM_CSRF_HASH = res.csrf_hash;
            }

            if (!res || res.status === false) {
                showError(
                    res && res.message
                        ? res.message
                        : 'Draft gagal disimpan.'
                );

                if (typeof callback === 'function') {
                    callback(false);
                }

                return;
            }

            updateProgress();

            if (typeof callback === 'function') {
                callback(true);
            } else {
                showSuccess(
                    res.message || 'Draft berhasil disimpan.',
                    true
                );
            }
        })
        .fail(function (xhr) {

            handleAjaxError(
                xhr,
                'Gagal menyimpan Draft.'
            );

            if (typeof callback === 'function') {
                callback(false);
            }

        })
        .always(function () {

            saving = false;

            btn.prop('disabled', false)
                .html(old);
        });
    }
    function updateStatus(status) {
        const statusEl = $('#formStatus');
        statusEl.text(status);
        statusEl.removeClass('status-draft status-progress status-completed bg-secondary bg-warning bg-success text-white badge');

        if (status === 'DRAFT') {
            statusEl.addClass('status-draft badge bg-secondary');
        } else if (status === 'IN_PROGRESS') {
            statusEl.addClass('status-progress badge bg-warning text-dark');
        } else if (status === 'COMPLETED') {
            statusEl.addClass('status-completed badge bg-success');

            // Sembunyikan Tombol Simpan Draft & Selesaikan Monev
            $('#btnSaveDraft').addClass('d-none').hide();
            $('#btnCompleteVisit').addClass('d-none').hide();

            // Nonaktifkan semua input form agar bersifat Read-Only
            $('.instrument-answer, .instrument-answer-checkbox').prop('disabled', true);
        }
    }
    
    function handleAjaxError(xhr, defaultMsg) {
        let message = defaultMsg;
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.status === 404) {
            message = 'Endpoint URL tidak ditemukan (404).';
        } else if (xhr.status === 500) {
            message = 'Terjadi kesalahan pada server (500).';
        }
        showError(message);
    }

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function escapeAttr(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function showSuccess(message, redirect) {
        if (typeof window.showGlobalAlert === 'function') {
            window.showGlobalAlert(message, 'success');
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: message, timer: 1200, showConfirmButton: false });
        } else {
            alert(message);
        }

        if (redirect) {
            setTimeout(function () {
                window.location.href = BASE_URL.replace(/\/+$/, '') + '/visits';
            }, 800);
        }
    }

    function showError(message) {
        if (typeof window.showGlobalAlert === 'function') {
            window.showGlobalAlert(message, 'error');
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Gagal', text: message });
        } else {
            alert(message);
        }
    }

    // READY STATE & EVENT BINDING
   $(document).ready(function () {
        loadForm();

        $('#btnSaveDraft').off('click').on('click', function (e) {
            e.preventDefault();
            saveDraft();
        });

        $('#btnCompleteVisit, #btnSubmitVisits').off('click').on('click', function (e) {
            e.preventDefault();

            $('#submitVisitId').val(VISIT_ID);

            if (typeof window.SCHOOL_NAME !== 'undefined') {
                $('#submitVisitSchool').text(window.SCHOOL_NAME);
            }

            const modalEl = document.getElementById('submitVisitModal');

            if (modalEl) {
                const submitModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                submitModal.show();
            }
        });

        $('#btnConfirmSubmitVisit').off('click').on('click', function () {
            const btn = $(this);
            const oldHtml = btn.html();

            btn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-1"></i>Memproses...');

            saveDraft(function (saved) {

                if (!saved) {
                    btn.prop('disabled', false).html(oldHtml);
                    return;
                }

                const postData = {};

                if (
                    window.VISITS_FORM_CSRF_NAME &&
                    window.VISITS_FORM_CSRF_HASH
                ) {
                    postData[window.VISITS_FORM_CSRF_NAME] =
                        window.VISITS_FORM_CSRF_HASH;
                }

                ajax('visits/complete/' + VISIT_ID, {
                    type: 'POST',
                    data: postData
                })
                .done(function (res) {

                    if (!res || res.status === false) {

                        let msg = res && res.message
                            ? res.message
                            : 'Gagal menyelesaikan kegiatan.';

                        if (res.missing && res.missing.length) {
                            msg += '\n\nInstrumen belum terisi:\n' +
                                res.missing.join('\n');
                        }

                        showError(msg);
                        return;
                    }

                    const modalEl =
                        document.getElementById('submitVisitModal');

                    if (modalEl) {
                        const modalInstance =
                            bootstrap.Modal.getInstance(modalEl);

                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    }

                    window.location.href =
                        BASE_URL.replace(/\/+$/, '') + '/visits';

                })
                .fail(function (xhr) {

                    handleAjaxError(
                        xhr,
                        'Terjadi kesalahan pada server saat menyelesaikan kegiatan.'
                    );

                })
                .always(function () {

                    btn.prop('disabled', false)
                        .html(oldHtml);

                });
            });
        });
    });

})();