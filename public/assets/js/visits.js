$(document).ready(function(){
    if(!document.querySelector('.visits-page'))return;
    let visitModal=null;
    let loading=false;
    init();
    function init(){
        initModal();
        bindEvents();
        loadVisits();
    }
    function initModal(){
        const element=document.getElementById('visitAddModal');
        if(element){
            visitModal=new bootstrap.Modal(element);
        }
    }
    function bindEvents(){
        $('#btnAddVisit').on('click',function(){openAddVisit();});
        $('#btnRefreshVisit').on('click',function(){loadVisits(true);});
        $('#searchVisit').on('input',function(){loadVisits();});
        $('#filterStatus').on('change',function(){loadVisits();});
        $('#btnSaveVisit').on('click',function(){saveVisit();});
        $('#visitAssignment').on('change',function(){showAssignmentInfo($(this).val());});
        $('#visitTableBody').on('click','.btn-start-visit',function(){startVisit($(this).data('id'));});
        $('#visitTableBody').on('click','.btn-continue-visit',function(){continueVisit($(this).data('id'));});
        $('#visitTableBody').on('click','.btn-view-visit',function(){viewVisit($(this).data('id'));});
    }
    function loadVisits(showMessage=false){
        if(loading)return;
        loading=true;
        $('#visitTableBody').html('<tr><td colspan="8" class="visit-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td></tr>');
        const keyword=($('#searchVisit').val()||'').toString().trim();
        const status=($('#filterStatus').val()||'').toString().trim();
        $.ajax({
            url:BASE_URL+'visits/data',
            type:'GET',
            data:{keyword:keyword,status:status},
            dataType:'json',
            success:function(response){
                if(response.success){
                    renderVisits(response.data||[]);
                    if(showMessage)notify('Data visitasi berhasil diperbarui.','success');
                }else{
                    renderEmpty(response.message||'Data visitasi tidak tersedia.');
                    notify(response.message||'Data visitasi tidak tersedia.','error');
                }
            },
            error:function(xhr){
                renderEmpty('Data visitasi gagal dimuat.');
                notify(getAjaxError(xhr,'Data visitasi gagal dimuat.'),'error');
                console.error(xhr.responseText);
            },
            complete:function(){loading=false;}
        });
    }
    function openAddVisit(){
        const form=$('#visitAddForm');
        if(form.length&&form[0])form[0].reset();
        $('#visitAssignment').html('<option value="">Memuat assignment...</option>');
        $('#visitDate').val(new Date().toISOString().split('T')[0]);
        $('#visitAssignmentInfo').empty();
        loadAssignments();
        if(visitModal)visitModal.show();
    }
    function loadAssignments(){
        $.ajax({
            url:BASE_URL+'visits/assignments',
            type:'GET',
            dataType:'json',
            success:function(response){
                const select=$('#visitAssignment');
                select.empty();
                select.append('<option value="">Pilih Assignment</option>');
                if(response.success&&Array.isArray(response.data)&&response.data.length){
                    response.data.forEach(function(item){
                        select.append('<option value="'+escapeHtml(item.id||'')+'" data-school="'+escapeHtml(item.school_name||'')+'" data-npsn="'+escapeHtml(item.npsn||'')+'" data-level="'+escapeHtml(item.level||'')+'" data-officer="'+escapeHtml(item.officer_name||'')+'" data-date="'+escapeHtml(item.assignment_date||'')+'">'+escapeHtml(item.npsn||'-')+' - '+escapeHtml(item.school_name||'-')+' - '+escapeHtml(item.officer_name||'-')+'</option>');
                    });
                }else{
                    select.html('<option value="">Tidak ada assignment aktif</option>');
                    notify(response.message||'Tidak ada assignment aktif yang dapat dibuatkan visitasi.','warning');
                }
            },
            error:function(xhr){
                $('#visitAssignment').html('<option value="">Gagal memuat assignment</option>');
                notify(getAjaxError(xhr,'Assignment gagal dimuat.'),'error');
                console.error(xhr.responseText);
            }
        });
    }
    function showAssignmentInfo(id){
        const option=$('#visitAssignment option:selected');
        if(!id){
            $('#visitAssignmentInfo').empty();
            return;
        }
        $('#visitAssignmentInfo').html('<div class="visit-assignment-info-grid"><div><small>Sekolah</small><strong>'+escapeHtml(option.data('school')||'-')+'</strong></div><div><small>NPSN</small><strong>'+escapeHtml(option.data('npsn')||'-')+'</strong></div><div><small>Level</small><strong>'+escapeHtml(option.data('level')||'-')+'</strong></div><div><small>Petugas</small><strong>'+escapeHtml(option.data('officer')||'-')+'</strong></div><div><small>Tanggal Assignment</small><strong>'+formatDate(option.data('date'))+'</strong></div></div>');
    }
    function saveVisit(){
        const assignmentId=$('#visitAssignment').val();
        const visitDate=$('#visitDate').val();
        if(!assignmentId){
            notify('Assignment wajib dipilih.','warning');
            return;
        }
        if(!visitDate){
            notify('Tanggal visitasi wajib diisi.','warning');
            return;
        }
        const button=$('#btnSaveVisit');
        const original=button.html();
        button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');
        $.ajax({
            url:BASE_URL+'visits/store',
            type:'POST',
            data:{assignment_id:assignmentId,visit_date:visitDate},
            dataType:'json',
            success:function(response){
                if(response.success){
                    if(visitModal)visitModal.hide();
                    loadVisits();
                    notify(response.message||'Visitasi berhasil dibuat.','success');
                }else{
                    notify(response.message||'Visitasi gagal dibuat.','error');
                }
            },
            error:function(xhr){
                notify(getAjaxError(xhr,'Visitasi gagal dibuat.'),'error');
                console.error(xhr.responseText);
            },
            complete:function(){button.prop('disabled',false).html(original);}
        });
    }
    function renderVisits(data){
        const tbody=$('#visitTableBody');
        tbody.empty();
        $('#visitTotal').text(data.length);
        if(!data.length){
            renderEmpty('Belum ada data visitasi.');
            return;
        }
        data.forEach(function(item,index){
            tbody.append('<tr><td>'+(index+1)+'</td><td><strong>'+escapeHtml(item.npsn||'-')+'</strong></td><td>'+escapeHtml(item.school_name||'-')+'</td><td>'+escapeHtml(item.level||'-')+'</td><td>'+formatDate(item.visit_date)+'</td><td>'+escapeHtml(item.officer_name||'-')+'</td><td>'+getStatusBadge(item.status)+'</td><td>'+getActionButton(item)+'</td></tr>');
        });
    }
    function getActionButton(item){
        if(item.status==='DRAFT'){
            return '<button type="button" class="btn btn-sm btn-primary btn-start-visit" data-id="'+escapeHtml(item.id||'')+'"><i class="fas fa-play me-1"></i>Mulai</button>';
        }
        if(item.status==='IN_PROGRESS'){
            return '<button type="button" class="btn btn-sm btn-primary btn-continue-visit" data-id="'+escapeHtml(item.id||'')+'"><i class="fas fa-arrow-right me-1"></i>Lanjutkan</button>';
        }
        return '<button type="button" class="btn btn-sm btn-outline-primary btn-view-visit" data-id="'+escapeHtml(item.id||'')+'"><i class="fas fa-eye me-1"></i>Lihat</button>';
    }
    function startVisit(id){
        if(!id){
            notify('ID visitasi tidak valid.','error');
            return;
        }
        notify('Menyiapkan visitasi...','info');
        $.ajax({
            url:BASE_URL+'visits/start/'+id,
            type:'POST',
            dataType:'json',
            success:function(response){
                if(response.success){
                    notify(response.message||'Visitasi dimulai.','success');
                    setTimeout(function(){
                        window.location.href=response.data&&response.data.redirect?response.data.redirect:BASE_URL+'visits/instrument/'+id;
                    },500);
                }else{
                    notify(response.message||'Visitasi gagal dimulai.','error');
                }
            },
            error:function(xhr){
                notify(getAjaxError(xhr,'Visitasi gagal dimulai.'),'error');
                console.error(xhr.responseText);
            }
        });
    }
    function continueVisit(id){
        if(!id){
            notify('ID visitasi tidak valid.','error');
            return;
        }
        window.location.href=BASE_URL+'visits/instrument/'+id;
    }
    function viewVisit(id){
        if(!id){
            notify('ID visitasi tidak valid.','error');
            return;
        }
        window.location.href=BASE_URL+'visits/instrument/'+id;
    }
    function renderEmpty(message){
        $('#visitTableBody').html('<tr><td colspan="8" class="visit-empty"><i class="fas fa-folder-open me-2"></i>'+escapeHtml(message)+'</td></tr>');
        $('#visitTotal').text('0');
    }
    function getStatusBadge(status){
        if(status==='DRAFT')return '<span class="badge bg-secondary-subtle text-secondary">Draft</span>';
        if(status==='IN_PROGRESS')return '<span class="badge bg-warning-subtle text-warning-emphasis">Berlangsung</span>';
        if(status==='COMPLETED')return '<span class="badge bg-success-subtle text-success">Selesai</span>';
        if(status==='VERIFIED')return '<span class="badge bg-primary-subtle text-primary">Terverifikasi</span>';
        return '<span class="badge bg-secondary-subtle text-secondary">'+escapeHtml(status||'-')+'</span>';
    }
    function notify(message,type){
        if(typeof showGlobalAlert==='function'){
            showGlobalAlert(message,type);
        }else{
            console.log(message);
        }
    }
    function getAjaxError(xhr,fallback){
        if(xhr.responseJSON&&xhr.responseJSON.message)return xhr.responseJSON.message;
        if(xhr.responseJSON&&xhr.responseJSON.errors)return Object.values(xhr.responseJSON.errors).join(' ');
        return fallback;
    }
    function formatDate(value){
        if(!value)return '-';
        const parts=String(value).split('-');
        if(parts.length!==3)return value;
        return parts[2]+'-'+parts[1]+'-'+parts[0];
    }
    function escapeHtml(value){
        return $('<div>').text(value??'').html();
    }
});