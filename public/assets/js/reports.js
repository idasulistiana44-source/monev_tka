document.addEventListener('DOMContentLoaded', function() {
    loadReportData();
    document.getElementById('btnApplyFilter').addEventListener('click', loadReportData);
    document.getElementById('btnResetFilter').addEventListener('click', function() {
        document.getElementById('filterForm').reset();
        document.getElementById('filterFollowupStatus').value = '';
        loadReportData();
    });
    document.getElementById('filterFollowupStatus').addEventListener('change', loadReportData);
    document.getElementById('btnExportExcel').addEventListener('click', exportExcelAjax);
    document.getElementById('btnExportPdf').addEventListener('click', exportPdfAjax);
});

function getFilterFormData() {
    const formData = new FormData(document.getElementById('filterForm'));
    const followupStatus = document.getElementById('filterFollowupStatus').value;
    if (followupStatus) formData.append('followup_status', followupStatus);
    return formData;
}

function loadReportData() {
    const formData = getFilterFormData();
    fetch(window.REPORT_URLS.data, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status) {
            renderAspects(res.aspects);
            renderFollowups(res.followups);
        } else {
            showToast('error', 'Gagal memuat data laporan');
        }
    })
    .catch(() => showToast('error', 'Terjadi kesalahan sistem'));
}

function renderAspects(data) {
    const tbody = document.getElementById('aspectTableBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Data aspek tidak ditemukan</td></tr>';
        return;
    }
    let html = '';
    data.forEach(item => {
        const sangated = parseInt(item.sangated_count) || 0;
        const baik = parseInt(item.baik_count) || 0;
        const cukup = parseInt(item.cukup_count) || 0;
        const kurang = parseInt(item.kurang_count) || 0;
        const maxVal = Math.max(sangated, baik, cukup, kurang);
        let domText = '-', domClass = 'bg-secondary';
        if (maxVal > 0) {
            if (maxVal === sangated) { domText = 'Sangat Memadai'; domClass = 'bg-success'; }
            else if (maxVal === baik) { domText = 'Baik'; domClass = 'bg-primary'; }
            else if (maxVal === cukup) { domText = 'Cukup'; domClass = 'bg-warning text-dark'; }
            else if (maxVal === kurang) { domText = 'Kurang Memadai'; domClass = 'bg-danger'; }
        }
        html += `<tr>
            <td><strong>${item.aspect_name}</strong></td>
            <td class="text-center"><span class="badge bg-light text-dark border">${sangated}</span></td>
            <td class="text-center"><span class="badge bg-light text-dark border">${baik}</span></td>
            <td class="text-center"><span class="badge bg-light text-dark border">${cukup}</span></td>
            <td class="text-center"><span class="badge bg-light text-dark border">${kurang}</span></td>
            <td class="text-center"><span class="badge ${domClass}">${domText}</span></td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function renderFollowups(data) {
    const tbody = document.getElementById('followupTableBody');
    if (!data || data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data tindak lanjut</td></tr>';
        return;
    }
    let html = '';
    data.forEach((item, index) => {
        html += `<tr>
            <td>${index + 1}</td>
            <td><strong>${item.school_name}</strong></td>
            <td><span class="badge bg-light text-primary border">${item.aspect_name || '-'}</span></td>
            <td>${item.finding_text || '-'}</td>
            <td>${item.recommendation || '-'}</td>
            <td>
                <select class="form-select status-select-sm" data-status="${item.status}" onchange="changeFollowupStatus(${item.id}, this)">
                    <option value="BELUM" ${item.status === 'BELUM' ? 'selected' : ''}>Belum</option>
                    <option value="PROSES" ${item.status === 'PROSES' ? 'selected' : ''}>Proses</option>
                    <option value="SELESAI" ${item.status === 'SELESAI' ? 'selected' : ''}>Selesai</option>
                </select>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function changeFollowupStatus(id, selectEl) {
    const newStatus = selectEl.value;
    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', newStatus);
    fetch(window.REPORT_URLS.updateStatus, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if (res.status) {
            selectEl.setAttribute('data-status', newStatus);
            showToast('success', res.message);
        } else {
            showToast('error', res.message);
            loadReportData();
        }
    })
    .catch(() => showToast('error', 'Gagal memperbarui status'));
}

function exportExcelAjax() {
    const btn = document.getElementById('btnExportExcel');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
    fetch(window.REPORT_URLS.exportExcel, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: getFilterFormData()
    })
    .then(res => res.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-excel me-1"></i>Ekspor Excel';
        if (res.status) {
            const blob = new Blob([res.html], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = res.filename;
            link.click();
            showToast('success', 'Excel berhasil diunduh!');
        } else {
            showToast('error', 'Gagal mengekspor Excel');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-excel me-1"></i>Ekspor Excel';
        showToast('error', 'Terjadi kesalahan sistem');
    });
}

function exportPdfAjax() {
    const btn = document.getElementById('btnExportPdf');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
    fetch(window.REPORT_URLS.exportPdf, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: getFilterFormData()
    })
    .then(res => res.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-pdf me-1"></i>Ekspor PDF';
        if (res.status) {
            const byteCharacters = atob(res.filebase64);
            const byteNumbers = new Array(byteCharacters.length);
            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);
            const blob = new Blob([byteArray], { type: 'application/pdf' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = res.filename;
            link.click();
            showToast('success', 'PDF berhasil diunduh!');
        } else {
            showToast('error', 'Gagal mengekspor PDF');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-pdf me-1"></i>Ekspor PDF';
        showToast('error', 'Terjadi kesalahan sistem');
    });
}

function showToast(icon, title) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}