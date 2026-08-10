$(function(){
    let schools=[];
    let users=[];
    let searchTimer=null;

    loadAssignments();
    loadSchools();
    loadUsers();

    $('#assignmentSearch').on('input',function(){
        clearTimeout(searchTimer);
        searchTimer=setTimeout(loadAssignments,300);
    });

    $('#assignmentStatus').on('change',loadAssignments);

    $('#btnAddAssignment').on('click',function(){
        resetAssignmentForm();
        $('#assignmentModalTitle').text('Add Assignment');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('assignmentModal')).show();
    });

    $('#assignmentSchool').on('change',function(){
        setSchoolLocation($(this).val());
    });

    $('#assignmentForm').on('submit',function(e){
        e.preventDefault();
        const id=$('#assignmentId').val();
        id ? updateAssignment() : createAssignment();
    });

    $(document).on('click','.btn-edit-assignment',function(){
        editAssignment($(this).data('id'));
    });

    $(document).on('click','.btn-delete-assignment',function(){
        $('#deleteAssignmentId').val($(this).data('id'));
        $('#deleteAssignmentSchool').text($(this).data('school'));
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteAssignmentModal')).show();
    });

    $('#btnConfirmDeleteAssignment').on('click',deleteAssignment);

    function loadAssignments(){
        $('#assignmentTableBody').html('<tr><td colspan="10" class="text-center py-5"><i class="fas fa-spinner fa-spin me-2"></i>Loading...</td></tr>');

        $.ajax({
            url:BASE_URL+'assignments/data',
            type:'GET',
            data:{keyword:$('#assignmentSearch').val(),status:$('#assignmentStatus').val()},
            dataType:'json',
            success:function(response){
                if(response.success){
                    renderAssignments(response.data||[]);
                }else{
                    showGlobalAlert(response.message||'Failed to load assignments.','error');
                }
            },
            error:function(xhr){
                showGlobalAlert(getAjaxError(xhr),'error');
            }
        });
    }

    function renderAssignments(data){
        const tbody=$('#assignmentTableBody');

        if(!data.length){
            tbody.empty();
            $('#assignmentEmpty').show();
            return;
        }

        $('#assignmentEmpty').hide();

        let html='';

        data.forEach(function(item,index){
            html+=`
                <tr>
                    <td>${index+1}</td>
                    <td><div class="assignment-school"><strong>${escapeHtml(item.school_name||'-')}</strong><small>NPSN: ${escapeHtml(item.npsn||'-')}</small></div></td>
                    <td>${escapeHtml(item.level||'-')}</td>
                    <td>${escapeHtml(item.city_name||'-')}</td>
                    <td>${escapeHtml(item.district_name||'-')}</td>
                    <td>${escapeHtml(item.region_name||'-')}</td>
                    <td>${escapeHtml(item.user_name||'-')}</td>
                    <td>${formatDate(item.assignment_date)}</td>
                    <td>${getStatusBadge(item.status)}</td>
                    <td><div class="assignment-actions"><button type="button" class="btn btn-sm btn-outline-primary btn-edit-assignment" data-id="${item.id}" title="Edit"><i class="fas fa-edit"></i></button><button type="button" class="btn btn-sm btn-outline-danger btn-delete-assignment" data-id="${item.id}" data-school="${escapeHtml(item.school_name||'')}" title="Delete"><i class="fas fa-trash"></i></button></div></td>
                </tr>
            `;
        });

        tbody.html(html);
    }

    function loadSchools(){
        $.ajax({
            url:BASE_URL+'assignments/schools',
            type:'GET',
            dataType:'json',
            success:function(response){
                if(response.success){
                    schools=response.data||[];
                    fillSchools();
                }else{
                    showGlobalAlert(response.message||'Failed to load schools.','error');
                }
            },
            error:function(xhr){
                showGlobalAlert(getAjaxError(xhr),'error');
            }
        });
    }

    function loadUsers(){
        $.ajax({
            url:BASE_URL+'assignments/users',
            type:'GET',
            dataType:'json',
            success:function(response){
                if(response.success){
                    users=response.data||[];
                    fillUsers();
                }else{
                    showGlobalAlert(response.message||'Failed to load officers.','error');
                }
            },
            error:function(xhr){
                showGlobalAlert(getAjaxError(xhr),'error');
            }
        });
    }

    function fillSchools(selected=''){
        const select=$('#assignmentSchool');
        select.empty().append('<option value="">Select School</option>');

        schools.forEach(function(item){
            const selectedAttr=String(item.id)===String(selected)?'selected':'';
            select.append(`<option value="${item.id}" ${selectedAttr}>${escapeHtml(item.school_name||'')} - ${escapeHtml(item.npsn||'')}</option>`);
        });
    }

    function fillUsers(selected=''){
        const select=$('#assignmentUser');
        select.empty().append('<option value="">Select Officer</option>');

        users.forEach(function(item){
            const selectedAttr=String(item.id)===String(selected)?'selected':'';
            select.append(`<option value="${item.id}" ${selectedAttr}>${escapeHtml(item.name||item.username||'')}</option>`);
        });
    }

    function setSchoolLocation(schoolId){
        const school=schools.find(item=>String(item.id)===String(schoolId));

        $('#assignmentCity').val(school?.city_name||'');
        $('#assignmentDistrict').val(school?.district_name||'');
        $('#assignmentRegion').val(school?.region_name||'');
        $('#assignmentLevel').val(school?.level||'');
    }

    function createAssignment(){
        setSaveButtonLoading(true);

        $.ajax({
            url:BASE_URL+'assignments/store',
            type:'POST',
            data:$('#assignmentForm').serialize(),
            dataType:'json',
            success:function(response){
                if(response.success){
                    bootstrap.Modal.getInstance(document.getElementById('assignmentModal')).hide();
                    showGlobalAlert(response.message,'success');
                    loadAssignments();
                }else{
                    showGlobalAlert(response.message||'Failed to create assignment.','error');
                }
            },
            error:function(xhr){
                showGlobalAlert(getAjaxError(xhr),'error');
            },
            complete:function(){
                setSaveButtonLoading(false);
            }
        });
    }

    function editAssignment(id){
        $.ajax({
            url:BASE_URL+'assignments/detail/'+id,
            type:'GET',
            dataType:'json',
            success:function(response){
                if(!response.success){
                    showGlobalAlert(response.message||'Assignment not found.','error');
                    return;
                }

                const item=response.data;

                $('#assignmentId').val(item.id);
                fillSchools(item.school_id);
                fillUsers(item.user_id);
                $('#assignmentDate').val(item.assignment_date||'');
                $('#assignmentStatusForm').val(item.status||'ACTIVE');
                $('#assignmentNotes').val(item.notes||'');
                setSchoolLocation(item.school_id);
                $('#assignmentModalTitle').text('Edit Assignment');

                bootstrap.Modal.getOrCreateInstance(document.getElementById('assignmentModal')).show();
            },
            error:function(xhr){
                showGlobalAlert(getAjaxError(xhr),'error');
            }
        });
    }

    function updateAssignment(){
        const id=$('#assignmentId').val();

        setSaveButtonLoading(true);

        $.ajax({
            url:BASE_URL+'assignments/update/'+id,
            type:'POST',
            data:$('#assignmentForm').serialize(),
            dataType:'json',
            success:function(response){
                if(response.success){
                    bootstrap.Modal.getInstance(document.getElementById('assignmentModal')).hide();
                    showGlobalAlert(response.message,'success');
                    loadAssignments();
                }else{
                    showGlobalAlert(response.message||'Failed to update assignment.','error');
                }
            },
            error:function(xhr){
                showGlobalAlert(getAjaxError(xhr),'error');
            },
            complete:function(){
                setSaveButtonLoading(false);
            }
        });
    }

    function deleteAssignment(){
        const id=$('#deleteAssignmentId').val();

        if(!id){
            showGlobalAlert('Assignment ID not found.','error');
            return;
        }

        const button=$('#btnConfirmDeleteAssignment');

        button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin"></i><span>Deleting...</span>');

        $.ajax({
            url:BASE_URL+'assignments/delete/'+id,
            type:'POST',
            data:$('#assignmentForm').serialize(),
            dataType:'json',
            success:function(response){
                if(response.success){
                    bootstrap.Modal.getInstance(document.getElementById('deleteAssignmentModal')).hide();
                    showGlobalAlert(response.message,'success');
                    loadAssignments();
                }else{
                    showGlobalAlert(response.message||'Failed to delete assignment.','error');
                }
            },
            error:function(xhr){
                showGlobalAlert(getAjaxError(xhr),'error');
            },
            complete:function(){
                button.prop('disabled',false).html('<i class="fas fa-trash"></i><span>Delete</span>');
            }
        });
    }

    function resetAssignmentForm(){
        $('#assignmentForm')[0].reset();
        $('#assignmentId').val('');
        $('#assignmentCity').val('');
        $('#assignmentDistrict').val('');
        $('#assignmentRegion').val('');
        $('#assignmentLevel').val('');
        fillSchools();
        fillUsers();
        $('#assignmentStatusForm').val('ACTIVE');
        $('#assignmentDate').val(new Date().toISOString().split('T')[0]);
    }

    function setSaveButtonLoading(loading){
        const button=$('#btnSaveAssignment');

        if(loading){
            button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin"></i><span>Saving...</span>');
        }else{
            button.prop('disabled',false).html('<i class="fas fa-save"></i><span>Save Assignment</span>');
        }
    }

    function getStatusBadge(status){
        if(status==='ACTIVE') return '<span class="assignment-status active">Active</span>';
        if(status==='COMPLETED') return '<span class="assignment-status completed">Completed</span>';
        if(status==='CANCELLED') return '<span class="assignment-status cancelled">Cancelled</span>';
        return '<span class="assignment-status">-</span>';
    }

    function formatDate(date){
        if(!date) return '-';

        const parts=String(date).split('-');

        if(parts.length!==3) return escapeHtml(date);

        return parts[2]+'-'+parts[1]+'-'+parts[0];
    }

    function escapeHtml(value){
        return String(value).replace(/[&<>"']/g,function(char){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char];
        });
    }

    function getAjaxError(xhr){
        if(xhr.responseJSON?.message) return xhr.responseJSON.message;
        if(xhr.status===404) return 'URL not found. Check Routes.php.';
        if(xhr.status===403) return 'Access denied.';
        if(xhr.status===422) return 'Invalid data.';
        if(xhr.status===500) return 'Server error.';
        return 'Request failed.';
    }
});