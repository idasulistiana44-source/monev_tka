$(document).ready(function(){
    loadSchools();
    loadCity('#schoolCity');
    loadCity('#editSchoolCity');

    $('#btnAddSchool').on('click',function(){
        $('#schoolAddForm')[0].reset();
        $('#schoolDistrict').prop('disabled',true).html('<option value="">Select city first</option>');
        $('#schoolAddAlert').hide().html('');
        clearFormErrors($('#schoolAddForm'));
        loadCity('#schoolCity');
        const modal=new bootstrap.Modal(document.getElementById('schoolAddModal'));
        modal.show();
    });

    $('#schoolCity').on('change',function(){
        loadDistrict($(this).val(),'#schoolDistrict');
    });

    $('#editSchoolCity').on('change',function(){
        loadDistrict($(this).val(),'#editSchoolDistrict');
    });

    $('#schoolSearch,#schoolLevelFilter').on('input change',function(){
        loadSchools();
    });

    $('#btnSaveSchool').on('click',function(){
        saveSchool();
    });

    $('#btnUpdateSchool').on('click',function(){
        updateSchool();
    });

    $(document).on('click','#btnDeleteSchool',function(e){
        e.preventDefault();
        e.stopPropagation();
        deleteSchool();
    });
});

function loadSchools(){
    const tbody=$('#schoolTableBody');

    tbody.html('<tr><td colspan="7" class="school-loading"><div class="school-spinner"></div>Loading school data...</td></tr>');
    $('#schoolEmpty').hide();

    $.ajax({
        url:BASE_URL+'schools/data',
        type:'GET',
        data:{
            keyword:$('#schoolSearch').val()||'',
            level:$('#schoolLevelFilter').val()||''
        },
        dataType:'json',
        timeout:15000,
        success:function(response){
            if(response.success){
                renderSchools(response.data||[]);
            }else{
                renderEmpty();
                showGlobalAlert(response.message||'School data failed to load.','error');
            }
        },
        error:function(xhr){
            renderEmpty();
            showGlobalAlert(getAjaxMessage(xhr,'School data failed to load.'),'error');
            console.error(xhr.responseText);
        }
    });
}

function renderSchools(data){
    const tbody=$('#schoolTableBody');

    $('#schoolTotal').text(data.length);

    if(!data.length){
        renderEmpty();
        return;
    }

    $('#schoolEmpty').hide();

    let html='';

    $.each(data,function(index,item){
        const status=item.is_active==1
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';

        html+='<tr>';
        html+='<td>'+(index+1)+'</td>';
        html+='<td>'+escapeHtml(item.npsn||'-')+'</td>';
        html+='<td><strong>'+escapeHtml(item.school_name||'-')+'</strong></td>';
        html+='<td><span class="badge bg-primary">'+escapeHtml(item.level||'-')+'</span></td>';
        html+='<td>'+escapeHtml(item.city_name||'-')+'</td>';
        html+='<td>'+escapeHtml(item.district_name||'-')+'</td>';
        html+='<td>'+escapeHtml(item.region_name||'-')+'</td>';
        html+='<td>'+status+'</td>';
        html+='<td>';
        html+='<button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editSchool('+item.id+')"><i class="fas fa-edit"></i></button>';
        html+='<button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteSchool('+item.id+',\''+escapeJs(item.school_name||'')+'\')"><i class="fas fa-trash"></i></button>';
        html+='</td>';
        html+='</tr>';
    });

    tbody.html(html);
}

function renderEmpty(){
    $('#schoolTableBody').empty();
    $('#schoolTotal').text('0');
    $('#schoolEmpty').show();
}

function loadCity(selector,selectedId=''){
    const select=$(selector);

    if(!select.length){
        return;
    }

    select.html('<option value="">Loading city...</option>');

    $.ajax({
        url:BASE_URL+'schools/city',
        type:'GET',
        dataType:'json',
        timeout:15000,
        success:function(response){
            select.empty();
            select.append('<option value="">Select City</option>');

            if(response.success&&Array.isArray(response.data)){
                $.each(response.data,function(_,item){
                    const selected=String(item.id)===String(selectedId)?' selected':'';

                    select.append(
                        '<option value="'+item.id+'"'+selected+'>'+
                        escapeHtml(item.name||item.nama_kota||'')+
                        '</option>'
                    );
                });
            }else{
                select.html('<option value="">City not available</option>');
            }
        },
        error:function(xhr){
            select.html('<option value="">Failed to load city</option>');
            showGlobalAlert(getAjaxMessage(xhr,'Failed to load city.'),'error');
            console.error(xhr.responseText);
        }
    });
}

