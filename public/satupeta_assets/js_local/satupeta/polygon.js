var poly = []
var poly_arr = []
var infoWindowPoly

async function prom_get_kota() {
    let data_arr = []
    let prov_id = $('select[name="provinsi"]').val()
    $('#data-wil').html('')

    if (prov_id != '') {
        await get_kota(`prov_id=${prov_id}`).then((data) => {
            data_arr = data
        })
    }else{
        await get_kota().then((data) => {
            data_arr = data
        })
    }

    $('select[name="kota"]').html('')
    $('select[name="kota"]').append(`
        <option value="">-- Pilih Kota --</option>
    `)
    data_arr.forEach((v) => {
        $('select[name="kota"]').append(`
            <option value="${v.city_id}">${v.city_name}</option>
        `)
    })
    $('select[name="kota"]').select2()
}

async function prom_get_kecamatan() {
    let data_arr = []
    let city_id = $('select[name="kota"]').val()
    $('#data-wil').html('')

    if (city_id != '') {
        await get_kecamatan(`city_id=${city_id}`).then((data) => {
            data_arr = data
        })
    }else{
        await get_kecamatan().then((data) => {
            data_arr = data
        })
    }

    $('select[name="kecamatan"]').html('')
    $('select[name="kecamatan"]').append(`
        <option value="">-- Pilih Kecamatan --</option>
    `)
    data_arr.forEach((v) => {
        $('select[name="kecamatan"]').append(`
            <option value="${v.dis_id}">${v.dis_name}</option>
        `)
    })
    $('select[name="kecamatan"]').select2()
}

function  get_wilayah() {
    var value = $('#optwil').val();
    var uprov = $('#prov').val();
    $('#wil').html('');
    $('#data-wil').html('')
    if (value != '') {
        if (uprov != '') {
            uprov = `prov_name=${uprov.toUpperCase()}`
        }
        switch (value) {
            case "prov":
                get_provinsi(uprov).then((data) => {
                    show_check_wil('provinsi')
                })
                break;
            case 'kota':
                $('#wil').append(`
                <label for="">Provinsi</label>
                <select name="provinsi" id="provinsi" class="form-control" onchange="show_check_wil('kota')" style="height: 34px!important;">
                    <option value="">-- Pilih Provinsi --</option>
                </select>
                `)
                get_provinsi(uprov).then((data) => {
                    data.forEach((v) => {
                        $('select[name="provinsi"]').append(`
                            <option value="${v.prov_id}">${v.prov_name}</option>
                        `)
                    })
                    $('select[name="provinsi"]').select2()
                })
                break;
            
            case 'kec' : 
                $('#wil').append(`
                <label for="">Provinsi</label>
                <select name="provinsi" id="provinsi" class="form-control" onchange="prom_get_kota()" style="height: 34px!important;">
                    <option value="">-- Pilih Provinsi --</option>
                </select>

                <label for="">Kota</label>
                <select name="kota" id="kota" class="form-control" onchange="show_check_wil('kecamatan')" style="height: 34px!important;">
                    <option value="">-- Pilih Kota --</option>
                </select>
                `)

                get_provinsi().then((data) => {
                    data.forEach((v) => {
                        $('select[name="provinsi"]').append(`
                            <option value="${v.prov_id}">${v.prov_name}</option>
                        `)
                    })
                    $('select[name="provinsi"]').select2()
                })

                break;

            case 'kel' :
                $('#wil').append(`
                <label for="">Provinsi</label>
                <select name="provinsi" id="provinsi" class="form-control" onchange="prom_get_kota()" style="height: 34px!important;">
                    <option value="">-- Pilih Provinsi --</option>
                </select>

                <label for="">Kota</label>
                <select name="kota" id="kota" class="form-control" onchange="prom_get_kecamatan()" style="height: 34px!important;">
                    <option value="">-- Pilih Kota --</option>
                </select>

                <label for="">Kecamatan</label>
                <select name="kecamatan" id="kecamatan" class="form-control" onchange="show_check_wil('kelurahan')" style="height: 34px!important;">
                    <option value="">-- Pilih kecamatan --</option>
                </select>
                `)

                get_provinsi().then((data) => {
                    data.forEach((v) => {
                        $('select[name="provinsi"]').append(`
                            <option value="${v.prov_id}">${v.prov_name}</option>
                        `)
                    })
                    $('select[name="provinsi"]').select2()
                })

                break;
        
            default:
                break;
        }
    }
}

