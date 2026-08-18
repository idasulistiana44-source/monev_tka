(function(){
    'use strict';
    const URLS=window.VISITS_URLS||{};
    let schools=[];
    let officers=[];
    let searchTimer=null;
    let editChangingRegion=false;
    function request(url,options){
        options=options||{};
        return $.ajax({
            url:url,
            type:options.type||'GET',
            data:options.data||{},
            dataType:'json',
            traditional:true
        });
    }
    function notify(message,type){
        if(typeof window.showGlobalAlert==='function'){
            window.showGlobalAlert(message,type||'success');
        }else{
            console.error('Global notification function showGlobalAlert tidak ditemukan:',message);
        }
    }
    function loadVisits(){
        const keyword=$.trim($('#searchVisit').val()||'');
        const status=$('#filterStatus').val()||'';
        $('#visitTableBody').html('<tr><td colspan="8" class="visit-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td></tr>');
        if($('#visitEmpty').length){
            $('#visitEmpty').hide();
        }
        request(URLS.data,{
            data:{
                keyword:keyword,
                status:status
            }
        }).done(function(res){
            if(!res||res.status===false||res.success===false){
                renderError(res&&res.message?res.message:'Data visitasi gagal dimuat.');
                notify(res&&res.message?res.message:'Data visitasi gagal dimuat.','error');
                return;
            }
            if(res.csrfHash){
                window.VISITS_CSRF_HASH=res.csrfHash;
            }
            renderVisits(res.data||[]);
        }).fail(function(xhr){
            let message='Gagal memuat data visitasi.';
            if(xhr.responseJSON&&xhr.responseJSON.message){
                message=xhr.responseJSON.message;
            }
            renderError(message);
            notify(message,'error');
            console.error('VISITS DATA ERROR:',xhr.responseText);
        });
    }
    function renderVisits(rows){
        $('#visitTotal').text(rows.length);
        if(!rows.length){
            $('#visitTableBody').html('');
            if($('#visitEmpty').length){
                $('#visitEmpty').show();
            }
            return;
        }
        if($('#visitEmpty').length){
            $('#visitEmpty').hide();
        }
        let html='';
        $.each(rows,function(index,row){
            const members=Array.isArray(row.members)?row.members:[];
            let team='';
            if(members.length){
                $.each(members,function(i,member){
                    team+='<span class="visit-team-badge">'+escapeHtml(member.name||'Petugas')+'</span>';
                });
            }else{
                team='<span class="text-muted">Belum ada petugas</span>';
            }
            let action='';
            if(row.status === 'DRAFT'){

                action =
                    '<a href="'+baseVisitUrl('visits/form/'+row.id)+'" '+
                    'class="btn btn-sm btn-primary" title="Mulai Monev">'+
                    '<i class="fas fa-play me-1"></i>Mulai'+
                    '</a>';

                action +=
                    '<button type="button" '+
                    'class="btn btn-sm btn-outline-primary btn-edit-visit" '+
                    'data-id="'+escapeAttr(row.id)+'" '+
                    'title="Edit Kegiatan">'+
                    '<i class="fas fa-edit"></i>'+
                    '</button>';

                action +=
                    '<button type="button" '+
                    'class="btn btn-sm btn-outline-danger btn-delete-visit" '+
                    'data-id="'+escapeAttr(row.id)+'" '+
                    'data-school="'+escapeAttr(row.school_name||'')+'" '+
                    'title="Hapus">'+
                    '<i class="fas fa-trash"></i>'+
                    '</button>';

            }else if(row.status === 'IN_PROGRESS'){

                action =
                    '<a href="'+baseVisitUrl('visits/form/'+row.id)+'" '+
                    'class="btn btn-sm btn-primary" title="Lanjutkan Monev">'+
                    '<i class="fas fa-edit me-1"></i>Lanjutkan'+
                    '</a>';

            }else if(row.status === 'COMPLETED'){

                action =
                    '<a href="'+baseVisitUrl('visits/form/'+row.id)+'" '+
                    'class="btn btn-sm btn-outline-primary" title="Lihat Monev">'+
                    '<i class="fas fa-eye me-1"></i>Lihat'+
                    '</a>';
            }
            html+='<tr>';
            html+='<td>'+(index+1)+'</td>';
            html+='<td><strong>'+escapeHtml(row.npsn||'-')+'</strong></td>';
            html+='<td><strong>'+escapeHtml(row.school_name||'-')+'</strong></td>';
            html+='<td>'+escapeHtml(row.region_name||'-')+'</td>';
            html+='<td>'+escapeHtml(row.level||'-')+'</td>';
            html+='<td>'+formatDate(row.visit_date)+'</td>';
            html+='<td><div class="visit-team-list">'+team+'</div></td>';
            html+='<td>'+renderStatus(row.status)+'</td>';
            html+='<td><div class="visit-actions">'+action+'</div></td>';
            html+='</tr>';
        });
        $('#visitTableBody').html(html);
    }
    function renderStatus(status){
        let text='-';
        let cls='visit-status-draft';
        if(status==='DRAFT'){
            text='Draft';
            cls='visit-status-draft';
        }else if(status==='IN_PROGRESS'){
            text='Berlangsung';
            cls='visit-status-progress';
        }else if(status==='COMPLETED'){
            text='Selesai';
            cls='visit-status-completed';
        }else if(status==='VERIFIED'){
            text='Terverifikasi';
            cls='visit-status-verified';
        }
        return '<span class="visit-status '+cls+'">'+text+'</span>';
    }
    function initVisitSelect2(){
        const modalParent=$('#visitAddModal');
        $('#visitRegion').select2({
            width:'100%',
            placeholder:'Pilih Wilayah',
            allowClear:true,
            dropdownParent:modalParent,
            language:{
                noResults:function(){
                    return 'Wilayah tidak ditemukan';
                },
                searching:function(){
                    return 'Mencari...';
                }
            }
        });
        $('#visitSchool').prop('disabled',true).select2({
            width:'100%',
            placeholder:'Pilih Sekolah',
            allowClear:true,
            dropdownParent:modalParent,
            language:{
                noResults:function(){
                    return 'Sekolah tidak ditemukan';
                },
                searching:function(){
                    return 'Mencari...';
                }
            }
        });
        $('#visitOfficerSelect').prop('disabled',true).select2({
            width:'100%',
            placeholder:'Pilih Petugas Monev',
            allowClear:true,
            closeOnSelect:false,
            dropdownParent:modalParent,
            language:{
                noResults:function(){
                    return 'Petugas tidak ditemukan';
                },
                searching:function(){
                    return 'Mencari...';
                }
            }
        });
        $('#visitOfficerSelect').on('select2:opening',function(e){
            if($(this).prop('disabled')){
                e.preventDefault();
            }
        });
    }
    function loadRegions(){
        const select=$('#visitRegion');
        select.prop('disabled',true);
        select.html('<option value="">Memuat wilayah...</option>').trigger('change');
        if(!URLS.regions){
            select.html('<option value="">URL wilayah tidak tersedia</option>').trigger('change');
            notify('Endpoint data wilayah tidak tersedia.','error');
            return;
        }
        request(URLS.regions).done(function(res){
            if(!res||res.status===false||res.success===false){
                select.html('<option value="">Wilayah gagal dimuat</option>').trigger('change');
                notify(res&&res.message?res.message:'Data wilayah gagal dimuat.','error');
                return;
            }
            const rows=Array.isArray(res.data)?res.data:[];
            let html='<option value="">Pilih Wilayah</option>';
            $.each(rows,function(index,row){
                html+='<option value="'+escapeAttr(row.id)+'">'+escapeHtml(row.name||'-')+'</option>';
            });
            select.html(html).prop('disabled',false).trigger('change');
            if(res.csrfHash){
                window.VISITS_CSRF_HASH=res.csrfHash;
            }
        }).fail(function(xhr){
            select.html('<option value="">Wilayah gagal dimuat</option>').trigger('change');
            let message='Gagal memuat data wilayah.';
            if(xhr.responseJSON&&xhr.responseJSON.message){
                message=xhr.responseJSON.message;
            }
            notify(message,'error');
            console.error('REGIONS ERROR:',xhr.responseText);
        });
    }
    function loadSchools(regionId){
        const select=$('#visitSchool');
        const officer=$('#visitOfficerSelect');
        schools=[];
        select.prop('disabled',true);
        officer.prop('disabled',true).val(null).trigger('change');
        $('#visitSchoolInfo').hide();
        $('#visitSelectedTeam').html('<span class="visit-no-team">Belum ada petugas dipilih.</span>');
        $('#visitSelectedCount').text('0');
        select.html('<option value="">Memuat sekolah...</option>').trigger('change');
        if(!regionId){
            select.html('<option value="">Pilih wilayah terlebih dahulu</option>').prop('disabled',true).trigger('change');
            return;
        }
        if(!URLS.schools){
            select.html('<option value="">URL sekolah tidak tersedia</option>').prop('disabled',true).trigger('change');
            notify('Endpoint data sekolah tidak tersedia.','error');
            return;
        }
        request(URLS.schools,{
            data:{
                region_id:regionId
            }
        }).done(function(res){
            if(!res||res.status===false||res.success===false){
                select.html('<option value="">Sekolah gagal dimuat</option>').prop('disabled',true).trigger('change');
                notify(res&&res.message?res.message:'Data sekolah gagal dimuat.','error');
                return;
            }
            schools=Array.isArray(res.data)?res.data:[];
            let html='<option value="">Pilih Sekolah</option>';
            $.each(schools,function(index,school){
                const schoolName=school.name||school.school_name||'-';
                const optionText=(school.npsn?school.npsn+' - ':'')+schoolName;
                html+='<option value="'+escapeAttr(school.id)+'">'+escapeHtml(optionText)+'</option>';
            });
            if(schools.length){
                select.html(html).prop('disabled',false).trigger('change');
            }else{
                select.html('<option value="">Tidak ada sekolah pada wilayah ini</option>').prop('disabled',true).trigger('change');
                officer.val(null).prop('disabled',true).trigger('change');
                notify('Belum ada sekolah yang tersedia pada wilayah tersebut.','warning');
            }
            if(res.csrfHash){
                window.VISITS_CSRF_HASH=res.csrfHash;
            }
        }).fail(function(xhr){
            select.html('<option value="">Sekolah gagal dimuat</option>').prop('disabled',true).trigger('change');
            officer.val(null).prop('disabled',true).trigger('change');
            let message='Gagal memuat data sekolah.';
            if(xhr.responseJSON&&xhr.responseJSON.message){
                message=xhr.responseJSON.message;
            }
            notify(message,'error');
            console.error('SCHOOLS ERROR:',xhr.responseText);
        });
    }
    function loadOfficers(){
        const select=$('#visitOfficerSelect');
        officers=[];
        select.prop('disabled',true);
        select.empty().trigger('change');
        if(!URLS.officers){
            select.html('<option value="">URL petugas tidak tersedia</option>').trigger('change');
            notify('Endpoint data petugas tidak tersedia.','error');
            return;
        }
        request(URLS.officers).done(function(res){
            if(!res||res.status===false||res.success===false){
                select.html('<option value="">Petugas gagal dimuat</option>').trigger('change');
                notify(res&&res.message?res.message:'Data petugas gagal dimuat.','error');
                return;
            }
            officers=Array.isArray(res.data)?res.data:[];
            let html='';
            $.each(officers,function(index,officer){
                html+='<option value="'+escapeAttr(officer.id)+'">'+escapeHtml(officer.name||'-')+'</option>';
            });
            select.html(html).prop('disabled',false).val(null).trigger('change');
            if(!officers.length){
                notify('Belum ada petugas yang tersedia.','warning');
            }
            if(res.csrfHash){
                window.VISITS_CSRF_HASH=res.csrfHash;
            }
        }).fail(function(xhr){
            select.html('<option value="">Petugas gagal dimuat</option>').trigger('change');
            let message='Gagal memuat data petugas.';
            if(xhr.responseJSON&&xhr.responseJSON.message){
                message=xhr.responseJSON.message;
            }
            notify(message,'error');
            console.error('OFFICERS ERROR:',xhr.responseText);
        });
    }
    function updateSelectedTeam(){
        const ids=$('#visitOfficerSelect').val()||[];
        $('#visitSelectedCount').text(ids.length);
        if(!ids.length){
            $('#visitSelectedTeam').html('<span class="visit-no-team">Belum ada petugas dipilih.</span>');
            return;
        }
        let html='';
        $.each(ids,function(index,id){
            const option=$('#visitOfficerSelect option[value="'+escapeAttr(id)+'"]');
            const name=option.length?option.text():'Petugas';
            html+='<span class="visit-selected-member">'+escapeHtml(name)+'</span>';
        });
        $('#visitSelectedTeam').html(html);
    }
    function showSchoolInfo(){
        const id=String($('#visitSchool').val()||'');
        let school=null;
        $.each(schools,function(index,item){
            if(String(item.id)===id){
                school=item;
                return false;
            }
        });
        if(!school){
            $('#visitSchoolInfo').hide();
            return;
        }
        const schoolName=school.name||school.school_name||'-';
        $('#visitSchoolName').text(schoolName);
        $('#visitSchoolMeta').text('NPSN '+(school.npsn||'-')+' • '+(school.level||'-')+' • '+(school.region_name||school.district||'-'));
        $('#visitSchoolInfo').show();
    }
    function openAddModal(){
        $('#visitAddForm')[0].reset();
        $('#visitSchoolInfo').hide();
        $('#visitRegion').val(null).trigger('change');
        $('#visitSchool').prop('disabled',true).html('<option value="">Pilih wilayah terlebih dahulu</option>').trigger('change');
        $('#visitOfficerSelect').val(null).html('<option value="">Pilih sekolah terlebih dahulu</option>').prop('disabled',true).trigger('change');
        $('#visitDate').val(today()).prop('disabled',true);
        const modalElement=document.getElementById('visitAddModal');
        if(modalElement&&typeof bootstrap!=='undefined'){
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
        loadRegions();
    }
    function saveVisit(){
        const regionId=$('#visitRegion').val();
        const schoolId=$('#visitSchool').val();
        const visitDate=$('#visitDate').val();
        const userIds=$('#visitOfficerSelect').val()||[];
        if(!regionId){
            notify('Silakan pilih wilayah.','warning');
            return;
        }
        if(!schoolId){
            notify('Silakan pilih sekolah.','warning');
            return;
        }
        if(!visitDate){
            notify('Tanggal Monev wajib diisi.','warning');
            return;
        }
        if(!userIds.length){
            notify('Minimal satu petugas harus dipilih.','warning');
            return;
        }
        const btn=$('#btnSaveVisit');
        const old=btn.html();
        btn.prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');
        const data={
            region_id:regionId,
            school_id:schoolId,
            visit_date:visitDate,
            'user_ids[]':userIds
        };
        if(window.VISITS_CSRF_NAME&&window.VISITS_CSRF_HASH){
            data[window.VISITS_CSRF_NAME]=window.VISITS_CSRF_HASH;
        }
        request(URLS.create,{
            type:'POST',
            data:data,
            traditional:true
        }).done(function(res){
            if(res&&res.csrfHash){
                window.VISITS_CSRF_HASH=res.csrfHash;
            }
            if(!res||res.status===false||res.success===false){
                notify(res&&res.message?res.message:'Kegiatan Monev gagal dibuat.','error');
                return;
            }
            const modalElement=document.getElementById('visitAddModal');
            if(modalElement&&typeof bootstrap!=='undefined'){
                bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            }
            notify(res.message||'Kegiatan Monev berhasil dibuat.','success');
            $('#visitRegion').val(null).trigger('change');
            $('#visitSchool').val(null).trigger('change');
            $('#visitOfficerSelect').val(null).trigger('change');
            updateSelectedTeam();
            loadVisits();
        }).fail(function(xhr){
            let message='Gagal membuat kegiatan Monev.';
            if(xhr.responseJSON&&xhr.responseJSON.message){
                message=xhr.responseJSON.message;
            }
            notify(message,'error');
            console.error('CREATE ERROR:',xhr.responseText);
        }).always(function(){
            btn.prop('disabled',false).html(old);
        });
    }
    function confirmDelete(id,school){
        $('#deleteVisitId').val(id);
        $('#deleteVisitSchool').text(school||'-');
        const modalElement=document.getElementById('deleteVisitModal');
        if(modalElement&&typeof bootstrap!=='undefined'){
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    }
    function deleteVisit(){
        const id=$('#deleteVisitId').val();
        if(!id){
            notify('ID kegiatan Monev tidak ditemukan.','error');
            return;
        }
        const btn=$('#btnConfirmDeleteVisit');
        const old=btn.html();
        btn.prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menghapus...');
        const data={};
        if(window.VISITS_CSRF_NAME&&window.VISITS_CSRF_HASH){
            data[window.VISITS_CSRF_NAME]=window.VISITS_CSRF_HASH;
        }
        request(URLS.delete+'/'+id,{
            type:'POST',
            data:data
        }).done(function(res){
            if(res&&res.csrfHash){
                window.VISITS_CSRF_HASH=res.csrfHash;
            }
            if(!res||res.status===false||res.success===false){
                notify(res&&res.message?res.message:'Gagal menghapus kegiatan Monev.','error');
                return;
            }
            const modalElement=document.getElementById('deleteVisitModal');
            if(modalElement&&typeof bootstrap!=='undefined'){
                bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            }
            notify(res.message||'Kegiatan Monev berhasil dihapus.','success');
            loadVisits();
        }).fail(function(xhr){
            let message='Gagal menghapus kegiatan Monev.';
            if(xhr.responseJSON&&xhr.responseJSON.message){
                message=xhr.responseJSON.message;
            }
            notify(message,'error');
            console.error('DELETE ERROR:',xhr.responseText);
        }).always(function(){
            btn.prop('disabled',false).html(old);
        });
    }
    function today(){
        const d=new Date();
        return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');
    }
    function formatDate(value){
        if(!value){
            return '-';
        }
        const match=String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
        return match?match[3]+'/'+match[2]+'/'+match[1]:value;
    }
    function baseVisitUrl(path){
        return window.location.origin+'/'+String(path).replace(/^\/+/,'');
    }
    function escapeHtml(value){
        return $('<div>').text(value==null?'':value).html();
    }
    function escapeAttr(value){
        return String(value==null?'':value).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#039;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function renderError(message){
        $('#visitTableBody').html('<tr><td colspan="8" class="text-center text-danger py-5"><i class="fas fa-exclamation-circle me-2"></i>'+escapeHtml(message)+'</td></tr>');
    }
    $(document).ready(function(){
        loadVisits();
        $('#visitAddModal').on('shown.bs.modal', function () {
            initVisitSelect2();
        });
        $('#btnAddVisit,#btnEmptyAddVisit').on('click',openAddModal);
        $('#btnRefreshVisit').on('click',loadVisits);
        $('#searchVisit').on('input',function(){
            clearTimeout(searchTimer);
            searchTimer=setTimeout(loadVisits,400);
        });
        $('#filterStatus').on('change',loadVisits);
       $('#visitRegion').on('change',function(){
            const regionId=$(this).val();
            const school=$('#visitSchool');
            const officer=$('#visitOfficerSelect');
            school.prop('disabled',true).val(null).trigger('change');
            officer.prop('disabled',true).val(null).trigger('change');
            updateSelectedTeam();
            $('#visitSchoolInfo').hide();
            if(!regionId){
                school.html('<option value="">Pilih wilayah terlebih dahulu</option>').trigger('change');
                return;
            }
            loadSchools(regionId);
            school.prop('disabled',false);
        });
        $('#visitSchool').on('change',function(){
            const schoolId=$(this).val();
            const officer=$('#visitOfficerSelect');
            const date=$('#visitDate');
            if(!schoolId){
                officer.val(null).html('<option value="">Pilih sekolah terlebih dahulu</option>').prop('disabled',true).trigger('change');
                date.val('').prop('disabled',true);
                $('#visitSchoolInfo').hide();
                return;
            }
            loadOfficers();
            officer.prop('disabled',false);
            date.val('').prop('disabled',true);
            const school=schools.find(function(item){
                return String(item.id)===String(schoolId);
            });
            if(school){
                $('#visitSchoolName').text(school.school_name||school.name||'-');
                $('#visitSchoolMeta').text((school.npsn||'-')+' • '+(school.level||'-'));
                $('#visitSchoolInfo').show();
            }
        });
        $('#visitOfficerSelect').on('change',function(){
            const userIds=$(this).val()||[];
            $('#visitDate').prop('disabled',userIds.length===0);
            if(userIds.length===0){
                $('#visitDate').val('');
            }
        });
        $('#visitOfficerSelect').on('change',updateSelectedTeam);
        $(document).on('click','.btn-delete-visit',function(){
            confirmDelete($(this).data('id'),$(this).data('school'));
        });
        $('#visitAddForm').on('submit',function(e){
            e.preventDefault();
            saveVisit();
        });
        $('#btnConfirmDeleteVisit').on('click',deleteVisit);
        $('#visitAddModal').on('hidden.bs.modal',function(){
            $('#visitAddForm')[0].reset();
            $('#visitRegion').val(null).trigger('change');
            $('#visitSchool').val(null).prop('disabled',true).html('<option value="">Pilih wilayah terlebih dahulu</option>').trigger('change');
            $('#visitOfficerSelect').val(null).trigger('change');
            $('#visitSchoolInfo').hide();
        });
    });
    $(document).on('click','.btn-edit-visit',function(e){
    e.preventDefault();
    const visitId=$(this).data('id');
    if(!visitId){
        notify('ID kegiatan Monev tidak ditemukan.','error');
        return;
    }
    openEditVisitModal(visitId);
});

    function openEditVisitModal(visitId){
        
        const modalElement=document.getElementById('visitEditModal');
        if(!modalElement){
            notify('Modal Edit belum tersedia.','error');
            return;
        }
        initEditVisitSelect2();
        $('#editVisitId').val(visitId);
        $('#editVisitRegion').val(null).trigger('change');
        $('#editVisitSchool').prop('disabled',true).html('<option value="">Pilih wilayah terlebih dahulu</option>').trigger('change');
        $('#editVisitOfficerSelect').prop('disabled',true).val(null).trigger('change');
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
        request(URLS.edit+'/'+visitId,{
            type:'GET'
        }).done(function(res){
            if(res&&res.csrfHash){
                window.VISITS_CSRF_HASH=res.csrfHash;
            }
            if(!res||res.status===false){
                notify(res&&res.message?res.message:'Data kegiatan gagal dimuat.','error');
                return;
            }
            const data=res.data||{};
            const userIds=Array.isArray(data.user_ids)?data.user_ids.map(String):[];
            $('#editVisitId').val(data.id||visitId);
            $('#editVisitDate').val(data.visit_date||'');
            loadEditRegions(data.region_id,data.school_id,userIds,data.visit_date);
        }).fail(function(xhr){
            notify(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Gagal mengambil data kegiatan Monev.','error');
        });
    }
    function loadEditRegions(regionId,schoolId,userIds,visitDate){
        const select=$('#editVisitRegion');
        select.prop('disabled',true).html('<option value="">Memuat wilayah...</option>').trigger('change');
        request(URLS.regions,{
            type:'GET'
        }).done(function(res){
            if(!res||res.status===false){
                notify(res&&res.message?res.message:'Data wilayah gagal dimuat.','error');
                return;
            }
            const rows=Array.isArray(res.data)?res.data:[];
            let html='<option value="">Pilih Wilayah</option>';
            $.each(rows,function(index,row){
                html+='<option value="'+escapeAttr(row.id)+'">'+escapeHtml(row.name||row.region_name||'-')+'</option>';
            });
            select.html(html).prop('disabled',false).val(String(regionId)).trigger('change');
            loadEditSchools(regionId,schoolId,userIds,visitDate);
        }).fail(function(xhr){
            notify(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Gagal memuat wilayah.','error');
        });
    }
    function loadEditSchools(regionId,schoolId,userIds,visitDate){
        const select=$('#editVisitSchool');
        select.prop('disabled',true).html('<option value="">Memuat sekolah...</option>').trigger('change');
        request(URLS.schools,{
            type:'GET',
            data:{
                region_id:regionId,
                edit_visit_id:$('#editVisitId').val()
            }
        }).done(function(res){
            if(!res||res.status===false){
                notify(res&&res.message?res.message:'Data sekolah gagal dimuat.','error');
                return;
            }
            const rows=Array.isArray(res.data)?res.data:[];
            let html='<option value="">Pilih Sekolah</option>';
            $.each(rows,function(index,row){
                const id=String(row.id);
                const name=row.school_name||row.name||'-';
                const text=(row.npsn?row.npsn+' - ':'')+name;
                html+='<option value="'+escapeAttr(id)+'">'+escapeHtml(text)+'</option>';
            });
            select.html(html).prop('disabled',false);
            if(schoolId){
                select.val(String(schoolId)).trigger('change.select2');
            }else{
                select.val(null).trigger('change.select2');
            }
            if(schoolId&&userIds.length){
                loadEditOfficers(userIds,visitDate);
            }
            editChangingRegion=false;
        }).fail(function(xhr){
            notify(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Gagal memuat sekolah.','error');
        });
    }
    function loadEditOfficers(userIds,visitDate){
        const select=$('#editVisitOfficerSelect');
        select.prop('disabled',true).html('').trigger('change');
        request(URLS.officers,{type:'GET'}).done(function(res){
            if(!res||res.status===false){
                notify(res&&res.message?res.message:'Data petugas gagal dimuat.','error');
                return;
            }
            const rows=Array.isArray(res.data)?res.data:[];
            let html='';
            $.each(rows,function(index,row){
                html+='<option value="'+escapeAttr(row.id)+'">'+escapeHtml(row.name||row.full_name||'-')+'</option>';
            });
            select.html(html);
            select.val(userIds.map(String)).prop('disabled',false);
            $('#editVisitDate').prop('disabled',false).val(visitDate||'');
        
        }).fail(function(xhr){
            notify(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Gagal memuat petugas.','error');
        });
    }

   $('#editVisitRegion').on('change',function(){
        const regionId=$(this).val();
        const school=$('#editVisitSchool');
        if(!regionId){
            school.prop('disabled',true).html('<option value="">Pilih wilayah terlebih dahulu</option>').trigger('change.select2');
            return;
        }
        school.prop('disabled',true).html('<option value="">Memuat sekolah...</option>').trigger('change.select2');
        loadEditSchools(regionId,null,[]);
    });
    $('#editVisitSchool').on('change',function(){
        const schoolId=$(this).val();
        const officer=$('#editVisitOfficerSelect');
        if(!schoolId){
            return;
        }
        officer.prop('disabled',false);
    });
    $('#editVisitOfficerSelect').on('change',function(){
        const ids=$(this).val()||[];
        $('#editVisitDate').prop('disabled',ids.length===0);
    });
    $('#editVisitForm').on('submit',function(e){
        e.preventDefault();
        updateVisit();
    });
    function updateVisit(){
    console.log('UPDATE VISIT DIPANGGIL');

    const formData=new FormData();
    formData.append('visit_id',$('#editVisitId').val()||'');
    formData.append('region_id',$('#editVisitRegion').val()||'');
    formData.append('school_id',$('#editVisitSchool').val()||'');
    formData.append('visit_date',$('#editVisitDate').val()||'');

    const userIds=$('#editVisitOfficerSelect').val()||[];

    console.log('DATA UPDATE:',{
        visit_id:$('#editVisitId').val(),
        region_id:$('#editVisitRegion').val(),
        school_id:$('#editVisitSchool').val(),
        visit_date:$('#editVisitDate').val(),
        user_ids:userIds
    });

    userIds.forEach(function(id){
        formData.append('user_ids[]',id);
    });

    formData.append(VISITS_CSRF_NAME,VISITS_CSRF_HASH);

    $('#btnUpdateVisit').prop('disabled',true);

    $.ajax({
        url:VISITS_URLS.update,
        type:'POST',
        data:formData,
        processData:false,
        contentType:false,
        dataType:'json'
    }).done(function(res){
        console.log('UPDATE RESPONSE:',res);

        if(res&&res.csrfHash){
            window.VISITS_CSRF_HASH=res.csrfHash;
        }

        if(!res||res.status===false){
            notify(res&&res.message?res.message:'Data gagal diperbarui.','error');
            return;
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('visitEditModal')).hide();
        notify(res.message||'Kegiatan Monev berhasil diperbarui.','success');
        loadVisits();
    }).fail(function(xhr){
        console.error('UPDATE VISIT ERROR:',xhr.responseText);
        notify(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Gagal memperbarui kegiatan Monev.','error');
    }).always(function(){
        $('#btnUpdateVisit').prop('disabled',false);
    });
}
    function initEditVisitSelect2(){
    const modalParent=$('#visitEditModal');
    $('#editVisitRegion').select2({
        width:'100%',
        placeholder:'Pilih Wilayah',
        allowClear:true,
        dropdownParent:modalParent,
        language:{
            noResults:function(){
                return 'Wilayah tidak ditemukan';
            },
            searching:function(){
                return 'Mencari...';
            }
        }
    });
    $('#editVisitSchool').select2({
        width:'100%',
        placeholder:'Pilih Sekolah',
        allowClear:true,
        dropdownParent:modalParent,
        language:{
            noResults:function(){
                return 'Sekolah tidak ditemukan';
            },
            searching:function(){
                return 'Mencari...';
            }
        }
    });
    $('#editVisitOfficerSelect').select2({
        width:'100%',
        placeholder:'Pilih Petugas Monev',
        allowClear:true,
        closeOnSelect:false,
        dropdownParent:modalParent,
        language:{
            noResults:function(){
                return 'Petugas tidak ditemukan';
            },
            searching:function(){
                return 'Mencari...';
            }
        }
    });
    }
    $(document).on('submit','#visitEditForm',function(e){
    e.preventDefault();
    console.log('FORM EDIT SUBMIT');
    updateVisit();
});
})();