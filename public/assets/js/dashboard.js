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
    const visitData=window.visitsByLevel||{
        SMA:0,
        SMK:0,
        SLB:0,
        MA:0
    };
    window.visitChart=new Chart(canvas,{
        type:'bar',
        data:{
            labels:['SMA','SMK','SLB','MA'],
            datasets:[{
                label:'Jumlah Visitasi',
                data:[
                    Number(visitData.SMA||0),
                    Number(visitData.SMK||0),
                    Number(visitData.SLB||0),
                    Number(visitData.MA||0)
                ],
                borderWidth:1
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            plugins:{
                legend:{
                    display:false
                },
                tooltip:{
                    callbacks:{
                        label:function(context){
                            return ' '+context.parsed.y+' visitasi';
                        }
                    }
                }
            },
            scales:{
                y:{
                    beginAtZero:true,
                    ticks:{
                        precision:0
                    },
                    title:{
                        display:true,
                        text:'Jumlah Visitasi'
                    }
                },
                x:{
                    title:{
                        display:true,
                        text:'Jenjang'
                    }
                }
            }
        }
    });
});