function show_check_wil(category) {
    $('#data-wil').html('')
    $('#data-wil').append(`
        <div style="height:200px;overflow-y:auto;">
            <div class="card">
            <div class="card-header">
                <div class="row mx-0" style="text-align:left!important;">
                <div class="col-4 pt-2" id="check-select-all-poly">
                    
                </div>
                    <div class="col">
                    <input type="text" class="form-control" placeholder="Cari" id="search-data-wil" onkeyup="search_data_wil()">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush data-check-wil" id="data-check-wil">
                </ul>
            </div>
            </div>
        </div>
        `)
    $('#data-check-wil').html('')
    if (category == 'provinsi') {
        $('#check-select-all-poly').html('')
        $('#check-select-all-poly').append(`
            <input type="checkbox" class="form-control-check" id="select_all_poly" onchange="check_poly('provinsi')"><span class="ms-1">All</span>
        `)
        provinsi.forEach((v) => {
            $('#data-check-wil').append(`
                <li class="list-group-item">
                    <input type="checkbox" class="me-2 check-poly" name="poly" onchange="check_poly('provinsi')" value="${v.prov_id}">
                    <span class="">${v.prov_name}</span>
                </li>
            `)
        })
    }

    if (category == 'kota') {
        $('#check-select-all-poly').html('')
        $('#check-select-all-poly').append(`
            <input type="checkbox" class="form-control-check" id="select_all_poly" onchange="check_poly('kota')"><span class="ms-1">All</span>
        `)
        get_kota(`prov_id=${$('select[name="provinsi"]').val()}`).then((data) => {
            data.forEach((v) => {
                $('#data-check-wil').append(`
                    <li class="list-group-item">
                        ${v.polygon != null ? `
                        <input type="checkbox" class="me-2 check-poly" name="poly" onchange="check_poly('kota')" value="${v.polres_id}">
                        <span class="">${v.city_name} / ${v.polres}</span>` 
                        : `
                        <span class="">${v.city_name} / ${v.polres}</span> 
                        `}
                    </li>
                `)
            })
        })
    }

    if (category == 'kecamatan') {
        $('#check-select-all-poly').html('')
        $('#check-select-all-poly').append(`
            <input type="checkbox" class="form-control-check" id="select_all_poly" onchange="check_poly('kecamatan')"><span class="ms-1">All</span>
        `)
        get_kecamatan(`city_id=${$('select[name="kota"]').val()}`).then((data) => {
            data.forEach((v) => {
                $('#data-check-wil').append(`
                    <li class="list-group-item">
                    ${v.polygon != null ? `
                        <input type="checkbox" class="me-2 check-poly" name="poly" onchange="check_poly('kecamatan')" value="${v.dis_id}">
                        <span class="">${v.dis_name}</span>`
                    : 
                    `<span class="">${v.dis_name}</span>`}
                    </li>
                `)
            })
        })
    }

    if (category == 'kelurahan') {
        $('#check-select-all-poly').html('')
        $('#check-select-all-poly').append(`
            <input type="checkbox" class="form-control-check" id="select_all_poly" onchange="check_poly('kelurahan')"><span class="ms-1">All</span>
        `)
        get_kelurahan(`dis_id=${$('select[name="kecamatan"]').val()}`).then((data) => {
            data.forEach((v) => {
                $('#data-check-wil').append(`
                    <li class="list-group-item">
                    ${v.polygon != null ? `
                        <input type="checkbox" class="me-2 check-poly" name="poly" onchange="check_poly('kelurahan')" value="${v.subdis_id}">
                        <span class="">${v.subdis_name}</span>`
                    : `<span class="">${v.subdis_name}</span>`}
                    </li>
                `)
            })
        })
    }

    //select all polygon
    $("#select_all_poly").change(function(){
        var status = this.checked;
        $('.check-poly').each(function(){
        this.checked = status;
        });
    });

    $('.check-poly').change(function(){ 
        if(this.checked == false){
        $("#select_all_poly")[0].checked = false;
        }
        
        if ($('.check-poly:checked').length == $('.check-poly').length ){ 
        $("#select_all_poly")[0].checked = true;
        }
    });
}

function check_poly(category) {
	prom_check_poly(category).then(function (hasil) {
		prom_clearPoly().then(function (data) {
			create_poly(hasil,category);
		});
	});
}

