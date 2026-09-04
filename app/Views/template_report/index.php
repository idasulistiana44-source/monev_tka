<div class="template-report-page">
    <div class="template-report-header">
        <div>
            <h1>Template Laporan</h1>
            <p>Atur struktur bagian dan item laporan. Isi konten dilakukan pada halaman berikutnya.</p>
        </div>
        <button type="button" class="btn btn-primary" id="btnAddSection">
            <i class="fas fa-plus me-1"></i>
            Tambah Section
        </button>
    </div>
    <div id="sectionsContainer"></div>
    <div class="template-empty" id="emptyTemplate">
        <i class="fas fa-layer-group"></i>
        <h5>Belum Ada Section</h5>
        <p>Tambahkan section untuk mulai membuat struktur template.</p>
        <button type="button" class="btn btn-primary" id="btnEmptyAddSection">
            <i class="fas fa-plus me-1"></i>
            Tambah Section
        </button>
    </div>
    <div class="template-save-area">
        <button type="button" class="btn btn-success btn-save-template" id="btnSaveTemplate">
            <i class="fas fa-save me-1"></i>
            Simpan Struktur Template
        </button>
    </div>
</div>
<script type="text/template" id="sectionTemplate">
    <div class="report-section-card" data-section-id="">
        <div class="report-section-header">
            <div class="section-number">SECTION</div>
            <input type="text" class="section-title-input" placeholder="Contoh: I. PENDAHULUAN">
            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-section" title="Hapus section">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="report-section-body">
            <div class="item-label">ITEM</div>
            <div class="report-section-items"></div>
            <button type="button" class="btn btn-sm btn-outline-primary btn-add-item">
                <i class="fas fa-plus me-1"></i>
                Tambah Item
            </button>
        </div>
    </div>
</script>
<script type="text/template" id="itemTemplate">
    <div class="report-item" data-item-id="">
        <input type="text" class="item-title-input" placeholder="Contoh: 1. Latar Belakang">
        <button type="button" class="btn-delete-item" title="Hapus item">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</script>
<script>
window.TEMPLATE_REPORT_URLS={
    data:"<?= site_url('template-report/data') ?>",
    save:"<?= site_url('template-report/save') ?>"
};
</script>