 <link rel="stylesheet" href="<?= base_url('assets/css/visits-instrument.css') ?>">
<main class="app-main">
    <div class="visit-instrument-page">
        <div class="visit-instrument-header">
            <div>
                <button type="button" class="btn btn-light btn-sm mb-3" id="btnBackVisit"><i class="fas fa-arrow-left me-1"></i>Kembali</button>
                <h1 class="visit-instrument-title">Visitasi Monev TKA</h1>
                <p class="visit-instrument-subtitle">Pengisian data hasil visitasi berdasarkan instrumen Monev.</p>
            </div>
            <div class="visit-instrument-actions">
                <button type="button" class="btn btn-outline-primary" id="btnSaveDraft"><i class="fas fa-save me-1"></i>Simpan Draft</button>
                <button type="button" class="btn btn-primary" id="btnCompleteVisit"><i class="fas fa-check me-1"></i>Selesaikan Visitasi</button>
            </div>
        </div>
        <div id="visitInfo" class="visit-info-card">
            <div class="visit-info-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data visitasi...</div>
        </div>
        <form id="visitInstrumentForm">
            <div id="instrumentContainer">
                <div class="instrument-page-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat instrumen...</div>
            </div>
        </form>
    </div>
<script>
window.VISIT_ID=<?= (int)($visitId??0) ?>;
</script>