function prom_check_poly(category) {
	return new Promise(function (resolve, reject) {
		var check_poly = [];
		setTimeout(() => {
            let arr
			$('input[name="poly"]:checked').each(function () {
                // alert(typeof(this.value))
                switch (category) {
                    case "provinsi" :
                        arr = provinsi.find(o => o.prov_id === parseInt(this.value))
                        break;
                    case "kota" :
                        arr = kota.find(o => o.polres_id === this.value)
                        break;
                    case "kecamatan" :
                        arr = kecamatan.find(o => o.dis_id === parseInt(this.value))
                        break;
                    
                    case "kelurahan" : 
                        arr = kelurahan.find(o => o.subdis_id === parseInt(this.value))
                        break;

                    default:
                        break;
                }
                check_poly.push(arr)
			});
			resolve(check_poly);
		}, 300);
	});
}

function create_poly(data,category) {
    return new Promise(function (resolve, reject) {
        poly_arr = [];
        let paths =[]
        let arr = []
        let arr2 = []
        let title
        let filters = {}
        for (let i = 0; i < data.length; i++) {
            switch (category) {
                case "provinsi" :
                    title = 'PROVINSI '+data[i].prov_name
                    filters = {"polda_id" : data[i].polda_id, "polda" : data[i].polda}
                    break;
                
                case "kota" :
                    title = 'KOTA '+data[i].city_name
                    filters = {"polda_id" : data[i].polda_id, "polda" : data[i].polda, "polres_id" : data[i].polres_id, "polres" : data[i].polres}
                    break;
                
                case "kecamatan" :
                    title = 'KECAMATAN '+data[i].dis_name
                    break;

                case "kelurahan" :
                    title = 'KELURAHAN '+data[i].subdis_name
                    break;

                default:
                    break;
            }
            for (let c = 0; c < data[i].polygon.length; c++) {
                    data[i].polygon[c][0].forEach((v,b) => {
                        // console.log(v);
                        arr.push({ lat : v[1], lng : v[0]})
                        // console.log(b+' = '+v);
                    });
                    paths.push(arr)
                    arr = []

                    poly = new google.maps.Polygon({
                        paths: paths[c],
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 1,
                        // fillColor: "#FF0000",
                        // fillOpacity: 0.35,
                        name: title,
                        filters : filters
                    });

                    // console.log(poly);
                    
                    
                    poly.setMap(map);
                    poly.addListener("click", showInfowindowPolygon);
                    arr2.push(poly)
                    poly_arr.push(poly)
                  }
                  paths = []
                  poly_arr.push(arr2)
                  // console.log(poly_arr);
                  arr2 =[]
        }
        resolve(poly_arr)
    })
}

