document.addEventListener('DOMContentLoaded',function(){
    const canvas=document.getElementById('visitChart');
    if(!canvas)return;
    if(typeof Chart==='undefined'){
        console.error('Chart.js tidak ditemukan.');
        return;
    }
    if(window.visitChart instanceof Chart){
        window.visitChart.destroy();
    }
    const regionData=window.visitsByRegion||{};
    const labels=Object.keys(regionData);
    const data=Object.values(regionData).map(Number);
    window.visitChart=new Chart(canvas,{
        type:'bar',
        data:{
            labels:labels,
            datasets:[{
                label:'Sekolah Dimonev',
                data:data,
                borderWidth:1,
                borderRadius:6
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
                            return context.raw+' sekolah';
                        }
                    }
                }
            },
            scales:{
                x:{
                    ticks:{
                        autoSkip:false
                    }
                },
                y:{
                    beginAtZero:true,
                    ticks:{
                        precision:0,
                        stepSize:1
                    }
                }
            }
        }
    });
});