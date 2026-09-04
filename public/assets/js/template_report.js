(function(){
    'use strict';
    const container=document.getElementById('sectionsContainer');
    const emptyTemplate=document.getElementById('emptyTemplate');
    const sectionTemplate=document.getElementById('sectionTemplate');
    const itemTemplate=document.getElementById('itemTemplate');
    const btnAddSection=document.getElementById('btnAddSection');
    const btnEmptyAddSection=document.getElementById('btnEmptyAddSection');
    const btnSaveTemplate=document.getElementById('btnSaveTemplate');
    if(!container||!sectionTemplate||!itemTemplate||!window.TEMPLATE_REPORT_URLS)return;
    function updateEmptyState(){
        emptyTemplate.style.display=container.querySelectorAll('.report-section-card').length?'none':'block';
    }
    function updateSectionNumbers(){
        container.querySelectorAll('.report-section-card').forEach((section,index)=>{
            const number=section.querySelector('.section-number');
            if(number)number.textContent='SECTION '+(index+1);
        });
    }
    function createItem(itemsContainer,data={}){
        const wrapper=document.createElement('div');
        wrapper.innerHTML=itemTemplate.innerHTML.trim();
        const item=wrapper.firstElementChild;
        const id=Number(data.id||0);
        item.dataset.itemId=id>0?id:'';
        item.dataset.content=data.content??'';
        item.querySelector('.item-title-input').value=data.title??data.item_title??'';
        itemsContainer.appendChild(item);
        return item;
    }
    function createSection(data={}){
        const wrapper=document.createElement('div');
        wrapper.innerHTML=sectionTemplate.innerHTML.trim();
        const section=wrapper.firstElementChild;
        const id=Number(data.id||0);
        section.dataset.sectionId=id>0?id:'';
        section.querySelector('.section-title-input').value=data.title??data.section_title??'';
        const items=Array.isArray(data.items)?data.items:[];
        const itemsContainer=section.querySelector('.report-section-items');
        items.forEach(item=>createItem(itemsContainer,item));
        container.appendChild(section);
        updateEmptyState();
        updateSectionNumbers();
        return section;
    }
    function buildSections(rows){
        const sections=[];
        const sectionMap=new Map();
        rows.forEach(row=>{
            const title=String(row.section_title??'').trim();
            if(!title)return;
            if(!sectionMap.has(title)){
                const section={id:0,title:title,items:[]};
                sectionMap.set(title,section);
                sections.push(section);
            }
            const section=sectionMap.get(title);
            const itemTitle=String(row.item_title??'').trim();
            if(itemTitle===''&&section.items.length===0&&Number(row.id||0)>0){
                section.id=Number(row.id);
            }else{
                section.items.push({
                    id:Number(row.id||0),
                    title:itemTitle,
                    content:row.content??''
                });
            }
        });
        return sections;
    }
    async function loadTemplate(){
        try{
            const response=await fetch(window.TEMPLATE_REPORT_URLS.data,{
                method:'GET',
                headers:{'Accept':'application/json'}
            });
            const result=await response.json();
            if(!response.ok||!result.success){
                throw new Error(result.message||'Gagal mengambil data template.');
            }
            container.innerHTML='';
            const rows=Array.isArray(result.data)?result.data:[];
            const sections=buildSections(rows);
            sections.forEach(section=>createSection(section));
            updateEmptyState();
            updateSectionNumbers();
        }catch(error){
            console.error(error);
            showError(error.message||'Gagal mengambil data template.');
        }
    }
    function collectData(){
        const sections=[];
        container.querySelectorAll('.report-section-card').forEach(section=>{
            const sectionTitle=section.querySelector('.section-title-input').value.trim();
            if(!sectionTitle)return;
            const items=[];
            section.querySelectorAll('.report-item').forEach(item=>{
                items.push({
                    id:Number(item.dataset.itemId||0),
                    title:item.querySelector('.item-title-input').value.trim()
                });
            });
            sections.push({
                id:Number(section.dataset.sectionId||0),
                title:sectionTitle,
                items:items
            });
        });
        return sections;
    }
    async function saveTemplate(){
        const sections=collectData();
        if(!sections.length){
            showWarning('Tambahkan minimal satu section terlebih dahulu.');
            return;
        }
        const originalHtml=btnSaveTemplate.innerHTML;
        btnSaveTemplate.disabled=true;
        btnSaveTemplate.innerHTML='<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
        try{
            const response=await fetch(window.TEMPLATE_REPORT_URLS.save,{
                method:'POST',
                headers:{
                    'Content-Type':'application/json',
                    'Accept':'application/json'
                },
                body:JSON.stringify({sections:sections})
            });
            const result=await response.json();
            if(!response.ok||!result.success){
                throw new Error(result.message||'Template gagal disimpan.');
            }
            await loadTemplate();
            showSuccess(result.message||'Template berhasil disimpan.');
        }catch(error){
            console.error(error);
            showError(error.message||'Template gagal disimpan.');
        }finally{
            btnSaveTemplate.disabled=false;
            btnSaveTemplate.innerHTML=originalHtml;
        }
    }
    function addSection(){
        const section=createSection();
        section.querySelector('.section-title-input').focus();
    }
    function showSuccess(message){
        if(window.Swal){
            Swal.fire({
                icon:'success',
                title:'Berhasil',
                text:message,
                timer:1200,
                showConfirmButton:false
            });
        }else{
            alert(message);
        }
    }
    function showWarning(message){
        if(window.Swal){
            Swal.fire({
                icon:'warning',
                title:'Template kosong',
                text:message
            });
        }else{
            alert(message);
        }
    }
    function showError(message){
        if(window.Swal){
            Swal.fire({
                icon:'error',
                title:'Gagal',
                text:message
            });
        }else{
            alert(message);
        }
    }
    btnAddSection.addEventListener('click',addSection);
    btnEmptyAddSection.addEventListener('click',addSection);
    btnSaveTemplate.addEventListener('click',saveTemplate);
    container.addEventListener('click',function(event){
        const addItemButton=event.target.closest('.btn-add-item');
        if(addItemButton){
            const section=addItemButton.closest('.report-section-card');
            const itemsContainer=section.querySelector('.report-section-items');
            const item=createItem(itemsContainer);
            item.querySelector('.item-title-input').focus();
            return;
        }
        const deleteItemButton=event.target.closest('.btn-delete-item');
        if(deleteItemButton){
            const item=deleteItemButton.closest('.report-item');
            if(item)item.remove();
            return;
        }
        const deleteSectionButton=event.target.closest('.btn-delete-section');
        if(deleteSectionButton){
            const section=deleteSectionButton.closest('.report-section-card');
            if(section)section.remove();
            updateEmptyState();
            updateSectionNumbers();
        }
    });
    loadTemplate();
})();