
document.addEventListener('DOMContentLoaded',function(){
    if(typeof Chart==='undefined'){
        console.error('Chart.js tidak ditemukan.');
        return;
    }
    const $=function(id){
        return document.getElementById(id);
    };
    const state={
        data:null,
        charts:{},
        pages:{
            infrastructure:1,
            electricity:1,
            internet:1,
            ispUtama:1,
            ispCadangan:1,
            students:1,
            session:1,
            wave:1,
            electricity:1,
            electricityCount:1,
            readiness:1,
            renderOfficerRecap:1,
            monevStatus:1,
        },
        pageSize:5,
        filters:{
            start_date:'',
            end_date:'',
            level:'',
            region_id:'',
            district_id:''
        }
    };
    const config=window.dashboardConfig||{};
    const regionsUrl=window.dashboardConfig?.regionsUrl||'';
    const districtsUrl=window.dashboardConfig?.districtsUrl||'';
    const dataUrl=config.dataUrl||'/dashboard/data';
    const exportUrl=config.exportUrl||'/dashboard/export';
    const numberFormat=function(value){
        return Number(value||0).toLocaleString('id-ID');
    };
    const loadDistricts=function(regionId){
        const element=$('filterKecamatan');
        if(!element||!districtsUrl)return;
        element.innerHTML='<option value="">Semua Kecamatan</option>';
        if(!regionId)return;
        fetch(districtsUrl+'?region_id='+encodeURIComponent(regionId),{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
        .then(function(response){
            if(!response.ok)throw new Error('HTTP '+response.status);
            return response.json();
        })
        .then(function(data){
            const districts=data.districts||[];
            element.innerHTML='<option value="">Semua Kecamatan</option>'+districts.map(function(item){
                return '<option value="'+escapeHtml(item.id)+'">'+escapeHtml(item.name)+'</option>';
            }).join('');
        })
        .catch(function(error){
            console.error('Gagal mengambil data kecamatan:',error);
        });
    };
    const loadRegions=function(){
        const element=$('filterWilayah');
        if(!element){
            console.error('filterWilayah tidak ditemukan');
            return;
        }
        if(!regionsUrl){
            console.error('regionsUrl kosong');
            return;
        }
        fetch(regionsUrl,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
        .then(function(response){
            if(!response.ok)throw new Error('HTTP '+response.status);
            return response.json();
        })
        .then(function(data){
            console.log('DATA REGION:',data);
            const regions=data.regions||[];
            element.innerHTML='<option value="">Semua Wilayah</option>'+regions.map(function(item){
                return '<option value="'+escapeHtml(item.id)+'">'+escapeHtml(item.name)+'</option>';
            }).join('');
        })
        .catch(function(error){
            console.error('Gagal mengambil data wilayah:',error);
        });
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
            'INF-05':'Ruang yang Dipakai TKAP',
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
        const data=[...getInfrastructureData()];
        const sort=$('infrastructureSort')?.value||'desc';
        const sortedData=[...data].sort(function(a,b){
            const av=Number(a.value||0);
            const bv=Number(b.value||0);
            return sort==='desc'?bv-av:av-bv;
        });
        const chartData=sortedData.filter(function(item){
            return Number(item.value||0)>0;
        }).slice(0,10);
        const values=chartData.map(function(item){
            return Number(item.value||0);
        });
        const min=values.length?Math.min(...values):0;
        const max=values.length?Math.max(...values):0;
        const average=values.length?values.reduce(function(sum,value){
            return sum+value;
        },0)/values.length:0;
        if($('infrastructureMin')){
            $('infrastructureMin').textContent=numberFormat(min);
        }
        if($('infrastructureAverage')){
            $('infrastructureAverage').textContent=numberFormat(Math.round(average));
        }
        if($('infrastructureMax')){
            $('infrastructureMax').textContent=numberFormat(max);
        }
        if($('infrastructureSchoolCount')){
            $('infrastructureSchoolCount').textContent=numberFormat(data.length);
        }
        createChart('infrastructure',$('infrastructureChart'),{
            type:'bar',
            data:{
                labels:chartData.map(function(item){
                    return item.school_name;
                }),
                datasets:[{
                    label:getInfrastructureLabel(getInfrastructureParameter()),
                    data:chartData.map(function(item){
                        return Number(item.value||0);
                    }),
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
                                return numberFormat(context.raw)+' '+getInfrastructureUnit(getInfrastructureParameter());
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
        state.pages.infrastructure=state.pages.infrastructure||1;
        renderInfrastructureTable(
            sortedData,
            getInfrastructureLabel(getInfrastructureParameter()),
            getInfrastructureUnit(getInfrastructureParameter())
        );
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
    const renderMonevStatusPagination=function(totalRows,totalPages){
        const container=$('monevStatusPagination');
        if(!container)return;

        const pageSize=state.pageSize||5;
        const currentPage=state.pages.monevStatus||1;

        let html='<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">';

        html+='<div class="d-flex align-items-center gap-2">';
        html+='<span class="text-muted small">Tampilkan</span>';
        html+='<select id="monevStatusPageSize" class="form-select form-select-sm" style="width:auto;">';
        html+='<option value="5" '+(pageSize===5?'selected':'')+'>5</option>';
        html+='<option value="10" '+(pageSize===10?'selected':'')+'>10</option>';
        html+='<option value="25" '+(pageSize===25?'selected':'')+'>25</option>';
        html+='<option value="50" '+(pageSize===50?'selected':'')+'>50</option>';
        html+='</select>';
        html+='<span class="text-muted small">data</span>';
        html+='</div>';

        const start=((currentPage-1)*pageSize)+1;
        const end=Math.min(currentPage*pageSize,totalRows);

        html+='<div class="text-muted small">Menampilkan '+start+'–'+end+' dari '+totalRows+' wilayah</div>';

        if(totalRows>pageSize){
            html+='<nav><ul class="pagination pagination-sm mb-0">';

            html+='<li class="page-item '+(currentPage===1?'disabled':'')+'">';
            html+='<button class="page-link" type="button" data-monev-page="'+(currentPage-1)+'">‹</button>';
            html+='</li>';

            for(let i=1;i<=totalPages;i++){
                html+='<li class="page-item '+(i===currentPage?'active':'')+'">';
                html+='<button class="page-link" type="button" data-monev-page="'+i+'">'+i+'</button>';
                html+='</li>';
            }

            html+='<li class="page-item '+(currentPage===totalPages?'disabled':'')+'">';
            html+='<button class="page-link" type="button" data-monev-page="'+(currentPage+1)+'">›</button>';
            html+='</li>';

            html+='</ul></nav>';
        }

        html+='</div>';

        container.innerHTML=html;

        const select=$('monevStatusPageSize');

        if(select){
            select.addEventListener('change',function(){
                state.pageSize=parseInt(this.value,10);
                state.pages.monevStatus=1;
                renderMonevStatus();
            });
        }

        container.querySelectorAll('[data-monev-page]').forEach(function(button){
            button.addEventListener('click',function(){
                const page=parseInt(this.dataset.monevPage,10);
                if(page<1||page>totalPages)return;
                state.pages.monevStatus=page;
                renderMonevStatus();
            });
        });
    };
    const renderDistributionChart=function(name,canvasId,distribution){

        const labels=Object.keys(distribution||{});
        const values=Object.values(distribution||{}).map(Number);

        createChart(name,$(canvasId),{
            type:'bar',

            data:{
                labels:labels.map(function(label){

                    if(name==='electricity'){
                        return numberFormat(label)+' Watt';
                    }

                    return label;
                }),

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
                    legend:{
                        display:false
                    },

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
    
    const renderElectricityDetail=function(data){
        const tbody=$('electricityTableBody');
        if(!tbody)return;

        const filter=$('electricityDetailFilter');
        const watt=String(filter?.value||'');

        if(!watt){
            emptyTable(tbody,4,'Pilih daya listrik untuk melihat sekolah.');

            if($('electricityTableInfo')){
                $('electricityTableInfo').textContent='Pilih daya listrik untuk melihat sekolah.';
            }

            renderPagination('electricityPagination',0,1,function(){});
            return;
        }

        const filtered=(data||[]).filter(function(item){
            return Number(item.value||0)===Number(watt);
        }).sort(function(a,b){
            return String(a.school_name||'').localeCompare(String(b.school_name||''),'id');
        });

        if(!filtered.length){
            emptyTable(tbody,4,'Tidak ada sekolah dengan daya '+numberFormat(watt)+' Watt.');

            if($('electricityTableInfo')){
                $('electricityTableInfo').textContent='0 sekolah menggunakan '+numberFormat(watt)+' Watt';
            }

            renderPagination('electricityPagination',0,1,function(){});
            return;
        }

        const totalPages=Math.ceil(filtered.length/state.pageSize);

        if(state.pages.electricity>totalPages){
            state.pages.electricity=1;
        }

        const current=state.pages.electricity||1;
        const rows=getPageData(filtered,current);
        const start=(current-1)*state.pageSize;

        tbody.innerHTML=rows.map(function(item,index){
            return '<tr><td>'+(start+index+1)+'</td><td>'+escapeHtml(item.school_name||'-')+'</td><td>'+escapeHtml(item.npsn||'-')+'</td><td><strong>'+numberFormat(item.value)+' Watt</strong></td></tr>';
        }).join('');

        if($('electricityTableInfo')){
            $('electricityTableInfo').textContent='Menampilkan '+(start+1)+'–'+Math.min(start+rows.length,filtered.length)+' dari '+filtered.length+' sekolah dengan daya '+numberFormat(watt)+' Watt';
        }

        renderPagination('electricityPagination',filtered.length,current,function(newPage){
            state.pages.electricity=newPage;
            renderElectricityDetail(data);
        });
    };

    const showElectricityDetail=function(watt,data){
        const filter=$('electricityDetailFilter');

        if(filter){
            if(window.jQuery && jQuery.fn && jQuery.fn.select2){
                jQuery(filter).val(String(watt)).trigger('change');
            }else{
                filter.value=String(watt);
                renderElectricityDetail(data);
            }
        }

        state.pages.electricity=1;
    };

    const renderElectricity=function(){
        const source=state.data?.electricity||{distribution:{},data:[]};
        const data=source.data||[];
        const grouped={};

        data.forEach(function(item){
            const watt=Number(item.value||0);
            if(watt>0){
                grouped[watt]=(grouped[watt]||0)+1;
            }
        });

        const chartEntries=Object.entries(grouped).map(function(item){
            return {
                watt:Number(item[0]),
                total:Number(item[1])
            };
        }).sort(function(a,b){
            if(b.total!==a.total)return b.total-a.total;
            return b.watt-a.watt;
        }).slice(0,10);

        const chartLabels=chartEntries.map(function(item){
            return numberFormat(item.watt)+' W';
        });

        const chartValues=chartEntries.map(function(item){
            return item.total;
        });

        createChart('electricity',$('electricityChart'),{
            type:'bar',
            data:{
                labels:chartLabels,
                datasets:[{
                    label:'Jumlah Sekolah',
                    data:chartValues,
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
                            title:function(items){
                                if(!items.length)return '';
                                const item=chartEntries[items[0].dataIndex];
                                return numberFormat(item.watt)+' Watt';
                            },
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
                },
                onClick:function(event,elements){
                    if(!elements.length)return;
                    const selected=chartEntries[elements[0].index];
                    if(selected){
                        showElectricityDetail(selected.watt,data);
                    }
                }
            }
        });

        let most=['-',0];

        Object.entries(grouped).forEach(function(item){
            if(Number(item[1])>Number(most[1])){
                most=item;
            }
        });

        if($('electricityMostUsed')){
            $('electricityMostUsed').textContent=most[0]==='-'?'-':numberFormat(most[0])+' Watt';
        }

        if($('electricityMostUsedCount')){
            $('electricityMostUsedCount').textContent=numberFormat(most[1])+' sekolah';
        }

        const countRows=Object.entries(grouped).map(function(item){
            return {
                watt:Number(item[0]),
                total:Number(item[1])
            };
        });

        const countSort=$('electricityCountSort')?.value||'desc';

        countRows.sort(function(a,b){
            if(countSort==='desc'){
                if(b.total!==a.total)return b.total-a.total;
                return b.watt-a.watt;
            }

            if(a.total!==b.total)return a.total-b.total;
            return a.watt-b.watt;
        });

        const detailFilter=$('electricityDetailFilter');
        if(detailFilter){
            const selected=detailFilter.value;

            detailFilter.innerHTML='<option value="">Pilih Daya Listrik</option>'+countRows.map(function(item){
                return '<option value="'+item.watt+'">'+numberFormat(item.watt)+' Watt — '+numberFormat(item.total)+' sekolah</option>';
            }).join('');

            if(window.jQuery && jQuery.fn && jQuery.fn.select2){
                if(jQuery(detailFilter).hasClass('select2-hidden-accessible')){
                    jQuery(detailFilter).select2('destroy');
                }

                jQuery(detailFilter).select2({
                    width:'100%',
                    placeholder:'Pilih Daya Listrik',
                    allowClear:true
                });

                if(selected && countRows.some(function(item){
                    return String(item.watt)===String(selected);
                })){
                    jQuery(detailFilter).val(selected).trigger('change');
                }
            }else{
                if(selected && countRows.some(function(item){
                    return String(item.watt)===String(selected);
                })){
                    detailFilter.value=selected;
                }
            }
        }
        
        const countBody=$('electricityCountTableBody');

        if(countBody){
            const total=countRows.length;

            if(total===0){
                emptyTable(countBody,3,'Belum ada data daya listrik.');
                renderPagination('electricityCountPagination',0,1,function(){});
            }else{
                const totalPages=Math.ceil(total/state.pageSize);

                if(!state.pages.electricityCount){
                    state.pages.electricityCount=1;
                }

                if(state.pages.electricityCount>totalPages){
                    state.pages.electricityCount=totalPages;
                }

                const current=state.pages.electricityCount;
                const rows=getPageData(countRows,current);
                const start=(current-1)*state.pageSize;

                countBody.innerHTML=rows.map(function(item,index){
                    return '<tr><td>'+(start+index+1)+'</td><td><strong>'+numberFormat(item.watt)+' Watt</strong></td><td><strong>'+numberFormat(item.total)+' sekolah</strong></td></tr>';
                }).join('');

                renderPagination(
                    'electricityCountPagination',
                    total,
                    current,
                    function(newPage){
                        state.pages.electricityCount=newPage;
                        renderElectricity();
                    }
                );
            }
        }

        renderElectricityDetail(data);
    };
    const renderInternet=function(){
        const source=state.data?.internet||{distribution:{},data:[]};
        renderDistributionChart('internet','internetChart',source.distribution);
        renderCategoricalTable('internet',source.data||[],'internetTableBody','internetPagination','internetFilter',4,'Jaringan');
    };
    const renderBandwidth=function(type,canvasId,tableId,paginationId,filterId){

        const source=state.data?.[type]||{
            distribution:{},
            data:[]
        };

        // Chart
        renderDistributionChart(
            type,
            canvasId,
            source.distribution
        );

        // Filter
        fillSelect(
            filterId,
            source.distribution,
            'Semua Bandwidth'
        );

        const entries=Object.entries(source.distribution||{});

        let most=['-',0];

        entries.forEach(function(item){
            if(Number(item[1])>Number(most[1])){
                most=item;
            }
        });

        const prefix=type==='ispUtama'
            ? 'ispUtama'
            : 'ispCadangan';

        if($(prefix+'MostUsed')){
            $(prefix+'MostUsed').textContent=most[0];
        }

        if($(prefix+'MostUsedCount')){
            $(prefix+'MostUsedCount').textContent=
                numberFormat(most[1])+' sekolah';
        }

        // Tabel + pagination
        renderCategoricalTable(
            type,
            source.data||[],
            tableId,
            paginationId,
            filterId,
            3,
            type==='ispUtama'
                ? 'ISP Utama'
                : 'ISP Cadangan'
        );
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
            return String(item.value||'').trim().toUpperCase()===String(filter).trim().toUpperCase();
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
            const suffix = stateName === 'electricity' ? ' Watt' : '';
            const value = stateName === 'electricity'
                ? numberFormat(item.value)
                : escapeHtml(item.value);

            return '<tr><td>'+(start+index+1)+'</td><td>'+
                escapeHtml(item.school_name)+'</td>'+
                npsn+
                '<td><strong>'+value+suffix+'</strong></td></tr>';
        }).join('');
        renderPagination(paginationId,filtered.length,page,function(newPage){
            state.pages[stateName]=newPage;
            renderCategoricalTable(stateName,data,tableId,paginationId,filterId,colspan,columnName);
        });
    };
    const renderStudents=function(){
        const data=[...(state.data?.students||[])];
        const order=$('studentReadinessSort')?.value||'desc';
        const sorted=[...data].sort(function(a,b){
            return order==='asc'
                ? Number(a.percentage||0)-Number(b.percentage||0)
                : Number(b.percentage||0)-Number(a.percentage||0);
        });
        const chartData=sorted.slice(0,10);

        createChart('students',$('studentReadinessChart'),{
            type:'bar',
            data:{
                labels:chartData.map(function(item){
                    return item.school_name;
                }),
                datasets:[
                    {
                        label:'Mengikuti TKAP',
                        data:chartData.map(function(item){
                            return Number(item.ikut||0);
                        }),
                        borderWidth:1,
                        borderRadius:5
                    },
                    {
                        label:'Tidak Mengikuti',
                        data:chartData.map(function(item){
                            return Number(item.tidak_ikut||0);
                        }),
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

        if($('summaryTotalSchools')){
            $('summaryTotalSchools').textContent=
                numberFormat(summary.totalSchools);
        }

        if($('summaryDraft')){
            $('summaryDraft').textContent=
                numberFormat(summary.draftSchools);
        }

        if($('summaryInProgress')){
            $('summaryInProgress').textContent=
                numberFormat(summary.inProgressSchools);
        }

        if($('summaryCompleted')){
            $('summaryCompleted').textContent=
                numberFormat(summary.visitedSchools);
        }

        if($('summaryReadiness')){
            $('summaryReadiness').textContent=
                Number(summary.readinessPercent||0).toFixed(1)+'%';
        }
    };
    
    const renderMonevStatus=function(){
        const tbody=$('monevStatusTableBody');
        if(!tbody)return;

        const rows=Array.isArray(state.data?.monevStatus)?state.data.monevStatus:[];
        const pageSize=state.pageSize||5;

        if(!rows.length){
            emptyTable(tbody,6,'Belum ada data status Monev.');
            const pagination=$('monevStatusPagination');
            if(pagination)pagination.innerHTML='';
            return;
        }

        const totalPages=Math.max(1,Math.ceil(rows.length/pageSize));

        if(state.pages.monevStatus>totalPages){
            state.pages.monevStatus=1;
        }

        const currentPage=state.pages.monevStatus||1;
        const start=(currentPage-1)*pageSize;
        const pageRows=rows.slice(start,start+pageSize);

        tbody.innerHTML=pageRows.map(function(item,index){
            return '<tr>'+
                '<td>'+(start+index+1)+'</td>'+
                '<td>'+escapeHtml(item.region_name||'-')+'</td>'+
                '<td>'+numberFormat(item.sudah_monev||0)+'</td>'+
                '<td>'+numberFormat(item.sedang_berlangsung||0)+'</td>'+
                '<td>'+numberFormat(item.draft_monev||0)+'</td>'+
                '<td><strong>'+Number(item.persentase||0).toFixed(1)+'%</strong></td>'+
            '</tr>';
        }).join('');

        renderMonevStatusPagination(rows.length,totalPages);
    };
   const renderOfficerRecap=function(){
        const tbody=document.getElementById('officerRecapTableBody');
        if(!tbody)return;

        const result=state.data?.officerRecap||{};
        const rows=Array.isArray(result.data)?result.data:[];
        const total=result.total||{};
        const pageSize=Number(state.pageSize)||5;

        if(!state.pages)state.pages={};
        if(!state.pages.officerRecap)state.pages.officerRecap=1;

        const totalPages=Math.max(1,Math.ceil(rows.length/pageSize));

        if(state.pages.officerRecap>totalPages){
            state.pages.officerRecap=1;
        }

        const currentPage=state.pages.officerRecap;
        const start=(currentPage-1)*pageSize;
        const end=start+pageSize;
        const pageRows=rows.slice(start,end);

        let html='';

        pageRows.forEach(function(item,index){
            html+='<tr>';
            html+='<td>'+(start+index+1)+'</td>';
            html+='<td>'+escapeHtml(item.officer_name||'-')+'</td>';
            html+='<td>'+escapeHtml(item.region_name||'-')+'</td>';
            html+='<td>'+numberFormat(item.jumlah_sasaran||0)+'</td>';
            html+='<td>'+numberFormat(item.sudah_monev||0)+'</td>';
            html+='<td>'+numberFormat(item.sedang_berlangsung||0)+'</td>';
            html+='<td>'+numberFormat(item.belum_monev||0)+'</td>';
            html+='<td><strong>'+Number(item.persentase||0).toFixed(1)+'%</strong></td>';
            html+='<td>'+escapeHtml(item.keterangan||'-')+'</td>';
            html+='</tr>';
        });

        if(currentPage===totalPages){
            html+='<tr class="table-total">';
            html+='<td><strong>Total</strong></td>';
            html+='<td></td>';
            html+='<td></td>';
            html+='<td><strong>'+numberFormat(total.jumlah_sasaran||0)+'</strong></td>';
            html+='<td><strong>'+numberFormat(total.sudah_monev||0)+'</strong></td>';
            html+='<td><strong>'+numberFormat(total.sedang_berlangsung||0)+'</strong></td>';
            html+='<td><strong>'+numberFormat(total.belum_monev||0)+'</strong></td>';
            html+='<td><strong>'+Number(total.persentase||0).toFixed(1)+'%</strong></td>';
            html+='<td></td>';
            html+='</tr>';
        }

        tbody.innerHTML=html;

        renderOfficerPagination(rows.length,totalPages);
    };
    const renderOfficerPagination=function(totalRows,totalPages){
        const container=$('officerRecapPagination');
        if(!container)return;

        const pageSize=state.pageSize;
        const currentPage=state.pages.officerRecap||1;

        let html='<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">';

        html+='<div class="d-flex align-items-center gap-2">';
        html+='<span class="text-muted small">Tampilkan</span>';
        html+='<select id="officerRecapPageSize" class="form-select form-select-sm" style="width:auto;">';
        html+='<option value="5" '+(pageSize===5?'selected':'')+'>5</option>';
        html+='<option value="10" '+(pageSize===10?'selected':'')+'>10</option>';
        html+='<option value="25" '+(pageSize===25?'selected':'')+'>25</option>';
        html+='<option value="50" '+(pageSize===50?'selected':'')+'>50</option>';
        html+='<option value="0" '+(pageSize===0?'selected':'')+'>Semua</option>';
        html+='</select>';
        html+='<span class="text-muted small">data</span>';
        html+='</div>';

        if(pageSize===0||totalRows<=pageSize){
            html+='<div class="text-muted small">Menampilkan 1–'+totalRows+' dari '+totalRows+' pelaksana</div>';
        }else{
            const start=((currentPage-1)*pageSize)+1;
            const end=Math.min(currentPage*pageSize,totalRows);

            html+='<div class="text-muted small">Menampilkan '+start+'–'+end+' dari '+totalRows+' pelaksana</div>';

            html+='<nav><ul class="pagination pagination-sm mb-0">';

            html+='<li class="page-item '+(currentPage===1?'disabled':'')+'">';
            html+='<button class="page-link" type="button" data-officer-page="'+(currentPage-1)+'">‹</button>';
            html+='</li>';

            for(let i=1;i<=totalPages;i++){
                html+='<li class="page-item '+(i===currentPage?'active':'')+'">';
                html+='<button class="page-link" type="button" data-officer-page="'+i+'">'+i+'</button>';
                html+='</li>';
            }

            html+='<li class="page-item '+(currentPage===totalPages?'disabled':'')+'">';
            html+='<button class="page-link" type="button" data-officer-page="'+(currentPage+1)+'">›</button>';
            html+='</li>';

            html+='</ul></nav>';
        }

        html+='</div>';

        container.innerHTML=html;

        const pageSizeSelect=$('officerRecapPageSize');

        if(pageSizeSelect){
            pageSizeSelect.addEventListener('change',function(){
                state.pageSize=parseInt(this.value,10);
                state.pages.officerRecap=1;
                renderOfficerRecap();
            });
        }

        container.querySelectorAll('[data-officer-page]').forEach(function(button){
            button.addEventListener('click',function(){
                const page=parseInt(this.dataset.officerPage,10);
                if(page<1||page>totalPages)return;
                state.pages.officerRecap=page;
                renderOfficerRecap();
            });
        });
    };
    
    const renderProblemRecommendations=function(){
    const tbody=document.getElementById('problemRecommendationTableBody');
    if(!tbody)return;

    const data=state.data?.problemRecommendations||[];

    if(!data.length){
        tbody.innerHTML='<tr><td colspan="5" class="table-empty">Belum ada data.</td></tr>';
        return;
    }

    tbody.innerHTML=data.map(function(item,index){
        const hasDetail=Number(item.total_school)>0;

        return '<tr>'+
            '<td>'+escapeHtml(item.no)+'</td>'+
            '<td style="font-size:12px;">'+escapeHtml(item.problem)+'</td>'+
            '<td style="font-size:12px;text-align:center;">'+numberFormat(item.total_school)+'</td>'+
            '<td style="font-size:12px;">'+escapeHtml(item.recommendation)+'</td>'+
            '<td style="font-size:12px;text-align:center;">'+
                (hasDetail
                    ?'<button type="button" class="btn btn-sm btn-outline-primary" onclick="showProblemDetail('+index+')">'+
                        '<i class="fas fa-search"></i> Detail'+
                    '</button>'
                    :'<span class="text-muted">-</span>')+
            '</td>'+
        '</tr>';
    }).join('');
};
    
    const renderAll=function(){
        updateSummary();
        renderInfrastructure();
        renderElectricity();
        renderInternet();
        renderBandwidth(
            'ispUtama',
            'ispUtamaChart',
            'ispUtamaTableBody',
            'ispUtamaPagination',
            'ispUtamaFilter'
        );

        renderBandwidth(
            'ispCadangan',
            'ispCadanganChart',
            'ispCadanganTableBody',
            'ispCadanganPagination',
            'ispCadanganFilter'
        );
        renderStudents();
        renderSession();
        renderWave();
        renderReadiness();
        renderMonevStatus();
        renderOfficerRecap();
        renderProblemRecommendations();
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
            console.log('DATA DASHBOARD:',data);
            console.log('PROBLEM RECOMMENDATIONS:',data.problemRecommendations);
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
        state.filters.region_id=$('filterWilayah')?.value||'';
        state.filters.district_id=$('filterKecamatan')?.value||'';
        loadDashboard();
    };
    const resetFilters=function(){
        if($('filterStartDate'))$('filterStartDate').value='';
        if($('filterEndDate'))$('filterEndDate').value='';
        if($('filterJenjang'))$('filterJenjang').value='';
        if($('filterWilayah'))$('filterWilayah').value='';
        if($('filterKecamatan'))$('filterKecamatan').value='';
        state.filters={start_date:'',end_date:'',level:'',region_id:'',district_id:''};
        loadRegions();
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

   if(window.jQuery){
        jQuery('#electricityDetailFilter').off('change.electricity').on('change.electricity',function(){
            state.pages.electricity=1;
            renderElectricityDetail(state.data?.electricity?.data||[]);
        });
    }
    $('electricityCountSort')?.addEventListener('change',function(){
        state.pages.electricityCount=1;
        renderElectricity();
    });
    $('internetFilter')?.addEventListener('change',function(){
        state.pages.internet=1;
        renderInternet();
    });
    $('filterWilayah')?.addEventListener('change',function(){
        loadDistricts(this.value);
    });
    $('ispUtamaFilter')?.addEventListener('change',function(){

        state.pages.ispUtama=1;

        renderBandwidth(
            'ispUtama',
            'ispUtamaChart',
            'ispUtamaTableBody',
            'ispUtamaPagination',
            'ispUtamaFilter'
        );

    });

    $('ispCadanganFilter')?.addEventListener('change',function(){

        state.pages.ispCadangan=1;

        renderBandwidth(
            'ispCadangan',
            'ispCadanganChart',
            'ispCadanganTableBody',
            'ispCadanganPagination',
            'ispCadanganFilter'
        );

    });
    window.showProblemDetail=function(index){
        const item=state.data?.problemRecommendations?.[index];
        if(!item)return;

        const details=Array.isArray(item.details)?item.details:[];

        document.getElementById('problemDetailTitle').textContent='Detail Permasalahan';
        document.getElementById('problemDetailSubtitle').textContent=item.problem;

        const head=document.getElementById('problemDetailHead');
        const body=document.getElementById('problemDetailBody');

        
        if(item.type==='device'){
            head.innerHTML=
                '<tr style="font-size:11px;">'+
                    '<th>No</th>'+
                    '<th>Sekolah</th>'+
                    '<th>Sesi</th>'+
                    '<th>Gelombang</th>'+
                    '<th>Kebutuhan Utama</th>'+
                    '<th>Kebutuhan Cadangan</th>'+
                    '<th>Total Kebutuhan</th>'+
                    '<th>Perangkat Tersedia</th>'+
                    '<th>Status</th>'+
                    '<th>Kekurangan</th>'+
                '</tr>';

            body.innerHTML=details.map(function(row,i){
                const utama=Number(row.kebutuhan_utama||0);
                const cadangan=Number(row.kebutuhan_cadangan||0);
                const tersedia=Number(row.available||0);

                const kurangUtama=Math.max(0,utama-tersedia);
                const sisaSetelahUtama=Math.max(0,tersedia-utama);
                const kurangCadangan=Math.max(0,cadangan-sisaSetelahUtama);

                const kurangTotal=kurangUtama+kurangCadangan;

                const status=kurangUtama>0
                    ?'<span style="color:#dc2626;font-weight:600;">KURANG</span>'
                    :'<span style="color:#16a34a;font-weight:600;">CUKUP</span>';

                let kekurangan='';

                if(kurangUtama>0 && kurangCadangan>0){
                    kekurangan=
                        numberFormat(kurangUtama)+' unit utama + '+
                        numberFormat(kurangCadangan)+' unit cadangan';
                }else if(kurangUtama>0){
                    kekurangan=
                        numberFormat(kurangUtama)+' unit utama';
                }else if(kurangCadangan>0){
                    kekurangan=
                        numberFormat(kurangCadangan)+' unit cadangan';
                }else{
                    kekurangan='Tidak ada';
                }

                return '<tr style="font-size:11px;">'+
                    '<td>'+(i+1)+'</td>'+
                    '<td>'+escapeHtml(row.school)+'</td>'+
                    '<td style="text-align:center;">'+numberFormat(row.sesi)+'</td>'+
                    '<td style="text-align:center;">'+numberFormat(row.gelombang)+'</td>'+
                    '<td style="text-align:center;">'+numberFormat(utama)+' unit</td>'+
                    '<td style="text-align:center;">'+numberFormat(cadangan)+' unit</td>'+
                    '<td style="text-align:center;font-weight:600;">'+numberFormat(row.total_kebutuhan)+' unit</td>'+
                    '<td style="text-align:center;">'+numberFormat(tersedia)+' unit</td>'+
                    '<td style="text-align:center;">'+status+'</td>'+
                    '<td style="text-align:center;font-weight:600;">'+
                        escapeHtml(kekurangan)+
                    '</td>'+
                '</tr>';
            }).join('');
        }
        else if(item.type==='main_isp'){
            head.innerHTML=
                '<tr style="font-size:11px;">'+
                    '<th>No</th>'+
                    '<th>Sekolah</th>'+
                    '<th>ISP Utama</th>'+
                    '<th style="text-align:center;">Bandwidth Tersedia</th>'+
                    '<th style="text-align:center;">Kebutuhan Bandwidth</th>'+
                    '<th style="text-align:center;">Kekurangan</th>'+
                '</tr>';

            body.innerHTML=details.map(function(row,i){
                return '<tr style="font-size:11px;">'+
                    '<td>'+(i+1)+'</td>'+
                    '<td>'+escapeHtml(row.school)+'</td>'+
                    '<td>'+escapeHtml(row.isp||'-')+'</td>'+
                    '<td style="text-align:center;">'+numberFormat(row.bandwidth)+' Mbps</td>'+
                    '<td style="text-align:center;">'+numberFormat(row.need)+' Mbps</td>'+
                    '<td style="text-align:center;color:#dc2626;font-weight:700;">'+
                        numberFormat(row.shortage)+' Mbps'+
                    '</td>'+
                '</tr>';
            }).join('');
        }

        else if(item.type==='backup_missing'){
            head.innerHTML=
                '<tr>'+
                    '<th style="width:60px;">No</th>'+
                    '<th>Sekolah</th>'+
                    '<th style="text-align:center;">ISP/Jaringan Cadangan</th>'+
                '</tr>';

            body.innerHTML=details.map(function(row,i){
                return '<tr>'+
                    '<td>'+(i+1)+'</td>'+
                    '<td>'+escapeHtml(row.school)+'</td>'+
                    '<td style="text-align:center;"><strong>Tidak Ada</strong></td>'+
                '</tr>';
            }).join('');
        }

        else if(item.type==='backup_bandwidth'){
            head.innerHTML=
                '<tr>'+
                    '<th style="width:60px;">No</th>'+
                    '<th>Sekolah</th>'+
                    '<th style="text-align:center;">Bandwidth Cadangan</th>'+
                    '<th style="text-align:center;">Kebutuhan</th>'+
                    '<th style="text-align:center;">Kekurangan</th>'+
                '</tr>';

            body.innerHTML=details.map(function(row,i){
                return '<tr>'+
                    '<td>'+(i+1)+'</td>'+
                    '<td>'+escapeHtml(row.school)+'</td>'+
                    '<td style="text-align:center;">'+numberFormat(row.bandwidth)+' Mbps</td>'+
                    '<td style="text-align:center;">'+numberFormat(row.need)+' Mbps</td>'+
                    '<td style="text-align:center;"><strong>'+numberFormat(row.shortage)+' Mbps</strong></td>'+
                '</tr>';
            }).join('');
        }

        const modal=new bootstrap.Modal(document.getElementById('problemDetailModal'));
        modal.show();
    };
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
    loadRegions(); 
    loadDashboard();
    
});