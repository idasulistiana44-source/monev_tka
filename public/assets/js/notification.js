(function(){
    window.showGlobalAlert=function(message,type){
        type=type||'success';
        let container=document.getElementById('globalUserAlert');
        if(!container){
            container=document.createElement('div');
            container.id='globalUserAlert';
            document.body.appendChild(container);
        }
        let icon=container.querySelector('i');
        let text=container.querySelector('span');
        if(!icon){
            icon=document.createElement('i');
            container.appendChild(icon);
        }
        if(!text){
            text=document.createElement('span');
            container.appendChild(text);
        }
        container.className='';
        container.id='globalUserAlert';
        container.classList.add(type);
        container.classList.add('show');
        if(type==='success'){
            icon.className='fas fa-check-circle';
        }else if(type==='error'){
            icon.className='fas fa-times-circle';
        }else if(type==='warning'){
            icon.className='fas fa-exclamation-triangle';
        }else{
            icon.className='fas fa-info-circle';
        }
        text.textContent=message;
        clearTimeout(window.globalUserAlertTimer);
        window.globalUserAlertTimer=setTimeout(function(){
            container.classList.remove('show');
        },4000);
    };
})();