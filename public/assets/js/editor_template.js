$(function () {
    let originalData = [];
    function initSummernote() {
        if (typeof $.fn.summernote !== 'function') {
            console.error('Summernote belum ter-load.');
            showGlobalAlert('Editor belum tersedia.', 'error');
            return;
        }
        $('.item-editor').each(function () {
            const $editor = $(this);
            if ($editor.next('.note-editor').length) {
                return;
            }
            $editor.summernote({
                height: 220,
                minHeight: 180,
                maxHeight: 500,
                focus: false,
                placeholder: 'Tulis isi template di sini...',
                dialogsInBody: true,
                disableDragAndDrop: true,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        });
    }
    function destroySummernote() {
        $('.item-editor').each(function () {
            const $editor = $(this);
            if ($editor.next('.note-editor').length) {
                try {
                    $editor.summernote('destroy');
                } catch (error) {
                    console.warn('Gagal destroy Summernote:', error);
                }
            }
        });
    }
    function renderSection(section) {
        const $section = $($('#editorSectionTemplate').html());
        const sectionTitle = section.section_title || '';
        $section.attr('data-section-title', sectionTitle);
        $section.find('.editor-section-title').text(sectionTitle);
        const $items = $section.find('.report-section-items');
        const items = Array.isArray(section.items) ? section.items : [];
        items.forEach(function (item) {
            renderItem($items, item);
        });
        $('#editorContainer').append($section);
    }
    function renderItem($items, item) {
        const $item = $($('#editorItemTemplate').html());
        $item.attr('data-item-id', item.id || '');
        $item.find('.editor-item-title').text(item.item_title || '');
        $item.find('.item-editor').val(item.content || '');
        $items.append($item);
    }
    function renderTemplate(data) {
        destroySummernote();
        $('#editorContainer').empty();
        if (!Array.isArray(data) || !data.length) {
            $('#editorContainer').html('<div class="editor-empty"><i class="fas fa-file-alt"></i><h4>Belum ada struktur template</h4><p>Silakan buat section dan item terlebih dahulu pada halaman Struktur Template.</p></div>');
            return;
        }
        const sections = [];
        const sectionMap = {};
        data.forEach(function (item) {
            const sectionTitle = item.section_title || 'Tanpa Section';
            if (!sectionMap[sectionTitle]) {
                sectionMap[sectionTitle] = {
                    section_title: sectionTitle,
                    items: []
                };
                sections.push(sectionMap[sectionTitle]);
            }
            sectionMap[sectionTitle].items.push(item);
        });
        sections.forEach(function (section) {
            renderSection(section);
        });
        initSummernote();
    }
    function loadTemplate() {
        destroySummernote();
        $('#editorContainer').html('<div class="editor-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat template...</div>');
        $.ajax({
            url: BASE_URL + 'template-report/editor/data',
            type: 'GET',
            dataType: 'json'
        }).done(function (res) {
            if (!res.status) {
                showGlobalAlert(res.message || 'Template gagal dimuat.', 'error');
                return;
            }
            originalData = JSON.parse(JSON.stringify(res.data || []));
            renderTemplate(res.data || []);
        }).fail(function (xhr) {
            console.error(xhr.responseText);
            showGlobalAlert('Template laporan gagal dimuat.', 'error');
        });
    }
    function getEditorContent($item) {
        const $editor = $item.find('.item-editor');
        if (typeof $.fn.summernote === 'function' && $editor.next('.note-editor').length) {
            return $editor.summernote('code');
        }
        return $editor.val() || '';
    }
    function collectEditorData() {
        const data = [];
        $('#editorContainer .editor-item').each(function () {
            const $item = $(this);
            const id = parseInt($item.attr('data-item-id') || 0, 10);
            if (!id) {
                return;
            }
            data.push({
                id: id,
                content: getEditorContent($item)
            });
        });
        return data;
    }
    function saveEditor() {
        const data = collectEditorData();
        if (!data.length) {
            showGlobalAlert('Tidak ada konten template yang dapat disimpan.', 'warning');
            return;
        }
        const $button = $('#btnSaveEditor');
        $button.prop('disabled', true);
        $.ajax({
            url: BASE_URL + 'template-report/editor/save',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': CSRF_HASH
            }
        }).done(function (res) {
            if (res.status) {
                originalData = JSON.parse(JSON.stringify(collectEditorData()));
                if (res.csrfHash) {
                    window.CSRF_HASH = res.csrfHash;
                }
                showGlobalAlert(res.message || 'Konten template berhasil disimpan.', 'success');
            } else {
                showGlobalAlert(res.message || 'Konten template gagal disimpan.', 'error');
            }
        }).fail(function (xhr) {
            console.error(xhr.responseText);
            let message = 'Terjadi kesalahan saat menyimpan template.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showGlobalAlert(message, 'error');
        }).always(function () {
            $button.prop('disabled', false);
        });
    }
    $('#btnResetEditor').on('click', function () {
        if (!originalData.length) {
            loadTemplate();
            return;
        }
        renderTemplate(JSON.parse(JSON.stringify(originalData)));
        showGlobalAlert('Perubahan yang belum disimpan telah dikembalikan.', 'success');
    });
    $('#btnSaveEditor').on('click', function () {
        saveEditor();
    });
    loadTemplate();
});