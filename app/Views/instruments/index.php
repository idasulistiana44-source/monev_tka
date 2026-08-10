<main class="app-main">
    <div class="instruments-page">
        <div class="instruments-page-header">
            <div>
                <h1 class="instruments-page-title">Instrumen Monev</h1>
                <p class="instruments-page-subtitle">Kelola template pertanyaan monitoring dan evaluasi.</p>
            </div>
            <div class="instruments-header-actions">
                <button type="button" class="btn btn-outline-primary" id="btnAddSection"><i class="fas fa-layer-group me-1"></i>Section</button>
                <button type="button" class="btn btn-primary" id="btnAddInstrument"><i class="fas fa-plus me-1"></i>Tambah Instrumen</button>
            </div>
        </div>
        <div class="instruments-card">
            <div class="instruments-card-header">
                <div class="instruments-card-title"><i class="fas fa-clipboard-list"></i> Daftar Instrumen</div>
                <div class="instruments-total"><span id="instrumentTotal">0</span> instrumen</div>
            </div>
            <div class="instruments-card-body">
                <div class="instruments-toolbar">
                    <div class="instruments-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="instrumentSearch" class="form-control" placeholder="Cari kode, pertanyaan, atau section..." autocomplete="off">
                    </div>
                    <select id="instrumentSectionFilter" class="form-select instruments-filter">
                        <option value="">Semua Section</option>
                    </select>
                    <button type="button" class="btn btn-outline-secondary" id="btnRefreshInstrument"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div class="instruments-table-responsive">
                    <table class="table instruments-table">
                        <thead>
                            <tr>
                                <th width="55">No</th>
                                <th width="100">Kode</th>
                                <th>Section</th>
                                <th>Pertanyaan</th>
                                <th width="110">Tipe</th>
                                <th width="90">Wajib</th>
                                <th width="90">Status</th>
                                <th width="145">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="instrumentTableBody">
                            <tr>
                                <td colspan="8" class="instrument-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat instrumen...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="instruments-section-card">
            <div class="instruments-section-header">
                <div>
                    <div class="instruments-card-title"><i class="fas fa-layer-group"></i> Section Instrumen</div>
                    <div class="instruments-section-subtitle">Kelompok pertanyaan yang digunakan dalam Monev.</div>
                </div>
            </div>
            <div class="instruments-section-body" id="sectionList"></div>
        </div>
    </div>
</main>
<div class="modal fade" id="instrumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content instrument-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="instrumentModalTitle"><i class="fas fa-clipboard-list me-2"></i>Tambah Instrumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="instrumentForm">
                <div class="modal-body">
                    <input type="hidden" id="instrumentId">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Section <span class="text-danger">*</span></label>
                            <select id="instrumentSection" class="form-select" required>
                                <option value="">Pilih Section</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" id="instrumentCode" class="form-control" placeholder="INF-01" maxlength="50" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" id="instrumentSortOrder" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                        <textarea id="instrumentQuestion" class="form-control" rows="3" placeholder="Masukkan pertanyaan instrumen..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea id="instrumentDescription" class="form-control" rows="2" placeholder="Petunjuk atau keterangan tambahan..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Jawaban <span class="text-danger">*</span></label>
                            <select id="instrumentAnswerType" class="form-select" required>
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="number">Number</option>
                                <option value="date">Date</option>
                                <option value="select">Select</option>
                                <option value="radio">Radio</option>
                                <option value="checkbox">Checkbox</option>
                                <option value="yesno">Ya / Tidak</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Wajib</label>
                            <select id="instrumentRequired" class="form-select">
                                <option value="1">Ya</option>
                                <option value="0">Tidak</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Status</label>
                            <select id="instrumentActive" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0" id="instrumentOptionsWrapper">
                        <label class="form-label">Pilihan Jawaban</label>
                        <textarea id="instrumentOptions" class="form-control" rows="4" placeholder="Contoh: Sangat Baik&#10;Baik&#10;Cukup&#10;Kurang"></textarea>
                        <div class="form-text">Satu pilihan per baris. Digunakan untuk Select, Radio, dan Checkbox.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveInstrument"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="sectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content instrument-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="sectionModalTitle"><i class="fas fa-layer-group me-2"></i>Tambah Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sectionForm">
                <div class="modal-body">
                    <input type="hidden" id="sectionId">
                    <div class="mb-3">
                        <label class="form-label">Nama Section <span class="text-danger">*</span></label>
                        <input type="text" id="sectionName" class="form-control" placeholder="Contoh: Infrastruktur dan Sarana" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea id="sectionDescription" class="form-control" rows="3" placeholder="Keterangan section..."></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Urutan</label>
                        <input type="number" id="sectionSortOrder" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveSection"><i class="fas fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteInstrumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content instrument-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash-alt text-danger me-2"></i>Hapus Instrumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="instrument-delete-icon"><i class="fas fa-trash-alt"></i></div>
                <p class="mb-1">Yakin ingin menghapus instrumen ini?</p>
                <strong id="deleteInstrumentName">-</strong>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDeleteInstrument"><i class="fas fa-trash me-1"></i>Hapus</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteSectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content instrument-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-trash-alt text-danger me-2"></i>Hapus Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="instrument-delete-icon"><i class="fas fa-layer-group"></i></div>
                <p class="mb-1">Yakin ingin menghapus section ini?</p>
                <strong id="deleteSectionName">-</strong>
                <small class="d-block text-muted mt-2">Section yang masih memiliki instrumen tidak dapat dihapus.</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btnConfirmDeleteSection"><i class="fas fa-trash me-1"></i>Hapus</button>
            </div>
        </div>
    </div>
</div>
