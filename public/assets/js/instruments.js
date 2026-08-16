$(document).ready(function(){
    let instrumentModal=null;
    let sectionModal=null;
    let deleteInstrumentModal=null;
    let deleteSectionModal=null;
    let currentInstrumentId=null;
    let currentSectionId=null;
    let sections=[];
    init();
    function init(){
        initModals();
        bindEvents();
        loadSections();
        loadInstruments();
    }
    function initModals(){
        instrumentModal=new bootstrap.Modal(document.getElementById('instrumentModal'));
        sectionModal=new bootstrap.Modal(document.getElementById('sectionModal'));
        deleteInstrumentModal=new bootstrap.Modal(document.getElementById('deleteInstrumentModal'));
        deleteSectionModal=new bootstrap.Modal(document.getElementById('deleteSectionModal'));
    }
    function bindEvents(){
        $('#btnAddInstrument').on('click',function(){
            openAddInstrument();
        });
        $('#btnAddSection').on('click',function(){
            openAddSection();
        });
        $('#btnRefreshInstrument').on('click',function(){
            loadInstruments(true);
        });
        $('#instrumentSearch').on('input',function(){
            loadInstruments();
        });
        $('#instrumentSectionFilter').on('change',function(){
            loadInstruments();
        });
        $('#instrumentAnswerType').on('change',function(){
            toggleOptions();
        });
        $('#instrumentForm').on('submit',function(e){
            e.preventDefault();
            saveInstrument();
        });
        $('#sectionForm').on('submit',function(e){
            e.preventDefault();
            saveSection();
        });
        $('#instrumentTableBody').on('click','.btn-edit-instrument',function(){
            openEditInstrument($(this).data('id'));
        });
        $('#instrumentTableBody').on('click','.btn-delete-instrument',function(){
            openDeleteInstrument($(this).data('id'),$(this).data('name'));
        });
        $('#sectionList').on('click','.btn-edit-section',function(){
            openEditSection($(this).data('id'));
        });
        $('#sectionList').on('click','.btn-delete-section',function(){
            openDeleteSection($(this).data('id'),$(this).data('name'));
        });
        $('#btnConfirmDeleteInstrument').on('click',function(){
            deleteInstrument();
        });
        $('#btnConfirmDeleteSection').on('click',function(){
            deleteSection();
        });
    }
    function loadSections(showMessage=false){
        $.ajax({
            url:BASE_URL+'instruments/sections',
            type:'GET',
            dataType:'json',
            success:function(response){
                if(!response.success){
                    showGlobalAlert(response.message||'Section gagal dimuat.','error');
                    return;
                }
                sections=response.data||[];
                renderSectionSelects();
                renderSections();
                if(showMessage){
                    showGlobalAlert('Section berhasil diperbarui.','success');
                }
            },
            error:function(xhr){
                showGlobalAlert(getAjaxError(xhr,'Data section gagal dimuat.'),'error');
            }
        });
    }
    function renderSectionSelects(){
        const filter=$('#instrumentSectionFilter');
        const form=$('#instrumentSection');
        const currentFilter=filter.val();
        const currentForm=form.val();
        filter.html('<option value="">Semua Section</option>');
        form.html('<option value="">Pilih Section</option>');
        sections.forEach(function(section){
            filter.append(`<option value="${section.id}">${escapeHtml(section.name)}</option>`);
            form.append(`<option value="${section.id}">${escapeHtml(section.name)}</option>`);
        });
        filter.val(currentFilter);
        form.val(currentForm);
    }
    function renderSections(){
        const container=$('#sectionList');
        container.empty();
        if(!sections.length){
            container.html('<div class="instrument-empty">Belum ada section.</div>');
            return;
        }
        sections.forEach(function(section,index){
            container.append(`
                <div class="section-item">
                    <div class="section-number">${index+1}</div>
                    <div class="section-info">
                        <div class="section-name">${escapeHtml(section.name)}</div>
                        <div class="section-description">${escapeHtml(section.description||'Tidak ada keterangan')}</div>
                    </div>
                    <div class="section-order">Urutan ${section.sort_order}</div>
                    <div class="section-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-section" data-id="${section.id}" title="Edit"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-section" data-id="${section.id}" data-name="${escapeHtml(section.name)}" title="Hapus"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `);
        });
    }
    function loadInstruments(showMessage=false){
        const keyword=$('#instrumentSearch').val().trim();
        const sectionId=$('#instrumentSectionFilter').val();
        $('#instrumentTableBody').html('<tr><td colspan="8" class="instrument-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat instrumen...</td></tr>');
        $.ajax({
            url:BASE_URL+'instruments/data',
            type:'GET',
            data:{
                keyword:keyword,
                section_id:sectionId
            },
            dataType:'json',
            success:function(response){
                if(response.success){
                    renderInstruments(response.data||[]);
                    if(showMessage){
                        showGlobalAlert('Data instrumen berhasil diperbarui.','success');
                    }
                }else{
                    renderEmpty(response.message||'Data instrumen tidak tersedia.');
                    showGlobalAlert(response.message||'Data instrumen gagal dimuat.','error');
                }
            },
            error:function(xhr){
                renderEmpty('Data instrumen gagal dimuat.');
                showGlobalAlert(getAjaxError(xhr,'Data instrumen gagal dimuat.'),'error');
            }
        });
    }
    function renderInstruments(data){
        const tbody=$('#instrumentTableBody');
        tbody.empty();
        $('#instrumentTotal').text(data.length);
        if(!data.length){
            renderEmpty('Belum ada instrumen.');
            return;
        }
        data.forEach(function(item,index){
            const typeLabel=getTypeLabel(item.answer_type);
            const required=parseInt(item.is_required)===1;
            const active=parseInt(item.is_active)===1;
            tbody.append(`
                <tr>
                    <td>${index+1}</td>
                    <td><span class="instrument-code">${escapeHtml(item.code)}</span></td>
                    <td><span class="instrument-section-badge">${escapeHtml(item.section_name||'-')}</span></td>
                    <td><div class="instrument-question">${escapeHtml(item.question)}</div>${item.description?`<small class="instrument-description">${escapeHtml(item.description)}</small>`:''}</td>
                    <td><span class="instrument-type">${escapeHtml(typeLabel)}</span></td>
                    <td>${required?'<span class="badge bg-danger-subtle text-danger">Ya</span>':'<span class="badge bg-light text-secondary">Tidak</span>'}</td>
                    <td>${active?'<span class="badge bg-success-subtle text-success">Aktif</span>':'<span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>'}</td>
                    <td>
                        <div class="instrument-actions">
                            <button type="button" class="btn btn-sm btn-primary btn-edit-instrument" data-id="${item.id}" title="Edit"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-instrument" data-id="${item.id}" data-name="${escapeHtml(item.code+' - '+item.question)}" title="Hapus"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }
    function renderEmpty(message){
        $('#instrumentTableBody').html(`<tr><td colspan="8" class="instrument-empty">${escapeHtml(message)}</td></tr>`);
        $('#instrumentTotal').text('0');
    }
    function openAddInstrument(){
        currentInstrumentId=null;
        $('#instrumentForm')[0].reset();
        $('#instrumentId').val('');
        $('#instrumentSortOrder').val(0);
        $('#instrumentRequired').val('1');
        $('#instrumentActive').val('1');
        $('#instrumentAnswerType').val('text');
        $('#instrumentModalTitle').html('<i class="fas fa-clipboard-list me-2"></i>Tambah Instrumen');
        $('#btnSaveInstrument').html('<i class="fas fa-save me-1"></i>Simpan');
        toggleOptions();
        instrumentModal.show();
    }
    function openEditInstrument(id){
        currentInstrumentId=id;
        $('#btnSaveInstrument').prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Memuat...');
        $.ajax({
            url:BASE_URL+'instruments/detail/'+id,
            type:'GET',
            dataType:'json',
            success:function(response){
                $('#btnSaveInstrument').prop('disabled',false).html('<i class="fas fa-save me-1"></i>Simpan Perubahan');
                if(!response.success){
                    showGlobalAlert(response.message||'Instrumen tidak ditemukan.','error');
                    return;
                }
                const data=response.data;
                $('#instrumentId').val(data.id);
                $('#instrumentSection').val(data.section_id);
                $('#instrumentCode').val(data.code);
                $('#instrumentQuestion').val(data.question);
                $('#instrumentDescription').val(data.description||'');
                $('#instrumentAnswerType').val(data.answer_type);
                $('#instrumentOptions').val(formatOptions(data.options));
                $('#instrumentRequired').val(String(data.is_required));
                $('#instrumentActive').val(String(data.is_active));
                $('#instrumentSortOrder').val(data.sort_order||0);
                $('#instrumentModalTitle').html('<i class="fas fa-edit me-2"></i>Edit Instrumen');
                toggleOptions();
                instrumentModal.show();
            },
            error:function(xhr){
                $('#btnSaveInstrument').prop('disabled',false).html('<i class="fas fa-save me-1"></i>Simpan Perubahan');
                showGlobalAlert(getAjaxError(xhr,'Detail instrumen gagal dimuat.'),'error');
            }
        });
    }
    function saveInstrument(){
        const id=$('#instrumentId').val();
        const isEdit=id!=='';
        const data={
            section_id:$('#instrumentSection').val(),
            code:$('#instrumentCode').val().trim(),
            question:$('#instrumentQuestion').val().trim(),
            description:$('#instrumentDescription').val().trim(),
            answer_type:$('#instrumentAnswerType').val(),
            options:$('#instrumentOptions').val().trim(),
            is_required:$('#instrumentRequired').val(),
            is_active:$('#instrumentActive').val(),
            sort_order:$('#instrumentSortOrder').val()
        };
        if(!data.section_id){
            showGlobalAlert('Section wajib dipilih.','warning');
            return;
        }
        if(!data.code){
            showGlobalAlert('Kode instrumen wajib diisi.','warning');
            return;
        }
        if(!data.question){
            showGlobalAlert('Pertanyaan wajib diisi.','warning');
            return;
        }
        const button=$('#btnSaveInstrument');
        button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');
        $.ajax({
            url:isEdit?BASE_URL+'instruments/update/'+id:BASE_URL+'instruments/store',
            type:'POST',
            data:data,
            dataType:'json',
            success:function(response){
                button.prop('disabled',false).html(isEdit?'<i class="fas fa-save me-1"></i>Simpan Perubahan':'<i class="fas fa-save me-1"></i>Simpan');
                if(response.success){
                    instrumentModal.hide();
                    loadInstruments();
                    showGlobalAlert(response.message||'Instrumen berhasil disimpan.','success');
                }else{
                    showGlobalAlert(response.message||'Instrumen gagal disimpan.','error');
                }
            },
            error:function(xhr){
                button.prop('disabled',false).html(isEdit?'<i class="fas fa-save me-1"></i>Simpan Perubahan':'<i class="fas fa-save me-1"></i>Simpan');
                showGlobalAlert(getAjaxError(xhr,'Instrumen gagal disimpan.'),'error');
            }
        });
    }
    function openDeleteInstrument(id,name){
        currentInstrumentId=id;
        $('#deleteInstrumentName').text(name||'-');
        $('#btnConfirmDeleteInstrument').prop('disabled',false).html('<i class="fas fa-trash me-1"></i>Hapus');
        deleteInstrumentModal.show();
    }
    function deleteInstrument(){
        if(!currentInstrumentId){
            showGlobalAlert('Instrumen belum dipilih.','warning');
            return;
        }
        const id=currentInstrumentId;
        const button=$('#btnConfirmDeleteInstrument');
        button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menghapus...');
        $.ajax({
            url:BASE_URL+'instruments/delete/'+id,
            type:'POST',
            dataType:'json',
            success:function(response){
                button.prop('disabled',false).html('<i class="fas fa-trash me-1"></i>Hapus');
                if(response.success){
                    deleteInstrumentModal.hide();
                    currentInstrumentId=null;
                    loadInstruments();
                    showGlobalAlert(response.message||'Instrumen berhasil dihapus.','success');
                }else{
                    showGlobalAlert(response.message||'Instrumen gagal dihapus.','error');
                }
            },
            error:function(xhr){
                button.prop('disabled',false).html('<i class="fas fa-trash me-1"></i>Hapus');
                showGlobalAlert(getAjaxError(xhr,'Instrumen gagal dihapus.'),'error');
            }
        });
    }
    function openAddSection(){
        currentSectionId=null;
        $('#sectionForm')[0].reset();
        $('#sectionId').val('');
        $('#sectionSortOrder').val(0);
        $('#sectionModalTitle').html('<i class="fas fa-layer-group me-2"></i>Tambah Section');
        $('#btnSaveSection').html('<i class="fas fa-save me-1"></i>Simpan');
        sectionModal.show();
    }
    function openEditSection(id){
        const section=sections.find(function(item){
            return String(item.id)===String(id);
        });
        if(!section){
            showGlobalAlert('Section tidak ditemukan.','error');
            return;
        }
        currentSectionId=id;
        $('#sectionId').val(section.id);
        $('#sectionName').val(section.name);
        $('#sectionDescription').val(section.description||'');
        $('#sectionSortOrder').val(section.sort_order||0);
        $('#sectionModalTitle').html('<i class="fas fa-edit me-2"></i>Edit Section');
        $('#btnSaveSection').html('<i class="fas fa-save me-1"></i>Simpan Perubahan');
        sectionModal.show();
    }
    function saveSection(){
        const id=$('#sectionId').val();
        const isEdit=id!=='';
        const data={
            name:$('#sectionName').val().trim(),
            description:$('#sectionDescription').val().trim(),
            sort_order:$('#sectionSortOrder').val()
        };
        if(!data.name){
            showGlobalAlert('Nama section wajib diisi.','warning');
            return;
        }
        const button=$('#btnSaveSection');
        button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');
        $.ajax({
            url:isEdit?BASE_URL+'instruments/section-update/'+id:BASE_URL+'instruments/section-store',
            type:'POST',
            data:data,
            dataType:'json',
            success:function(response){
                button.prop('disabled',false).html(isEdit?'<i class="fas fa-save me-1"></i>Simpan Perubahan':'<i class="fas fa-save me-1"></i>Simpan');
                if(response.success){
                    sectionModal.hide();
                    loadSections();
                    loadInstruments();
                    showGlobalAlert(response.message||'Section berhasil disimpan.','success');
                }else{
                    showGlobalAlert(response.message||'Section gagal disimpan.','error');
                }
            },
            error:function(xhr){
                button.prop('disabled',false).html(isEdit?'<i class="fas fa-save me-1"></i>Simpan Perubahan':'<i class="fas fa-save me-1"></i>Simpan');
                showGlobalAlert(getAjaxError(xhr,'Section gagal disimpan.'),'error');
            }
        });
    }
    function openDeleteSection(id,name){
        currentSectionId=id;
        $('#deleteSectionName').text(name||'-');
        $('#btnConfirmDeleteSection').prop('disabled',false).html('<i class="fas fa-trash me-1"></i>Hapus');
        deleteSectionModal.show();
    }
    function deleteSection(){
        if(!currentSectionId){
            showGlobalAlert('Section belum dipilih.','warning');
            return;
        }
        const id=currentSectionId;
        const button=$('#btnConfirmDeleteSection');
        button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menghapus...');
        $.ajax({
            url:BASE_URL+'instruments/section-delete/'+id,
            type:'POST',
            dataType:'json',
            success:function(response){
                button.prop('disabled',false).html('<i class="fas fa-trash me-1"></i>Hapus');
                if(response.success){
                    deleteSectionModal.hide();
                    currentSectionId=null;
                    loadSections();
                    loadInstruments();
                    showGlobalAlert(response.message||'Section berhasil dihapus.','success');
                }else{
                    showGlobalAlert(response.message||'Section gagal dihapus.','error');
                }
            },
            error:function(xhr){
                button.prop('disabled',false).html('<i class="fas fa-trash me-1"></i>Hapus');
                showGlobalAlert(getAjaxError(xhr,'Section gagal dihapus.'),'error');
            }
        });
    }
    function toggleOptions(){
        const type=$('#instrumentAnswerType').val();
        if(['select','radio','checkbox'].includes(type)){
            $('#instrumentOptionsWrapper').show();
        }else{
            $('#instrumentOptionsWrapper').hide();
            $('#instrumentOptions').val('');
        }
    }
    function getTypeLabel(type){
        const labels={
            text:'Text',
            textarea:'Textarea',
            number:'Number',
            date:'Date',
            select:'Select',
            radio:'Radio',
            checkbox:'Checkbox',
            yesno:'Ya / Tidak',
            pdf:'Upload PDF',
            photo:'Upload Foto'
        };
        return labels[type]||type;
    }
    function formatOptions(options){
        if(!options){
            return '';
        }
        try{
            const data=JSON.parse(options);
            if(Array.isArray(data)){
                return data.map(function(item){
                    return typeof item==='object'?(item.label||item.value||''):item;
                }).join('\n');
            }
        }catch(e){}
        return options;
    }
    function getAjaxError(xhr,fallback){
        if(xhr.responseJSON&&xhr.responseJSON.message){
            return xhr.responseJSON.message;
        }
        if(xhr.responseJSON&&xhr.responseJSON.errors){
            return Object.values(xhr.responseJSON.errors).join(' ');
        }
        return fallback;
    }
    function escapeHtml(value){
        return $('<div>').text(value??'').html();
    }
});