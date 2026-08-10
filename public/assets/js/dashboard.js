document.addEventListener('DOMContentLoaded',function(){
    const canvas=document.getElementById('visitChart');
    if(!canvas){
        return;
    }
    if(typeof Chart==='undefined'){
        console.error('Chart.js tidak ditemukan.');
        return;
    }
    if(window.visitChart instanceof Chart){
        window.visitChart.destroy();
    }
    let visitData=window.visitsPerMonth;
    if(!Array.isArray(visitData)||visitData.length!==12){
        visitData=[0,0,0,0,0,0,0,0,0,0,0,0];
    }
    window.visitChart=new Chart(canvas,{
        type:'line',
        data:{
            labels:['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
            datasets:[{
                label:'Visitasi',
                data:visitData,
                borderWidth:3,
                tension:0.35,
                fill:false,
                pointRadius:4,
                pointHoverRadius:6
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            interaction:{
                intersect:false,
                mode:'index'
            },
            plugins:{
                legend:{
                    display:false
                },
                tooltip:{
                    enabled:true
                }
            },
            scales:{
                y:{
                    beginAtZero:true,
                    ticks:{
                        precision:0
                    }
                }
            }
        }
    });
});