function loadDistrict(cityId,selector,selectedId=''){
    const select=$(selector);

    if(!select.length){
        return;
    }

    if(!cityId){
        select.prop('disabled',true).html('<option value="">Select city first</option>');
        return;
    }

    select.prop('disabled',true).html('<option value="">Loading district...</option>');

    $.ajax({
        url:BASE_URL+'schools/district',
        type:'GET',
        data:{
            city_id:cityId
        },
        dataType:'json',
        timeout:15000,
        success:function(response){
            select.empty();
            select.append('<option value="">Select District</option>');

            if(response.success&&Array.isArray(response.data)){
                $.each(response.data,function(_,item){
                    const selected=String(item.id)===String(selectedId)?' selected':'';

                    select.append(
                        '<option value="'+item.id+'"'+selected+'>'+
                        escapeHtml(item.name||item.nama_kecamatan||'')+
                        '</option>'
                    );
                });

                if(response.data.length){
                    select.prop('disabled',false);
                }else{
                    select.prop('disabled',true).html('<option value="">District not available</option>');
                }
            }else{
                select.prop('disabled',true).html('<option value="">District not available</option>');
            }
        },
        error:function(xhr){
            select.prop('disabled',true).html('<option value="">Failed to load district</option>');
            showGlobalAlert(getAjaxMessage(xhr,'Failed to load district.'),'error');
            console.error(xhr.responseText);
        }
    });
}

function saveSchool(){
    const button=$('#btnSaveSchool');
    const form=$('#schoolAddForm');

    clearFormErrors(form);

    button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url:BASE_URL+'schools/store',
        type:'POST',
        data:form.serialize(),
        dataType:'json',
        timeout:15000,
        success:function(response){
            if(response.success){
                const modalElement=document.getElementById('schoolAddModal');
                const modal=bootstrap.Modal.getInstance(modalElement);

                if(modal){
                    modal.hide();
                }

                form[0].reset();

                $('#schoolDistrict')
                    .prop('disabled',true)
                    .html('<option value="">Select city first</option>');

                showGlobalAlert(
                    response.message||'School successfully added.',
                    'success'
                );

                loadSchools();
            }else{
                showFormErrors(form,response.errors||{});

                showGlobalAlert(
                    response.message||'School data could not be saved.',
                    'error'
                );
            }
        },
        error:function(xhr){
            const response=xhr.responseJSON||{};

            showFormErrors(
                form,
                response.errors||{}
            );

            showGlobalAlert(
                response.message||getAjaxMessage(xhr,'School data could not be saved.'),
                'error'
            );

            console.error(xhr.responseText);
        },
        complete:function(){
            button
                .prop('disabled',false)
                .html('<i class="fas fa-save"></i> Save');
        }
    });
}

function editSchool(id){
    $.ajax({
        url:BASE_URL+'schools/detail/'+id,
        type:'GET',
        dataType:'json',
        timeout:15000,
        success:function(response){
            if(!response.success){
                showGlobalAlert(
                    response.message||'School data not found.',
                    'error'
                );
                return;
            }

            const item=response.data;

            $('#editSchoolId').val(item.id||'');
            $('#editNpsn').val(item.npsn||'');
            $('#editSchoolName').val(item.school_name||'');
            $('#editLevel').val(item.level||'');
            $('#editAddress').val(item.address||'');
            $('#editIsActive').val(item.is_active==1?'1':'0');

            loadCity('#editSchoolCity',item.city_id);

            loadDistrict(
                item.city_id,
                '#editSchoolDistrict',
                item.district_id
            );

            const modal=new bootstrap.Modal(
                document.getElementById('schoolEditModal')
            );

            modal.show();
        },
        error:function(xhr){
            showGlobalAlert(
                getAjaxMessage(xhr,'School data failed to load.'),
                'error'
            );

            console.error(xhr.responseText);
        }
    });
}

