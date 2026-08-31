
document.addEventListener('DOMContentLoaded',function(){
    if(typeof Chart==='undefined'){
        console.error('Chart.js tidak ditemukan.');
        return;
    }
    const $=function(id){
        return document.getElementById(id);
    };
    const state={
        data:window.dashboardData||null,
        charts:{},
        pages:{
            infrastructure:1,
            electricity:1,
            internet:1,
            upload:1,
            download:1,
            students:1,
            session:1,
            wave:1,
            readiness:1
        },
        pageSize:5,
        filters:{
            start_date:'',
            end_date:'',
            level:'',
            district_id:''
        }
    };
    const config=window.dashboardConfig||{};
    const dataUrl=config.dataUrl||'/dashboard/data';
    const exportUrl=config.exportUrl||'/dashboard/export';
    const numberFormat=function(value){
        return Number(value||0).toLocaleString('id-ID');
    };
    const escapeHtml=function(value){
        return String(value??'').replace(/[&<>"']/g,function(char){
            return {
                '&':'&amp;',
                '<':'&lt;',
                '>':'&gt;',
                '"':'&quot;',
                "'":'&#039;'
            }[char];
        });
    };
    const destroyChart=function(name){
        if(state.charts[name] instanceof Chart){
            state.charts[name].destroy();
        }
        state.charts[name]=null;
    };
    const createChart=function(name,canvas,config){
        if(!canvas)return;
        destroyChart(name);
        state.charts[name]=new Chart(canvas,config);
    };
    const emptyTable=function(tbody,colspan,message){
        if(!tbody)return;
        tbody.innerHTML='<tr><td colspan="'+colspan+'" class="table-empty">'+escapeHtml(message||'Belum ada data.')+'</td></tr>';
    };
    const renderPagination=function(elementId,total,current,callback){
        const element=$(elementId);
        if(!element)return;
        element.innerHTML='';
        const pages=Math.ceil(total/state.pageSize);
        if(pages<=1)return;
        const previous=document.createElement('button');
        previous.type='button';
        previous.innerHTML='&lsaquo;';
        previous.disabled=current<=1;
        previous.addEventListener('click',function(){
            if(current>1)callback(current-1);
        });
        element.appendChild(previous);
        let start=Math.max(1,current-2);
        let end=Math.min(pages,start+4);
        if(end-start<4){
            start=Math.max(1,end-4);
        }
        for(let page=start;page<=end;page++){
            const button=document.createElement('button');
            button.type='button';
            button.textContent=page;
            if(page===current){
                button.classList.add('active');
            }
            button.addEventListener('click',function(){
                callback(page);
            });
            element.appendChild(button);
        }
        const next=document.createElement('button');
        next.type='button';
        next.innerHTML='&rsaquo;';
        next.disabled=current>=pages;
        next.addEventListener('click',function(){
            if(current<pages)callback(current+1);
        });
        element.appendChild(next);
    };
    const getPageData=function(data,page){
        const start=(page-1)*state.pageSize;
        return data.slice(start,start+state.pageSize);
    };
    const getInfrastructureParameter=function(){
        const element=$('infrastructureParameter');
        return element?element.value:'INF-02';
    };
    const getInfrastructureLabel=function(code){
        const labels={
            'INF-01':'Komputer / PC Milik',
            'INF-02':'Laptop Milik',
            'INF-03':'Laptop Bukan Milik',
            'INF-04':'Labkom',
            'INF-05':'Ruang yang Dipakai TKA-P',
            'INF-06':'Switch Hub',
            'INF-07':'UPS',
            'INF-08':'Access Point'
        };
        return labels[code]||code;
    };
    const getInfrastructureUnit=function(code){
        const units={
            'INF-01':'unit',
            'INF-02':'unit',
            'INF-03':'unit',
            'INF-04':'ruang',
            'INF-05':'ruang',
            'INF-06':'unit',
            'INF-07':'unit',
            'INF-08':'unit'
        };
        return units[code]||'unit';
    };
    const getInfrastructureData=function(){
        const code=getInfrastructureParameter();
        if(!state.data||!state.data.infrastructure)return[];
        if(!state.data.infrastructure[code])return[];
        return state.data.infrastructure[code].data||[];
    };
    const createRangeDistribution=function(data){
        const values=data.map(function(item){
            return Number(item.value||0);
        });
        if(!values.length){
            return {
                labels:[],
                values:[]
            };
        }
        const max=Math.max.apply(null,values);
        let ranges=[];
        if(max<=5){
            ranges=[
                {label:'0',min:0,max:0},
                {label:'1–2',min:1,max:2},
                {label:'3–5',min:3,max:5}
            ];
        }else if(max<=20){
            ranges=[
                {label:'0',min:0,max:0},
                {label:'1–5',min:1,max:5},
                {label:'6–10',min:6,max:10},
                {label:'11–20',min:11,max:20}
            ];
        }else if(max<=50){
            ranges=[
                {label:'0',min:0,max:0},
                {label:'1–10',min:1,max:10},
                {label:'11–20',min:11,max:20},
                {label:'21–30',min:21,max:30},
                {label:'31–50',min:31,max:50}
            ];
        }else{
            const step=Math.ceil(max/5);
            ranges=[
                {label:'0',min:0,max:0},
                {label:'1–'+step,min:1,max:step},
                {label:(step+1)+'–'+(step*2),min:step+1,max:step*2},
                {label:(step*2+1)+'–'+(step*3),min:step*2+1,max:step*3},
                {label:'>'+step*3,min:step*3+1,max:Infinity}
            ];
        }
        const result=ranges.map(function(range){
            return {
                label:range.label,
                total:values.filter(function(value){
                    return value>=range.min&&value<=range.max;
                }).length
            };
        });
        return {
            labels:result.map(function(item){return item.label;}),
            values:result.map(function(item){return item.total;})
        };
    };
    const renderInfrastructure=function(){
        const code=getInfrastructureParameter();
        const label=getInfrastructureLabel(code);
        const unit=getInfrastructureUnit(code);
        const data=[...getInfrastructureData()];
        const order=$('infrastructureSort')?.value||'asc';
        data.sort(function(a,b){
            const x=Number(a.value||0);
            const y=Number(b.value||0);
            if(order==='desc')return y-x;
            return x-y;
        });
        const distribution=createRangeDistribution(data);
        createChart('infrastructure',$('infrastructureChart'),{
            type:'bar',
            data:{
                labels:distribution.labels,
                datasets:[{
                    label:'Jumlah Sekolah',
                    data:distribution.values,
                    borderWidth:1,
                    borderRadius:6
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{
                    legend:{display:false},
                    tooltip:{
                        callbacks:{
                            label:function(context){
                                return numberFormat(context.raw)+' sekolah';
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
        const values=data.map(function(item){
            return Number(item.value||0);
        });
        const min=values.length?Math.min.apply(null,values):0;
        const max=values.length?Math.max.apply(null,values):0;
        const average=values.length?values.reduce(function(a,b){return a+b;},0)/values.length:0;
        if($('infrastructureMin'))$('infrastructureMin').textContent=numberFormat(min);
        if($('infrastructureAverage'))$('infrastructureAverage').textContent=average.toFixed(1);
        if($('infrastructureMax'))$('infrastructureMax').textContent=numberFormat(max);
        if($('infrastructureSchoolCount'))$('infrastructureSchoolCount').textContent=numberFormat(data.length);
        renderInfrastructureTable(data,label,unit);
    };
    const renderInfrastructureTable=function(data,label,unit){
        const tbody=$('infrastructureTableBody');
        if(!tbody)return;
        if(!data.length){
            emptyTable(tbody,4,'Belum ada data sekolah.');
            if($('infrastructureTableInfo'))$('infrastructureTableInfo').textContent='Tidak ada data';
            renderPagination('infrastructurePagination',0,1,function(){});
            return;
        }
        const totalPages=Math.ceil(data.length/state.pageSize);
        if(state.pages.infrastructure>totalPages){
            state.pages.infrastructure=1;
        }
        const page=state.pages.infrastructure;
        const rows=getPageData(data,page);
        const start=(page-1)*state.pageSize;
        tbody.innerHTML=rows.map(function(item,index){
            return '<tr><td>'+(start+index+1)+'</td><td>'+escapeHtml(item.school_name)+'</td><td>'+escapeHtml(item.npsn)+'</td><td><strong>'+numberFormat(item.value)+' '+escapeHtml(unit)+'</strong></td></tr>';
        }).join('');
        if($('infrastructureTableInfo')){
            $('infrastructureTableInfo').textContent='Menampilkan '+(start+1)+'–'+Math.min(start+state.pageSize,data.length)+' dari '+data.length+' sekolah';
        }
        renderPagination('infrastructurePagination',data.length,page,function(newPage){
            state.pages.infrastructure=newPage;
            renderInfrastructureTable(data,label,unit);
        });
    };
    const renderDistributionChart=function(name,canvasId,distribution){
        const labels=Object.keys(distribution||{});
        const values=Object.values(distribution||{}).map(Number);
        createChart(name,$(canvasId),{
            type:'bar',
            data:{
                labels:labels,
                datasets:[{
                    label:'Jumlah Sekolah',
                    data:values,
                    borderWidth:1,
                    borderRadius:6
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{
                    legend:{display:false},
                    tooltip:{
                        callbacks:{
                            label:function(context){
                                return numberFormat(context.raw)+' sekolah';
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
    };
    const renderElectricity=function(){
        const source=state.data?.electricity||{distribution:{},data:[]};
        renderDistributionChart('electricity','electricityChart',source.distribution);
        const entries=Object.entries(source.distribution||{});
        let most=['-',0];
        entries.forEach(function(item){
            if(Number(item[1])>Number(most[1]))most=item;
        });
        if($('electricityMostUsed'))$('electricityMostUsed').textContent=most[0];
        if($('electricityMostUsedCount'))$('electricityMostUsedCount').textContent=numberFormat(most[1])+' sekolah';
        fillSelect('electricityFilter',source.distribution,'Semua Daya');
        renderCategoricalTable('electricity',source.data||[],'electricityTableBody','electricityPagination','electricityFilter',4,'Daya');
    };
    const renderInternet=function(){
        const source=state.data?.internet||{distribution:{},data:[]};
        renderDistributionChart('internet','internetChart',source.distribution);
        renderCategoricalTable('internet',source.data||[],'internetTableBody','internetPagination','internetFilter',4,'Jaringan');
    };
    const renderBandwidth=function(type,canvasId,tableId,paginationId,filterId){
        const source=state.data?.[type]||{distribution:{},data:[]};
        renderDistributionChart(type,canvasId,source.distribution);
        fillSelect(filterId,source.distribution,'Semua Bandwidth');
        const entries=Object.entries(source.distribution||{});
        let most=['-',0];
        entries.forEach(function(item){
            if(Number(item[1])>Number(most[1]))most=item;
        });
        const prefix=type==='upload'?'upload':'download';
        if($(prefix+'MostUsed'))$(prefix+'MostUsed').textContent=most[0];
        if($(prefix+'MostUsedCount'))$(prefix+'MostUsedCount').textContent=numberFormat(most[1])+' sekolah';
        renderCategoricalTable(type,source.data||[],tableId,paginationId,filterId,3,type==='upload'?'Upload':'Download');
    };
    const fillSelect=function(id,distribution,defaultText){
        const element=$(id);
        if(!element)return;
        const current=element.value;
        const options=Object.keys(distribution||{});
        element.innerHTML='<option value="">'+escapeHtml(defaultText)+'</option>'+options.map(function(value){
            return '<option value="'+escapeHtml(value)+'">'+escapeHtml(value)+'</option>';
        }).join('');
        if(options.indexOf(current)!==-1){
            element.value=current;
        }
    };
    const renderCategoricalTable=function(stateName,data,tableId,paginationId,filterId,colspan,columnName){
        const tbody=$(tableId);
        if(!tbody)return;
        const filter=$(filterId)?.value||'';
        let filtered=filter?data.filter(function(item){
            return String(item.value)===String(filter);
        }):[...data];
        filtered.sort(function(a,b){
            return String(a.school_name||'').localeCompare(String(b.school_name||''),'id');
        });
        if(!filtered.length){
            emptyTable(tbody,colspan,'Belum ada data sekolah.');
            renderPagination(paginationId,0,1,function(){});
            return;
        }
        const totalPages=Math.ceil(filtered.length/state.pageSize);
        if(state.pages[stateName]>totalPages){
            state.pages[stateName]=1;
        }
        const page=state.pages[stateName]||1;
        const rows=getPageData(filtered,page);
        const start=(page-1)*state.pageSize;
        tbody.innerHTML=rows.map(function(item,index){
            const npsn=colspan===4?'<td>'+escapeHtml(item.npsn)+'</td>':'';
            return '<tr><td>'+(start+index+1)+'</td><td>'+escapeHtml(item.school_name)+'</td>'+npsn+'<td><strong>'+escapeHtml(item.value)+'</strong></td></tr>';
        }).join('');
        renderPagination(paginationId,filtered.length,page,function(newPage){
            state.pages[stateName]=newPage;
            renderCategoricalTable(stateName,data,tableId,paginationId,filterId,colspan,columnName);
        });
    };
    const renderStudents=function(){
        const data=[...(state.data?.students||[])];
        createChart('students',$('studentReadinessChart'),{
            type:'bar',
            data:{
                labels:data.map(function(item){return item.school_name;}),
                datasets:[
                    {
                        label:'Mengikuti TKA-P',
                        data:data.map(function(item){return Number(item.ikut||0);}),
                        borderWidth:1,
                        borderRadius:5
                    },
                    {
                        label:'Tidak Mengikuti',
                        data:data.map(function(item){return Number(item.tidak_ikut||0);}),
                        borderWidth:1,
                        borderRadius:5
                    }
                ]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{
                    tooltip:{
                        callbacks:{
                            label:function(context){
                                return context.dataset.label+': '+numberFormat(context.raw)+' siswa';
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
                            precision:0
                        }
                    }
                }
            }
        });
        renderStudentTable(data);
    };
    const renderStudentTable=function(data){
        const tbody=$('studentReadinessTableBody');
        if(!tbody)return;
        const order=$('studentReadinessSort')?.value||'desc';
        const sorted=[...data].sort(function(a,b){
            return order==='asc'?Number(a.percentage||0)-Number(b.percentage||0):Number(b.percentage||0)-Number(a.percentage||0);
        });
        if(!sorted.length){
            emptyTable(tbody,6,'Belum ada data siswa.');
            renderPagination('studentReadinessPagination',0,1,function(){});
            return;
        }
        const totalPages=Math.ceil(sorted.length/state.pageSize);
        if(state.pages.students>totalPages)state.pages.students=1;
        const page=state.pages.students;
        const rows=getPageData(sorted,page);
        const start=(page-1)*state.pageSize;
        tbody.innerHTML=rows.map(function(item,index){
            return '<tr><td>'+(start+index+1)+'</td><td>'+escapeHtml(item.school_name)+'</td><td>'+numberFormat(item.total)+'</td><td>'+numberFormat(item.ikut)+'</td><td>'+numberFormat(item.tidak_ikut)+'</td><td><strong>'+Number(item.percentage||0).toFixed(1)+'%</strong></td></tr>';
        }).join('');
        renderPagination('studentReadinessPagination',sorted.length,page,function(newPage){
            state.pages.students=newPage;
            renderStudentTable(data);
        });
    };
    const renderSession=function(){
        const source=state.data?.sessions||{distribution:{},data:[]};
        renderDistributionChart('session','sessionChart',source.distribution);
        fillSelect('sessionFilter',source.distribution,'Semua Sesi');
        renderCategoricalTable('session',source.data||[],'sessionTableBody','sessionPagination','sessionFilter',3,'Sesi');
    };
    const renderWave=function(){
        const source=state.data?.waves||{distribution:{},data:[]};
        renderDistributionChart('wave','waveChart',source.distribution);
        fillSelect('waveFilter',source.distribution,'Semua Gelombang');
        renderCategoricalTable('wave',source.data||[],'waveTableBody','wavePagination','waveFilter',3,'Gelombang');
    };
    const renderReadiness=function(){
        const source=state.data?.readiness||{};
        const labels=Object.keys(source);
        const values=Object.values(source).map(Number);
        createChart('readiness',$('readinessChart'),{
            type:'doughnut',
            data:{
                labels:labels,
                datasets:[{
                    data:values,
                    borderWidth:1
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{
                    legend:{
                        position:'bottom'
                    },
                    tooltip:{
                        callbacks:{
                            label:function(context){
                                return context.label+': '+numberFormat(context.raw)+' sekolah';
                            }
                        }
                    }
                }
            }
        });
        if($('readinessExcellent'))$('readinessExcellent').textContent=numberFormat(source['Sangat Baik']||0);
        if($('readinessGood'))$('readinessGood').textContent=numberFormat(source['Baik']||0);
        if($('readinessFair'))$('readinessFair').textContent=numberFormat(source['Cukup']||0);
        if($('readinessPoor'))$('readinessPoor').textContent=numberFormat(source['Kurang Memadai']||0);
        const data=state.data?.readinessData||[];
        renderCategoricalTable('readiness',data,'readinessTableBody','readinessPagination','readinessFilter',4,'Kesiapan');
    };
    const updateSummary=function(){
        const summary=state.data?.summary||{};
        if($('summaryTotalSchools'))$('summaryTotalSchools').textContent=numberFormat(summary.totalSchools);
        if($('summaryCompleted'))$('summaryCompleted').textContent=numberFormat(summary.totalVisits);
        if($('summaryReadiness'))$('summaryReadiness').textContent=Number(summary.readinessPercent||0).toFixed(1)+'%';
        if($('summaryDocuments'))$('summaryDocuments').textContent=Number(summary.documentPercent||0).toFixed(1)+'%';
    };
    const renderAll=function(){
        updateSummary();
        renderInfrastructure();
        renderElectricity();
        renderInternet();
        renderBandwidth('upload','uploadChart','uploadTableBody','uploadPagination','uploadFilter');
        renderBandwidth('download','downloadChart','downloadTableBody','downloadPagination','downloadFilter');
        renderStudents();
        renderSession();
        renderWave();
        renderReadiness();
    };
    const loadDashboard=function(){
        const params=new URLSearchParams();
        Object.keys(state.filters).forEach(function(key){
            if(state.filters[key]){
                params.set(key,state.filters[key]);
            }
        });
        const url=dataUrl+(params.toString()?'?'+params.toString():'');
        fetch(url,{
            method:'GET',
            headers:{
                'X-Requested-With':'XMLHttpRequest',
                'Accept':'application/json'
            }
        })
        .then(function(response){
            if(!response.ok){
                throw new Error('HTTP '+response.status);
            }
            return response.json();
        })
        .then(function(data){
            state.data=data;
            Object.keys(state.pages).forEach(function(key){
                state.pages[key]=1;
            });
            renderAll();
        })
        .catch(function(error){
            console.error('Dashboard gagal mengambil data:',error);
        });
    };
    const applyFilters=function(){
        state.filters.start_date=$('filterStartDate')?.value||'';
        state.filters.end_date=$('filterEndDate')?.value||'';
        state.filters.level=$('filterJenjang')?.value||'';
        state.filters.district_id=$('filterKecamatan')?.value||'';
        loadDashboard();
    };
    const resetFilters=function(){
        if($('filterStartDate'))$('filterStartDate').value='';
        if($('filterEndDate'))$('filterEndDate').value='';
        if($('filterJenjang'))$('filterJenjang').value='';
        if($('filterKecamatan'))$('filterKecamatan').value='';
        state.filters={
            start_date:'',
            end_date:'',
            level:'',
            district_id:''
        };
        loadDashboard();
    };
    const exportReport=function(type){
        const params=new URLSearchParams();
        Object.keys(state.filters).forEach(function(key){
            if(state.filters[key]){
                params.set(key,state.filters[key]);
            }
        });
        params.set('type',type);
        window.open(exportUrl+'?'+params.toString(),'_blank');
    };
    $('btnApplyFilter')?.addEventListener('click',applyFilters);
    $('btnResetFilter')?.addEventListener('click',resetFilters);
    $('infrastructureParameter')?.addEventListener('change',function(){
        state.pages.infrastructure=1;
        renderInfrastructure();
    });
    $('infrastructureSort')?.addEventListener('change',function(){
        state.pages.infrastructure=1;
        renderInfrastructure();
    });
    $('electricityFilter')?.addEventListener('change',function(){
        state.pages.electricity=1;
        renderElectricity();
    });
    $('internetFilter')?.addEventListener('change',function(){
        state.pages.internet=1;
        renderInternet();
    });
    $('uploadFilter')?.addEventListener('change',function(){
        state.pages.upload=1;
        renderBandwidth('upload','uploadChart','uploadTableBody','uploadPagination','uploadFilter');
    });
    $('downloadFilter')?.addEventListener('change',function(){
        state.pages.download=1;
        renderBandwidth('download','downloadChart','downloadTableBody','downloadPagination','downloadFilter');
    });
    $('studentReadinessSort')?.addEventListener('change',function(){
        state.pages.students=1;
        renderStudents();
    });
    $('sessionFilter')?.addEventListener('change',function(){
        state.pages.session=1;
        renderSession();
    });
    $('waveFilter')?.addEventListener('change',function(){
        state.pages.wave=1;
        renderWave();
    });
    $('readinessFilter')?.addEventListener('change',function(){
        state.pages.readiness=1;
        renderReadiness();
    });
    $('btnExportPDF')?.addEventListener('click',function(){
        exportReport('pdf');
    });
    $('btnExportExcel')?.addEventListener('click',function(){
        exportReport('excel');
    });
    if(state.data){
        renderAll();
    }else{
        loadDashboard();
    }
});