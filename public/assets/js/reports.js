(function(){
'use strict';
const BASE_URL=String(window.BASE_URL||window.location.origin).replace(/\/+$/,'');
const REPORTS_URLS={
    regions:BASE_URL+'/reports/regions',
    data:BASE_URL+'/reports/data',
    exportExcel:BASE_URL+'/reports/export-excel',
    pdf:BASE_URL+'/reports/pdf'
};
function escapeHtml(value){
    return $('<div>').text(value??'').html();
}
function loadRegions(){
    $.ajax({
        url:REPORTS_URLS.regions,
        type:'GET',
        dataType:'json'
    }).done(function(res){
        if(!res||res.status===false){
            notify(res?.message||'Gagal memuat wilayah.','error');
            return;
        }
        const select=$('#reportRegion');
        select.html('<option value="">Semua Wilayah</option>');
        (res.data||[]).forEach(function(row){
            select.append('<option value="'+escapeHtml(row.id)+'">'+escapeHtml(row.name)+'</option>');
        });
    }).fail(function(xhr){
        console.error('REPORT REGION ERROR:',xhr.responseText);
        notify('Gagal memuat wilayah.','error');
    });
}
function getFilters(){
    return {
        keyword:$('#reportKeyword').val()||'',
        region_id:$('#reportRegion').val()||'',
        status:$('#reportStatus').val()||'',
        date_from:$('#reportDateFrom').val()||'',
        date_to:$('#reportDateTo').val()||''
    };
}
function loadReports(){
    const filters=getFilters();
    $('#reportsTableBody').html('<tr><td colspan="8" class="text-center py-5"><i class="fas fa-spinner fa-spin me-2"></i>Memuat data...</td></tr>');
    $.ajax({
        url:REPORTS_URLS.data,
        type:'GET',
        data:filters,
        dataType:'json'
    }).done(function(res){
        if(!res||res.status===false){
            $('#reportsTableBody').html('<tr><td colspan="8" class="text-center text-danger py-5">Gagal memuat data.</td></tr>');
            notify(res?.message||'Gagal memuat data laporan.','error');
            return;
        }
        renderReports(res.data||[]);
    }).fail(function(xhr){
        console.error('REPORT DATA ERROR:',xhr.responseText);
        $('#reportsTableBody').html('<tr><td colspan="8" class="text-center text-danger py-5">Gagal memuat data laporan.</td></tr>');
        notify(xhr.responseJSON?.message||'Gagal memuat data laporan.','error');
    });
}
function renderReports(data){
    const tbody=$('#reportsTableBody');
    $('#reportTotal').text(data.length);
    if(!data.length){
        tbody.html('<tr><td colspan="8" class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x mb-2"></i><div>Tidak ada data Monev.</div></td></tr>');
        return;
    }
    let html='';
    data.forEach(function(row,index){
        const statusClass={
            DRAFT:'secondary',
            IN_PROGRESS:'warning',
            COMPLETED:'success'
        }[row.status]||'secondary';
        const statusText={
            DRAFT:'Draft',
            IN_PROGRESS:'Sedang Berjalan',
            COMPLETED:'Selesai'
        }[row.status]||row.status;
        html+='<tr>';
        html+='<td>'+(index+1)+'</td>';
        html+='<td><strong>'+escapeHtml(row.region_name||'-')+'</strong></td>';
        html+='<td><div class="fw-semibold">'+escapeHtml(row.school_name||'-')+'</div><small class="text-muted">'+escapeHtml(row.level||'')+'</small></td>';
        html+='<td>'+escapeHtml(row.npsn||'-')+'</td>';
        html+='<td>'+formatDate(row.visit_date)+'</td>';
        html+='<td>'+escapeHtml(row.member_names||'-')+'</td>';
        html+='<td><span class="badge bg-'+statusClass+'">'+escapeHtml(statusText)+'</span></td>';
        html+='<td><button type="button" class="btn btn-sm btn-danger btn-report-pdf" data-id="'+escapeHtml(row.id)+'"><i class="fas fa-file-pdf me-1"></i>PDF</button></td>';
        html+='</tr>';
    });
    tbody.html(html);
}
function formatDate(value){
    if(!value)return '-';
    const parts=String(value).split('-');
    if(parts.length!==3)return escapeHtml(value);
    return parts[2]+'-'+parts[1]+'-'+parts[0];
}
function exportExcel(){
    const filters=getFilters();
    const query=$.param(filters);
    window.location.href=REPORTS_URLS.exportExcel+'?'+query;
}
$(document).on('click','#btnSearchReport',function(){
    loadReports();
});

$(document).on('click','#btnExportReport',function(){
    exportExcel();
});
$(document).on('click','.btn-report-pdf',function(){
    const id=$(this).data('id');
    if(!id)return;
    window.open(REPORTS_URLS.pdf+'/'+encodeURIComponent(id),'_blank');
});
$(document).on('keypress','#reportKeyword',function(e){
    if(e.which===13){
        e.preventDefault();
        loadReports();
    }
});
$(document).ready(function(){
    loadRegions();
    loadReports();
});
})();