(function(){
    'use strict';
    const URLS=window.VISITS_URLS||{};
    let schools=[];
    let officers=[];
    let selectedOfficers={};
    let searchTimer=null;

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
            if(!res||res.status===false){
                renderError(res&&res.message?res.message:'Data Monev gagal dimuat.');
                notify(res&&res.message?res.message:'Data Monev gagal dimuat.','error');
                return;
            }
            renderVisits(res.data||[]);
        }).fail(function(xhr){
            let message='Gagal memuat data kegiatan Monev.';
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
            $('#visitTableBody').html('<tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-clipboard-check fa-2x mb-3 d-block"></i>Belum ada kegiatan Monev.</td></tr>');
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
            if(row.status==='DRAFT'){
                action='<a href="'+baseVisitUrl('visits/form/'+row.id)+'" class="btn btn-sm btn-primary"><i class="fas fa-play me-1"></i>Mulai</a>';
                action+='<button type="button" class="btn btn-sm btn-outline-danger btn-delete-visit" data-id="'+escapeAttr(row.id)+'" data-school="'+escapeAttr(row.school_name||'')+'"><i class="fas fa-trash"></i></button>';
            }else if(row.status==='IN_PROGRESS'){
                action='<a href="'+baseVisitUrl('visits/form/'+row.id)+'" class="btn btn-sm btn-primary"><i class="fas fa-edit me-1"></i>Lanjutkan</a>';
            }else{
                action='<a href="'+baseVisitUrl('visits/form/'+row.id)+'" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye me-1"></i>Lihat</a>';
            }
            html+='<tr>';
            html+='<td>'+(index+1)+'</td>';
            html+='<td><strong>'+escapeHtml(row.npsn||'-')+'</strong></td>';
            html+='<td><strong>'+escapeHtml(row.school_name||'-')+'</strong></td>';
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

    function loadSchools(){
        const select=$('#visitSchool');
        select.html('<option value="">Memuat sekolah...</option>');
        if(!URLS.schools){
            select.html('<option value="">URL sekolah tidak tersedia</option>');
            notify('Endpoint data sekolah tidak tersedia.','error');
            return;
        }
        request(URLS.schools).done(function(res){
            if(!res||res.status===false){
                select.html('<option value="">Sekolah gagal dimuat</option>');
                notify(res&&res.message?res.message:'Data sekolah gagal dimuat.','error');
                console.error('SCHOOLS ERROR RESPONSE:',res);
                return;
            }
            schools=Array.isArray(res.data)?res.data:[];
            let html='<option value="">Pilih Sekolah</option>';
            $.each(schools,function(index,school){
                const schoolName=school.name||school.school_name||'-';
                html+='<option value="'+escapeAttr(school.id)+'">'+escapeHtml(schoolName)+' - '+escapeHtml(school.npsn||'-')+'</option>';
            });
            select.html(html);
            if(!schools.length){
                select.html('<option value="">Belum ada data sekolah</option>');
                notify('Belum ada data sekolah yang tersedia.','warning');
            }
        }).fail(function(xhr){
            select.html('<option value="">Sekolah gagal dimuat</option>');
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
        select.html('<option value="">Memuat petugas...</option>');
        if(!URLS.officers){
            select.html('<option value="">URL petugas tidak tersedia</option>');
            notify('Endpoint data petugas tidak tersedia.','error');
            return;
        }
        request(URLS.officers).done(function(res){
            if(!res||res.status===false){
                select.html('<option value="">Petugas gagal dimuat</option>');
                notify(res&&res.message?res.message:'Data petugas gagal dimuat.','error');
                return;
            }
            officers=Array.isArray(res.data)?res.data:[];
            renderOfficerSelect();
            if(!officers.length){
                notify('Belum ada petugas yang tersedia.','warning');
            }
        }).fail(function(xhr){
            select.html('<option value="">Petugas gagal dimuat</option>');
            let message='Gagal memuat data petugas.';
            if(xhr.responseJSON&&xhr.responseJSON.message){
                message=xhr.responseJSON.message;
            }
            notify(message,'error');
            console.error('OFFICERS ERROR:',xhr.responseText);
        });
    }

    function renderOfficerSelect(){
        const select=$('#visitOfficerSelect');
        let html='<option value="">Pilih Petugas</option>';
        $.each(officers,function(index,officer){
            const id=String(officer.id);
            if(selectedOfficers[id]){
                return;
            }
            html+='<option value="'+escapeAttr(id)+'">'+escapeHtml(officer.name||'-')+'</option>';
        });
        select.html(html);
    }

    function addOfficer(id){
        id=String(id||'');
        if(!id){
            return;
        }
        if(selectedOfficers[id]){
            notify('Petugas tersebut sudah dipilih.','warning');
            return;
        }
        let officer=null;
        $.each(officers,function(index,item){
            if(String(item.id)===id){
                officer=item;
                return false;
            }
        });
        if(!officer){
            notify('Data petugas tidak ditemukan.','error');
            return;
        }
        selectedOfficers[id]=officer;
        updateSelectedTeam();
        renderOfficerSelect();
        $('#visitOfficerSelect').val('');
    }

    function removeOfficer(id){
        id=String(id||'');
        if(!selectedOfficers[id]){
            return;
        }
        const name=selectedOfficers[id].name||'Petugas';
        delete selectedOfficers[id];
        updateSelectedTeam();
        renderOfficerSelect();
        notify(name+' dihapus dari tim Monev.','info');
    }

    function updateSelectedTeam(){
        const ids=Object.keys(selectedOfficers);
        $('#visitSelectedCount').text(ids.length);
        if(!ids.length){
            $('#visitSelectedTeam').html('<span class="visit-no-team">Belum ada petugas dipilih.</span>');
            return;
        }
        let html='';
        $.each(ids,function(index,id){
            const officer=selectedOfficers[id];
            html+='<span class="visit-selected-member">';
            html+=escapeHtml(officer.name||'-');
            html+='<button type="button" class="btn-remove-officer" data-id="'+escapeAttr(id)+'" title="Hapus petugas"><i class="fas fa-times"></i></button>';
            html+='</span>';
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
        $('#visitSchoolMeta').text('NPSN '+(school.npsn||'-')+' • '+(school.level||'-')+' • '+(school.district||'-'));
        $('#visitSchoolInfo').show();
    }

    function openAddModal(){
        selectedOfficers={};
        $('#visitAddForm')[0].reset();
        $('#visitSchoolInfo').hide();
        $('#visitSelectedCount').text('0');
        $('#visitSelectedTeam').html('<span class="visit-no-team">Belum ada petugas dipilih.</span>');
        $('#visitDate').val(today());
        $('#visitSchool').html('<option value="">Memuat sekolah...</option>');
        $('#visitOfficerSelect').html('<option value="">Memuat petugas...</option>');
        const modalElement=document.getElementById('visitAddModal');
        if(modalElement&&typeof bootstrap!=='undefined'){
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
        loadSchools();
        loadOfficers();
    }

    // ==========================================
    // FUNGSI SAVE VISIT YANG SUDAH DIPERBAIKI
    // ==========================================
    function saveVisit(){
        const schoolId=$('#visitSchool').val();
        const visitDate=$('#visitDate').val();
        const userIds=Object.keys(selectedOfficers).map(function(id){
            return parseInt(id,10);
        }).filter(function(id){
            return id>0;
        });

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

        // Sertakan baik 'user_ids' maupun 'user_ids[]' agar terbaca sempurna oleh CI4
        const data={
            school_id:schoolId,
            visit_date:visitDate,
            user_ids:userIds,
            'user_ids[]':userIds
        };

        if(window.VISITS_CSRF_NAME&&window.VISITS_CSRF_HASH){
            data[window.VISITS_CSRF_NAME]=window.VISITS_CSRF_HASH;
        }

        console.log('USER IDS CREATE MONEV:',userIds);

        request(URLS.create,{
            type:'POST',
            data:data,
            traditional:true
        }).done(function(res){
            if(!res||res.status===false){
                notify(res&&res.message?res.message:'Kegiatan Monev gagal dibuat.','error');
                return;
            }
            const modalElement=document.getElementById('visitAddModal');
            if(modalElement&&typeof bootstrap!=='undefined'){
                bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            }
            notify(res.message||'Kegiatan Monev berhasil dibuat.','success');
            selectedOfficers={};
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
            if(!res||res.status===false){
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
        $('#btnAddVisit,#btnEmptyAddVisit').on('click',openAddModal);
        $('#btnRefreshVisit').on('click',loadVisits);
        $('#searchVisit').on('input',function(){
            clearTimeout(searchTimer);
            searchTimer=setTimeout(loadVisits,400);
        });
        $('#filterStatus').on('change',loadVisits);
        $('#visitSchool').on('change',showSchoolInfo);
        $('#visitOfficerSelect').on('change',function(){
            addOfficer($(this).val());
        });
        $(document).on('click','.btn-remove-officer',function(){
            removeOfficer($(this).data('id'));
        });
        $(document).on('click','.btn-delete-visit',function(){
            confirmDelete($(this).data('id'),$(this).data('school'));
        });
        $('#visitAddForm').on('submit',function(e){
            e.preventDefault();
            saveVisit();
        });
        $('#btnConfirmDeleteVisit').on('click',deleteVisit);
    });
})();