function updateSchool(){
    const id=$('#editSchoolId').val();
    const form=$('#schoolEditForm');
    const button=$('#btnUpdateSchool');

    if(!id){
        showGlobalAlert(
            'School ID not found.',
            'error'
        );
        return;
    }

    clearFormErrors(form);

    button
        .prop('disabled',true)
        .html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url:BASE_URL+'schools/update/'+id,
        type:'POST',
        data:form.serialize(),
        dataType:'json',
        timeout:15000,
        success:function(response){
            if(response.success){
                const modalElement=document.getElementById('schoolEditModal');
                const modal=bootstrap.Modal.getInstance(modalElement);

                if(modal){
                    modal.hide();
                }

                showGlobalAlert(
                    response.message||'School data successfully updated.',
                    'success'
                );

                loadSchools();
            }else{
                showFormErrors(
                    form,
                    response.errors||{}
                );

                showGlobalAlert(
                    response.message||'School data could not be updated.',
                    'error'
                );
            }
        },
        error:function(xhr){
            const response=xhr.responseJSON||{};

            showFormErrors(
                form,
                response.errors||{}
            );

            showGlobalAlert(
                response.message||getAjaxMessage(xhr,'School data could not be updated.'),
                'error'
            );

            console.error(xhr.responseText);
        },
        complete:function(){
            button
                .prop('disabled',false)
                .html('<i class="fas fa-save"></i> Save Changes');
        }
    });
}

function confirmDeleteSchool(id,name){
    if(!id){
        showGlobalAlert(
            'School ID not found.',
            'error'
        );
        return;
    }

    $('#deleteSchoolId').val(id);
    $('#deleteSchoolName').text(name||'-');

    const modalElement=document.getElementById('schoolDeleteModal');

    if(!modalElement){
        showGlobalAlert(
            'Delete modal not found.',
            'error'
        );
        return;
    }

    const modal=bootstrap.Modal.getOrCreateInstance(
        modalElement
    );

    modal.show();
}

function deleteSchool(){
    const id=$('#deleteSchoolId').val();
    const button=$('#btnDeleteSchool');

    console.log('DELETE SCHOOL ID:',id);

    if(!id){
        showGlobalAlert(
            'School ID not found.',
            'error'
        );
        return;
    }

    button
        .prop('disabled',true)
        .html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

    $.ajax({
        url:BASE_URL+'schools/delete/'+id,
        type:'POST',
        dataType:'json',
        timeout:15000,
        success:function(response){
            console.log('DELETE RESPONSE:',response);

            if(response.success){
                const modalElement=document.getElementById('schoolDeleteModal');
                const modal=bootstrap.Modal.getInstance(modalElement);

                if(modal){
                    modal.hide();
                }

                $('#deleteSchoolId').val('');
                $('#deleteSchoolName').text('-');

                showGlobalAlert(
                    response.message||'School successfully deleted.',
                    'success'
                );

                loadSchools();
            }else{
                showGlobalAlert(
                    response.message||'School could not be deleted.',
                    'error'
                );
            }
        },
        error:function(xhr){
            console.error('DELETE STATUS:',xhr.status);
            console.error('DELETE ERROR:',xhr.responseText);

            const response=xhr.responseJSON||{};

            showGlobalAlert(
                response.message||getAjaxMessage(xhr,'School could not be deleted.'),
                'error'
            );
        },
        complete:function(){
            button
                .prop('disabled',false)
                .html('<i class="fas fa-trash"></i> Delete');
        }
    });
}

function clearFormErrors(form){
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').text('');
}

function showFormErrors(form,errors){
    $.each(errors,function(field,message){
        const input=form.find('[name="'+field+'"]');

        input.addClass('is-invalid');

        input
            .closest('.mb-3,.mb-0,.form-group')
            .find('.invalid-feedback')
            .text(message);
    });
}

function getAjaxMessage(xhr,fallback){
    if(xhr.responseJSON&&xhr.responseJSON.message){
        return xhr.responseJSON.message;
    }

    if(xhr.status===404){
        return 'URL not found. Please check Routes.php.';
    }

    if(xhr.status===403){
        return 'Access denied.';
    }

    if(xhr.status===422){
        return 'Invalid data submitted.';
    }

    if(xhr.status===500){
        return 'A server error occurred.';
    }

    return fallback;
}

function escapeHtml(value){
    return String(value).replace(/[&<>"']/g,function(char){
        return {
            '&':'&amp;',
            '<':'&lt;',
            '>':'&gt;',
            '"':'&quot;',
            "'":'&#039;'
        }[char];
    });
}

function escapeJs(value){
    return String(value)
        .replace(/\\/g,'\\\\')
        .replace(/'/g,"\\'")
        .replace(/"/g,'&quot;');
}