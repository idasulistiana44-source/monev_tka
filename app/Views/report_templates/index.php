<div class="report-template-page">

    <!-- HEADER -->
    <div class="report-template-toolbar">
        <div>
            <h1>Editor Template</h1>
            <p>Template Laporan Monitoring dan Evaluasi Pelaksanaan TKA Provinsi</p>
        </div>

        <div class="report-toolbar-actions">
            <button
                type="button"
                class="btn btn-secondary"
                id="btnResetEditor">
                <i class="fas fa-undo me-1"></i>
                Reset
            </button>

            <button
                type="button"
                class="btn btn-success"
                id="btnSaveEditor">
                <i class="fas fa-save me-1"></i>
                Simpan Template
            </button>
        </div>
    </div>


    <!-- HEADER / COVER -->
    <div class="report-cover-card">

        <div class="report-logo-wrap">
            <div class="logo-upload-wrap">
                <img
                    id="coverLogo"
                    src="<?= base_url('assets/img/tutwuri.svg') ?>"
                    alt="Tut Wuri Handayani">
            </div>
        </div>

        <input
            type="text"
            class="cover-title-input"
            id="coverTitle"
            value="LAPORAN MONITORING DAN EVALUASI">

        <input
            type="text"
            class="cover-subtitle-input"
            id="coverSubtitle"
            value="PELAKSANAAN TES KEMAMPUAN AKADEMIK (TKA) PROVINSI">

        <input
            type="text"
            class="cover-level-input"
            id="coverLevel"
            value="Jenjang SMA, SMK, dan MA">

        <div class="cover-location-box">

            <div class="cover-location-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>

            <div class="cover-location-content">

                <input
                    type="text"
                    class="cover-location-label"
                    id="coverLocationLabel"
                    value="TEMPAT TUGAS AKTIF">

                <input
                    type="text"
                    class="cover-location-value"
                    id="coverLocationValue"
                    value="Provinsi DKI Jakarta">

            </div>

        </div>

        <div class="cover-info-row">

            <div class="cover-info-field">
                <label>Kabupaten/Kota</label>

                <input
                    type="text"
                    id="coverKabupaten"
                    value="">
            </div>

            <div class="cover-info-field">
                <label>Tahun Pelaksanaan</label>

                <input
                    type="number"
                    id="coverTahun"
                    value="<?= date('Y') ?>">
            </div>

        </div>

    </div>


    <!-- EDITOR CONTENT -->
    <div id="editorContainer">

        <div class="editor-loading">
            <i class="fas fa-spinner fa-spin me-2"></i>
            Memuat template...
        </div>

    </div>

</div>


<!-- TEMPLATE SECTION -->
<script type="text/template" id="editorSectionTemplate">

    <div class="report-section-card editor-section-card">

        <div class="report-section-title">
            <div class="editor-section-heading">
                <i class="fas fa-layer-group"></i>
                <span class="editor-section-title"></span>
            </div>
        </div>

        <div class="report-section-items"></div>

    </div>

</script>


<!-- TEMPLATE ITEM -->
<script type="text/template" id="editorItemTemplate">

    <div class="report-item editor-item">

        <div class="editor-item-title"></div>

        <textarea class="item-editor"></textarea>

    </div>

</script>