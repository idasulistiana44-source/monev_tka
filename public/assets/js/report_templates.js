$(function () {

    let originalData = [];


    /*
    |--------------------------------------------------------------------------
    | Summernote
    |--------------------------------------------------------------------------
    */

    function initEditor($editor, content) {

        if (typeof $.fn.summernote !== 'function') {

            console.error('Summernote belum ter-load.');

            return;
        }

        $editor.summernote({

            height: 220,

            placeholder: 'Isi template laporan...',

            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview']]
            ]

        });

        $editor.summernote('code', content || '');
    }


    /*
    |--------------------------------------------------------------------------
    | Render Section
    |--------------------------------------------------------------------------
    */

    function renderSection(section) {

        const $section = $(
            $('#editorSectionTemplate').html()
        );

        $section.attr(
            'data-section-id',
            section.id || ''
        );

        $section.find('.editor-section-title')
            .text(section.section_title || '');

        $('#editorContainer').append($section);


        /*
        |--------------------------------------------------------------------------
        | Render Item
        |--------------------------------------------------------------------------
        */

        const items = section.items || [];

        items.forEach(function (item) {

            const $item = $(
                $('#editorItemTemplate').html()
            );

            $item.attr(
                'data-item-id',
                item.id || ''
            );

            $item.find('.editor-item-title')
                .text(item.item_title || '');

            $section
                .find('.report-section-items')
                .append($item);

            initEditor(
                $item.find('.item-editor'),
                item.content || ''
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Render Template
    |--------------------------------------------------------------------------
    */

    function renderTemplate(data) {

        $('#editorContainer').empty();

        if (!Array.isArray(data) || !data.length) {

            $('#editorContainer').html(`
                <div class="editor-empty">
                    <i class="fas fa-file-alt"></i>
                    <h4>Belum ada struktur template</h4>
                    <p>
                        Silakan buat section dan item terlebih dahulu
                        pada halaman Struktur Template.
                    </p>
                </div>
            `);

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Kelompokkan berdasarkan section_title
        |--------------------------------------------------------------------------
        */

        const sections = [];

        const sectionMap = {};


        data.forEach(function (item) {

            const sectionTitle =
                item.section_title || 'Tanpa Section';

            if (!sectionMap[sectionTitle]) {

                sectionMap[sectionTitle] = {

                    section_title: sectionTitle,

                    items: []

                };

                sections.push(
                    sectionMap[sectionTitle]
                );
            }

            sectionMap[sectionTitle].items.push(item);

        });


        sections.forEach(function (section) {

            renderSection(section);

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Load Template
    |--------------------------------------------------------------------------
    */

    function loadTemplate() {

        $('#editorContainer').html(`
            <div class="editor-loading">
                <i class="fas fa-spinner fa-spin me-2"></i>
                Memuat template...
            </div>
        `);


        $.ajax({

            url: BASE_URL + 'template-report/editor/data',

            type: 'GET',

            dataType: 'json'

        })

        .done(function (res) {

            if (!res.status) {

                showGlobalAlert(
                    res.message || 'Template gagal dimuat.',
                    'error'
                );

                return;
            }


            originalData =
                JSON.parse(
                    JSON.stringify(res.data || [])
                );


            renderTemplate(res.data || []);

        })

        .fail(function (xhr) {

            console.error(
                xhr.responseText
            );

            showGlobalAlert(
                'Template laporan gagal dimuat.',
                'error'
            );

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Collect Content
    |--------------------------------------------------------------------------
    */

    function collectEditorData() {

        const data = [];


        $('#editorContainer .editor-item')
            .each(function () {

                const $item = $(this);

                const id =
                    $item.attr('data-item-id');


                if (!id) {
                    return;
                }


                let content = '';


                if (
                    typeof $.fn.summernote === 'function' &&
                    $item.find('.item-editor')
                        .next('.note-editor')
                        .length
                ) {

                    content =
                        $item.find('.item-editor')
                            .summernote('code');

                } else {

                    content =
                        $item.find('.item-editor')
                            .val() || '';

                }


                data.push({

                    id: parseInt(id),

                    content: content

                });

            });


        return data;

    }


    /*
    |--------------------------------------------------------------------------
    | Save Editor
    |--------------------------------------------------------------------------
    */

    function saveEditor() {

        const data =
            collectEditorData();


        if (!data.length) {

            showGlobalAlert(
                'Tidak ada konten template yang dapat disimpan.',
                'warning'
            );

            return;
        }


        $('#btnSaveEditor')
            .prop('disabled', true);


        $.ajax({

            url:
                BASE_URL +
                'template-report/editor/save',

            type: 'POST',

            contentType:
                'application/json',

            data:
                JSON.stringify(data),

            dataType:
                'json',

            headers: {

                'X-CSRF-TOKEN':
                    CSRF_HASH

            }

        })

        .done(function (res) {

            if (res.status) {

                originalData =
                    JSON.parse(
                        JSON.stringify(
                            collectEditorData()
                        )
                    );


                if (res.csrfHash) {

                    window.CSRF_HASH =
                        res.csrfHash;

                }


                showGlobalAlert(
                    res.message ||
                    'Konten template berhasil disimpan.',
                    'success'
                );

            } else {

                showGlobalAlert(
                    res.message ||
                    'Konten template gagal disimpan.',
                    'error'
                );

            }

        })

        .fail(function (xhr) {

            console.error(
                xhr.responseText
            );


            let message =
                'Terjadi kesalahan saat menyimpan template.';


            if (
                xhr.responseJSON &&
                xhr.responseJSON.message
            ) {

                message =
                    xhr.responseJSON.message;

            }


            showGlobalAlert(
                message,
                'error'
            );

        })

        .always(function () {

            $('#btnSaveEditor')
                .prop('disabled', false);

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    $('#btnResetEditor').on(
        'click',
        function () {

            if (!originalData.length) {

                loadTemplate();

                return;
            }


            renderTemplate(
                JSON.parse(
                    JSON.stringify(originalData)
                )
            );


            showGlobalAlert(
                'Perubahan yang belum disimpan telah dikembalikan.',
                'success'
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    $('#btnSaveEditor').on(
        'click',
        function () {

            saveEditor();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Initial Load
    |--------------------------------------------------------------------------
    */

    loadTemplate();

}); 