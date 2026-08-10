$(document).ready(function(){
    const visitId=window.VISIT_ID||0;
    let instrumentData=null;
    let saving=false;
    init();
    function init(){
        if(!visitId){
            notify('ID visitasi tidak valid.','error');
            return;
        }
        bindEvents();
        loadInstrument();
    }
    function bindEvents(){
        $('#btnBackVisit').on('click',function(){
            window.location.href=BASE_URL+'visits';
        });
        $('#btnSaveDraft').on('click',function(){
            saveDraft();
        });
        $('#btnCompleteVisit').on('click',function(){
            completeVisit();
        });
    }
    function loadInstrument(){
        $('#instrumentContainer').html('<div class="instrument-page-loading"><i class="fas fa-spinner fa-spin me-2"></i>Memuat instrumen...</div>');
        $.ajax({
            url:BASE_URL+'visits/instrument-data/'+visitId,
            type:'GET',
            dataType:'json',
            success:function(response){
                if(!response.success){
                    notify(response.message||'Instrumen gagal dimuat.','error');
                    return;
                }
                instrumentData=response.data;
                renderVisitInfo(response.data.visit);
                renderInstrument(response.data.sections||[]);
            },
            error:function(xhr){
                notify(getAjaxError(xhr,'Instrumen gagal dimuat.'),'error');
                $('#instrumentContainer').html('<div class="instrument-empty"><i class="fas fa-exclamation-circle me-2"></i>Instrumen gagal dimuat.</div>');
            }
        });
    }
    function renderVisitInfo(visit){
        $('#visitInfo').html(`
            <div class="visit-info-grid">
                <div class="visit-info-item">
                    <span class="visit-info-label">NPSN</span>
                    <strong>${escapeHtml(visit.npsn||'-')}</strong>
                </div>
                <div class="visit-info-item">
                    <span class="visit-info-label">Sekolah</span>
                    <strong>${escapeHtml(visit.school_name||'-')}</strong>
                </div>
                <div class="visit-info-item">
                    <span class="visit-info-label">Level</span>
                    <strong>${escapeHtml(visit.level||'-')}</strong>
                </div>
                <div class="visit-info-item">
                    <span class="visit-info-label">Tanggal Visitasi</span>
                    <strong>${formatDate(visit.visit_date)}</strong>
                </div>
                <div class="visit-info-item">
                    <span class="visit-info-label">Petugas</span>
                    <strong>${escapeHtml(visit.officer_name||'-')}</strong>
                </div>
                <div class="visit-info-item">
                    <span class="visit-info-label">Status</span>
                    ${getStatusBadge(visit.status)}
                </div>
            </div>
        `);
    }
    function renderInstrument(sections){
        const container=$('#instrumentContainer');
        container.empty();
        if(!sections.length){
            container.html('<div class="instrument-empty"><i class="fas fa-file-alt me-2"></i>Belum ada instrumen aktif.</div>');
            return;
        }
        sections.forEach(function(section,index){
            const questions=section.questions||[];
            const sectionHtml=$(`
                <div class="instrument-section-card">
                    <div class="instrument-section-header">
                        <div class="instrument-section-number">${index+1}</div>
                        <div class="instrument-section-info">
                            <h3>${escapeHtml(section.name||'-')}</h3>
                            ${section.description?'<p>'+escapeHtml(section.description)+'</p>':''}
                        </div>
                    </div>
                    <div class="instrument-section-body"></div>
                </div>
            `);
            const body=sectionHtml.find('.instrument-section-body');
            if(!questions.length){
                body.html('<div class="instrument-question-empty">Belum ada pertanyaan pada bagian ini.</div>');
            }else{
                questions.forEach(function(question,qIndex){
                    body.append(renderQuestion(question,qIndex));
                });
            }
            container.append(sectionHtml);
        });
    }
    function renderQuestion(question,index){
        const id='question_'+question.id;
        const required=Number(question.is_required)===1;
        const requiredMark=required?' <span class="text-danger">*</span>':'';
        const answer=question.answer??'';
        let input='';
        const type=String(question.type||'text').toLowerCase();
        if(type==='textarea'){
            input='<textarea class="form-control instrument-answer" data-question-id="'+question.id+'" data-required="'+(required?1:0)+'" rows="4" placeholder="Isi jawaban...">'+escapeHtml(answer)+'</textarea>';
        }else if(type==='number'){
            input='<input type="number" class="form-control instrument-answer" data-question-id="'+question.id+'" data-required="'+(required?1:0)+'" value="'+escapeHtml(answer)+'" placeholder="Masukkan angka">';
        }else if(type==='date'){
            input='<input type="date" class="form-control instrument-answer" data-question-id="'+question.id+'" data-required="'+(required?1:0)+'" value="'+escapeHtml(answer)+'">';
        }else if(type==='select'){
            input=renderSelect(question,answer,required);
        }else if(type==='radio'){
            input=renderRadio(question,answer,required);
        }else if(type==='checkbox'){
            input=renderCheckbox(question,answer,required);
        }else{
            input='<input type="text" class="form-control instrument-answer" data-question-id="'+question.id+'" data-required="'+(required?1:0)+'" value="'+escapeHtml(answer)+'" placeholder="Isi jawaban...">';
        }
        return `
            <div class="instrument-question" data-question="${question.id}">
                <div class="instrument-question-label">
                    <span class="instrument-question-number">${index+1}</span>
                    <div>
                        <label>${escapeHtml(question.question||'-')}${requiredMark}</label>
                        ${question.description?'<small>'+escapeHtml(question.description)+'</small>':''}
                    </div>
                </div>
                <div class="instrument-question-input">${input}</div>
            </div>
        `;
    }
    function renderSelect(question,answer,required){
        const options=parseOptions(question.options);
        let html='<select class="form-select instrument-answer" data-question-id="'+question.id+'" data-required="'+(required?1:0)+'"><option value="">Pilih jawaban</option>';
        options.forEach(function(option){
            const value=typeof option==='object'?option.value:option;
            const label=typeof option==='object'?option.label:option;
            html+='<option value="'+escapeHtml(value)+'"'+(String(value)===String(answer)?' selected':'')+'>'+escapeHtml(label)+'</option>';
        });
        html+='</select>';
        return html;
    }
    function renderRadio(question,answer,required){
        const options=parseOptions(question.options);
        let html='<div class="instrument-radio-group">';
        options.forEach(function(option,index){
            const value=typeof option==='object'?option.value:option;
            const label=typeof option==='object'?option.label:option;
            html+='<label class="instrument-radio"><input type="radio" name="question_'+question.id+'" class="instrument-answer" data-question-id="'+question.id+'" data-required="'+(required?1:0)+'" value="'+escapeHtml(value)+'"'+(String(value)===String(answer)?' checked':'')+'><span>'+escapeHtml(label)+'</span></label>';
        });
        html+='</div>';
        return html;
    }
    function renderCheckbox(question,answer,required){
        const options=parseOptions(question.options);
        let selected=[];
        try{
            selected=Array.isArray(answer)?answer:JSON.parse(answer||'[]');
        }catch(e){
            selected=answer?[answer]:[];
        }
        let html='<div class="instrument-checkbox-group">';
        options.forEach(function(option,index){
            const value=typeof option==='object'?option.value:option;
            const label=typeof option==='object'?option.label:option;
            html+='<label class="instrument-checkbox"><input type="checkbox" class="instrument-answer" data-question-id="'+question.id+'" data-required="'+(required?1:0)+'" value="'+escapeHtml(value)+'"'+(selected.map(String).includes(String(value))?' checked':'')+'><span>'+escapeHtml(label)+'</span></label>';
        });
        html+='</div>';
        return html;
    }
    function parseOptions(options){
        if(!options)return[];
        if(Array.isArray(options))return options;
        try{
            const parsed=JSON.parse(options);
            return Array.isArray(parsed)?parsed:[];
        }catch(e){
            return String(options).split(',').map(function(item){
                return item.trim();
            }).filter(Boolean);
        }
    }
    function collectAnswers(){
        const answers={};
        $('.instrument-answer').each(function(){
            const questionId=$(this).data('question-id');
            if(!questionId)return;
            if($(this).is(':checkbox')){
                if(!answers[questionId])answers[questionId]=[];
                if($(this).is(':checked'))answers[questionId].push($(this).val());
            }else if($(this).is(':radio')){
                if($(this).is(':checked'))answers[questionId]=$(this).val();
            }else{
                answers[questionId]=$(this).val();
            }
        });
        return answers;
    }
    function validateRequired(){
        let valid=true;
        $('.instrument-answer[data-required="1"]').each(function(){
            const questionId=$(this).data('question-id');
            let hasValue=false;
            if($(this).is(':checkbox')){
                hasValue=$('.instrument-answer[data-question-id="'+questionId+'"]:checked').length>0;
            }else if($(this).is(':radio')){
                hasValue=$('.instrument-answer[data-question-id="'+questionId+'"]:checked').length>0;
            }else{
                hasValue=String($(this).val()||'').trim()!=='';
            }
            if(!hasValue){
                valid=false;
                $(this).addClass('is-invalid');
            }else{
                $(this).removeClass('is-invalid');
            }
        });
        if(!valid)notify('Masih ada pertanyaan wajib yang belum diisi.','warning');
        return valid;
    }
    function saveDraft(){
        if(saving)return;
        saveAnswers(false);
    }
    function completeVisit(){
        if(saving)return;
        if(!validateRequired())return;
        saveAnswers(true);
    }
    function saveAnswers(complete){
        saving=true;
        const button=complete?$('#btnCompleteVisit'):$('#btnSaveDraft');
        const original=button.html();
        button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');
        const answers=collectAnswers();
        $.ajax({
            url:complete?BASE_URL+'visits/complete/'+visitId:BASE_URL+'visits/save-answers/'+visitId,
            type:'POST',
            data:{
                answers:answers
            },
            dataType:'json',
            success:function(response){
                if(response.success){
                    notify(response.message||'Data berhasil disimpan.','success');
                    if(complete&&response.data&&response.data.redirect){
                        setTimeout(function(){
                            window.location.href=response.data.redirect;
                        },700);
                    }
                }else{
                    notify(response.message||'Data gagal disimpan.','error');
                }
            },
            error:function(xhr){
                notify(getAjaxError(xhr,'Data gagal disimpan.'),'error');
            },
            complete:function(){
                saving=false;
                button.prop('disabled',false).html(original);
            }
        });
    }
    function getStatusBadge(status){
        if(status==='draft')return '<span class="badge bg-secondary-subtle text-secondary">Belum Mulai</span>';
        if(status==='in_progress')return '<span class="badge bg-warning-subtle text-warning-emphasis">Berlangsung</span>';
        if(status==='completed')return '<span class="badge bg-success-subtle text-success">Selesai</span>';
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