function showInfowindowPolygon(event) {
    if (infoWindowPoly != undefined) {
        infoWindowPoly.close();
    }
    infoWindowPoly = new google.maps.InfoWindow({minWidth: 500});
    
    let jml_badge = ''
    let category = this.name.split(" ")

    // if (bs.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < bs.length; i++) {
    //             bs[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < bs.length; i++) {
    //             bs[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-danger me-1"><img src="./my/satupeta/blackspot2.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`

    // }

    // if (ts.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < ts.length; i++) {
    //             ts[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < ts.length; i++) {
    //             ts[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-warning me-1"><img src="./my/satupeta/troublespot2.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`
    // }

    // if (rl.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < rl.length; i++) {
    //             rl[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < rl.length; i++) {
    //             rl[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-warning me-1"><img src="./my/satupeta/rawan-laka.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`
    // }

    // if (rm.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < rm.length; i++) {
    //             rm[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < rm.length; i++) {
    //             rm[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-warning me-1"><img src="./my/satupeta/rawan-macet.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`
    // }

    // if (rlong.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < rlong.length; i++) {
    //             rlong[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < rlong.length; i++) {
    //             rlong[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-warning me-1"><img src="./my/satupeta/rawan-longsor.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`
    // }

    // if (rb.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < rb.length; i++) {
    //             rb[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < rb.length; i++) {
    //             rb[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-warning me-1"><img src="./my/satupeta/rawan-banjir.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`
    // }

    // if (rpt.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < rpt.length; i++) {
    //             rpt[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < rpt.length; i++) {
    //             rpt[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-warning me-1"><img src="./my/satupeta/rawan-pohontumbang.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`
    // }

    // if (rtp.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < rtp.length; i++) {
    //             rtp[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < rtp.length; i++) {
    //             rtp[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-warning me-1"><img src="./my/satupeta/rawan-tindakpidana.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`
    // }

    // if (ag.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < ag.length; i++) {
    //             ag[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < ag.length; i++) {
    //             ag[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-secondary me-1"><img src="./my/satupeta/ambanggangguan.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`
    // }

    // if (gm.length != '') {
    //     let total = 0
    //     if (category[0] == "PROVINSI") {
    //         for (let i = 0; i < gm.length; i++) {
    //             gm[i].forEach(e => {
    //                 if (e.polda_id == this.filters.polda_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }

    //     if (category[0] == "KOTA") {
    //         for (let i = 0; i < gm.length; i++) {
    //             gm[i].forEach(e => {
    //                 if (e.polres_id == this.filters.polres_id) {
    //                     total++
    //                 }
    //             });
    //         }
    //     }
    //     jml_badge += `<div class="col mt-1" style="width:13.888889%!important;"><span class="badge rounded-pill text-bg-primary me-1"><img src="./my/satupeta/giatmasyarakat.png" class="jml_data_img"> (<span><b>${total}</b></span>)</span></div>`
    // }

    var contentString =
      `<div class="border-bottom pb-2">
        <b>${this.name}</b>
      </div>
      <div class="container">
        <div class="row row-cols-6 mt-2">
            ${jml_badge}
        </div>
      </div>
      `

    // get_data_algors().then((data) => {
    //     console.log(data);
    //     console.log(Object.values(data.data.grafik_bar_polda.cfr[0].value));
    //     console.log(Object.keys(data.data.grafik_bar_polda.cfr[0].value));
    //     bar_chart('#chart_tes',{
    //         series: [{name:'cfr' ,data: Object.values(data.data.grafik_bar_polda.cfr[0].value)}],
    //         categories: Object.keys(data.data.grafik_bar_polda.cfr[0].value),
    //         sidetext: data.data.grafik_bar_polda.cfr[0].da_nam,
    //         text: 'tes2',
    //     },'render')
    // })
  
    // Replace the info window's content and position.
    infoWindowPoly.setContent(contentString);
    infoWindowPoly.setPosition(event.latLng);
    infoWindowPoly.open(map);
  }

function prom_clearPoly() {
	return new Promise(function (resolve, reject) {
    if (poly_arr) {
			for (let i = 0; i < poly_arr.length; i++) {
          for (let c = 0; c < poly_arr[i].length; c++) {
            poly_arr[i][c].setMap(null);
            
          }
      }
      poly_arr = []
		}
    resolve(poly_arr);
	});
}

function search_data_wil() {
	let f_ = $('#search-data-wil').val().toLowerCase()
	let i_ = $('.data-check-wil>.list-group-item')
	for(var i = 0; i<i_.length; i++){
		let span = $('.list-group-item>span')[i];
		if(span.innerHTML.toLowerCase().indexOf(f_) > -1){
			i_[i].style.display = "";
		}else{
			i_[i].style.display = "none";
		}
	}
}

function get_data_algors(url="", params={}) {
    return new Promise(function (resolve, reject) {
        let u = []
        let s = []
        
        var params = {
                    year_from : "2020",
                    year_to : "2025",
                    polda : '14',
                    polres : '',
                    per : '',
                    item : 'grafik_bar_polda',
                    jalan : ''
                }
        var url = `http://192.168.8.28/backend_statistik/API/Keselamatan/cfr?`
        
        filter_obj = function (model) {
            const asArray = Object.entries(model);
            const filtered = asArray.filter(([key, value]) => value != undefined && value != "null");
            const justStrings = Object.fromEntries(filtered);
            return justStrings;
        }
        
        if (params) {
            let m =  filter_obj(params);
            let no = 1;
            for (const key in m) {
                if (Object.hasOwnProperty.call(m, key)) {
                    const element = m[key];
                    u.push(key+'='+element);
                    s.push(element)
                }
            no++;
            }

            if (s.length != 0) {
                url += `${u.join('&')}`;
            }
        }

        console.log(url);
        getDataApi(url, {} , '880f6ec57cd2d7f387020fcb94af7f82').then((data) => {
            resolve(data)
        })

    })
}