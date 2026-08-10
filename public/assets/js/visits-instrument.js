$(document).ready(function(){
    if(!document.querySelector('.visit-instrument-page'))return;
    const visitId=Number(window.VISIT_ID||0);
    if(!visitId){
        notify('ID visitasi tidak valid.','error');
        return;
    }
    init();
    function init(){
        bindEvents();
        loadInstrument();
    }
    function bindEvents(){
        $('#btnBackVisit').on('click',function(){
            window.location.href=BASE_URL+'visits';
        });
        $('#btnSaveDraft').on('click',function(){
            saveInstrument(false);
        });
        $('#btnCompleteVisit').on('click',function(){
            saveInstrument(true);
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
                    $('#instrumentContainer').html('<div class="instrument-empty">Instrumen gagal dimuat.</div>');
                    notify(response.message||'Instrumen gagal dimuat.','error');
                    return;
                }
                if(response.data&&response.data.visit){
                    renderVisitInfo(response.data.visit);
                }
                const sections=response.data&&Array.isArray(response.data.sections)?response.data.sections:[];
                renderInstrument(sections);
            },
            error:function(xhr){
                console.error(xhr.responseText);
                $('#instrumentContainer').html('<div class="instrument-empty">Instrumen gagal dimuat.</div>');
                notify(getAjaxError(xhr,'Instrumen gagal dimuat.'),'error');
            }
        });
    }
    function renderVisitInfo(data){
        $('#visitInfo').html('<div class="visit-info-grid"><div class="visit-info-item"><span class="visit-info-label">NPSN</span><strong>'+escapeHtml(data.npsn||'-')+'</strong></div><div class="visit-info-item"><span class="visit-info-label">Sekolah</span><strong>'+escapeHtml(data.school_name||'-')+'</strong></div><div class="visit-info-item"><span class="visit-info-label">Level</span><strong>'+escapeHtml(data.level||'-')+'</strong></div><div class="visit-info-item"><span class="visit-info-label">Tanggal Visitasi</span><strong>'+formatDate(data.visit_date)+'</strong></div><div class="visit-info-item"><span class="visit-info-label">Petugas</span><strong>'+escapeHtml(data.officer_name||'-')+'</strong></div><div class="visit-info-item"><span class="visit-info-label">Status</span><strong>'+getStatusBadge(data.status)+'</strong></div></div>');
    }
    function renderInstrument(sections){
        const container=$('#instrumentContainer');
        container.empty();
        if(!Array.isArray(sections)||sections.length===0){
            container.html('<div class="instrument-empty"><i class="fas fa-file-circle-question me-2"></i>Belum ada instrumen aktif.</div>');
            return;
        }
        sections.forEach(function(section,sectionIndex){
            const questions=Array.isArray(section.questions)?section.questions:[];
            let questionHtml='';
            questions.forEach(function(question,index){
                questionHtml+=renderQuestion(question,index+1);
            });
            container.append('<div class="instrument-section-card"><div class="instrument-section-header"><div class="instrument-section-number">'+(sectionIndex+1)+'</div><div class="instrument-section-info"><h3>'+escapeHtml(section.name||'-')+'</h3>'+(section.description?'<p>'+escapeHtml(section.description)+'</p>':'')+'</div></div><div class="instrument-section-body">'+(questionHtml||'<div class="instrument-question-empty">Belum ada pertanyaan pada bagian ini.</div>')+'</div></div>');
        });
    }
    function renderQuestion(question,number){
        const id=Number(question.id||0);
        const type=(question.type||'text').toLowerCase();
        const required=Number(question.is_required||0)===1;
        const answer=question.answer??'';
        let input='';
        if(type==='textarea'){
            input='<textarea class="form-control instrument-answer" name="answer['+id+']" data-question-id="'+id+'" rows="4" '+(required?'required':'')+'>'+escapeHtml(answer)+'</textarea>';
        }else if(type==='select'){
            input='<select class="form-select instrument-answer" name="answer['+id+']" data-question-id="'+id+'" '+(required?'required':'')+'><option value="">Pilih jawaban</option>'+renderOptions(question.options,answer)+'</select>';
        }else if(type==='radio'){
            input='<div class="instrument-radio-group">'+renderRadioOptions(question.options,answer,id)+'</div>';
        }else if(type==='checkbox'){
            input='<div class="instrument-checkbox-group">'+renderCheckboxOptions(question.options,answer,id)+'</div>';
        }else if(type==='number'){
            input='<input type="number" class="form-control instrument-answer" name="answer['+id+']" data-question-id="'+id+'" value="'+escapeHtml(answer)+'" '+(required?'required':'')+'>';
        }else if(type==='date'){
            input='<input type="date" class="form-control instrument-answer" name="answer['+id+']" data-question-id="'+id+'" value="'+escapeHtml(answer)+'" '+(required?'required':'')+'>';
        }else{
            input='<input type="text" class="form-control instrument-answer" name="answer['+id+']" data-question-id="'+id+'" value="'+escapeHtml(answer)+'" '+(required?'required':'')+'>';
        }
        return '<div class="instrument-question"><div class="instrument-question-label"><div class="instrument-question-number">'+number+'</div><div><label>'+escapeHtml(question.question||'-')+(required?' <span class="text-danger">*</span>':'')+'</label>'+(question.description?'<small>'+escapeHtml(question.description)+'</small>':'')+'</div></div><div class="instrument-question-input">'+input+'</div></div>';
    }
    function renderOptions(options,selected){
        if(!Array.isArray(options))return '';
        let html='';
        options.forEach(function(option){
            const value=typeof option==='object'?(option.value??''):option;
            const label=typeof option==='object'?(option.label??value):option;
            html+='<option value="'+escapeHtml(value)+'" '+(String(value)===String(selected)?'selected':'')+'>'+escapeHtml(label)+'</option>';
        });
        return html;
    }
    function renderRadioOptions(options,selected,id){
        if(!Array.isArray(options)||options.length===0)return '<div class="text-muted small">Pilihan jawaban belum tersedia.</div>';
        let html='';
        options.forEach(function(option,index){
            const value=typeof option==='object'?(option.value??''):option;
            const label=typeof option==='object'?(option.label??value):option;
            const optionId='radio_'+id+'_'+index;
            html+='<label class="instrument-radio" for="'+optionId+'"><input type="radio" id="'+optionId+'" name="answer['+id+']" value="'+escapeHtml(value)+'" data-question-id="'+id+'" '+(String(value)===String(selected)?'checked':'')+'>'+escapeHtml(label)+'</label>';
        });
        return html;
    }
    function renderCheckboxOptions(options,selected,id){
        if(!Array.isArray(options)||options.length===0)return '<div class="text-muted small">Pilihan jawaban belum tersedia.</div>';
        let selectedValues=[];
        if(Array.isArray(selected)){
            selectedValues=selected.map(String);
        }else if(typeof selected==='string'&&selected!==''){
            try{
                const decoded=JSON.parse(selected);
                if(Array.isArray(decoded))selectedValues=decoded.map(String);
                else selectedValues=selected.split(',').map(function(item){return item.trim();});
            }catch(e){
                selectedValues=selected.split(',').map(function(item){return item.trim();});
            }
        }
        let html='';
        options.forEach(function(option,index){
            const value=typeof option==='object'?(option.value??''):option;
            const label=typeof option==='object'?(option.label??value):option;
            const optionId='checkbox_'+id+'_'+index;
            html+='<label class="instrument-checkbox" for="'+optionId+'"><input type="checkbox" id="'+optionId+'" name="answer['+id+'][]" value="'+escapeHtml(value)+'" data-question-id="'+id+'" '+(selectedValues.indexOf(String(value))!==-1?'checked':'')+'>'+escapeHtml(label)+'</label>';
        });
        return html;
    }
    function collectAnswers(){
        const answers={};
        $('.instrument-answer').each(function(){
            const element=$(this);
            const id=element.data('question-id');
            if(!id)return;
            if(element.is(':radio')){
                if(element.is(':checked'))answers[id]=element.val();
            }else if(element.is(':checkbox')){
                if(!Array.isArray(answers[id]))answers[id]=[];
                if(element.is(':checked'))answers[id].push(element.val());
            }else{
                answers[id]=element.val();
            }
        });
        return answers;
    }
    function validateRequired(){
        let valid=true;
        let firstInvalid=null;
        $('.instrument-answer[required]').each(function(){
            const element=$(this);
            if(element.is(':radio')){
                const name=element.attr('name');
                if($('input[name="'+name+'"]:checked').length===0){
                    valid=false;
                    if(!firstInvalid)firstInvalid=element;
                }
            }else if(!String(element.val()||'').trim()){
                valid=false;
                if(!firstInvalid)firstInvalid=element;
            }
        });
        if(!valid){
            notify('Masih ada pertanyaan wajib yang belum diisi.','warning');
            if(firstInvalid&&firstInvalid.length){
                $('html,body').animate({scrollTop:firstInvalid.offset().top-120},300);
            }
        }
        return valid;
    }
    function saveInstrument(complete){
        if(complete&&!validateRequired())return;
        const answers=collectAnswers();
        const button=complete?$('#btnCompleteVisit'):$('#btnSaveDraft');
        const original=button.html();
        button.prop('disabled',true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');
        const url=complete?BASE_URL+'visits/complete/'+visitId:BASE_URL+'visits/save-answers/'+visitId;
        $.ajax({
            url:url,
            type:'POST',
            data:{answers:JSON.stringify(answers)},
            dataType:'json',
            success:function(response){
                if(response.success){
                    notify(response.message||(complete?'Visitasi berhasil diselesaikan.':'Data berhasil disimpan.'),'success');
                    if(complete){
                        setTimeout(function(){
                            window.location.href=response.data&&response.data.redirect?response.data.redirect:BASE_URL+'visits';
                        },700);
                    }
                }else{
                    notify(response.message||'Data gagal disimpan.','error');
                }
            },
            error:function(xhr){
                console.error(xhr.responseText);
                notify(getAjaxError(xhr,'Data gagal disimpan.'),'error');
            },
            complete:function(){
                button.prop('disabled',false).html(original);
            }
        });
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