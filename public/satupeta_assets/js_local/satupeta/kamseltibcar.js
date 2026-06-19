var poly2 = []
var poly_arr2 = []
var infoWindowPoly2

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

function  get_wilayah2() {
    var value = $('#optwil2').val();
    var uprov = $('#prov').val();
    $('#wil2').html('');
    $('#data-wil2').html('')
    if (value != '') {
        if (uprov != '') {
            uprov = `prov_name=${uprov.toUpperCase()}`
        }
        switch (value) {
            case "prov":
                get_provinsi(uprov).then((data) => {
                    show_check_wil2('provinsi')
                })
                break;
            case 'kota':
                $('#wil2').append(`
                <label for="">Provinsi</label>
                <select name="provinsi" id="provinsi2" class="form-control" onchange="show_check_wil2('kota')" style="height: 34px!important;">
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

            default:
                break;
        }
    }
}

function show_check_wil2(category) {
    $('#data-wil2').html('')
    $('#data-wil2').append(`
        <div style="height:200px;overflow-y:auto;">
            <div class="card">
            <div class="card-header">
                <div class="row mx-0" style="text-align:left!important;">
                <div class="col-4 pt-2" id="check-select-all-poly2">
                    
                </div>
                    <div class="col">
                    <input type="text" class="form-control" placeholder="Cari" id="search-data-wil2" onkeyup="search_data_wil2()">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush data-check-wil" id="data-check-wil2">
                </ul>
            </div>
            </div>
        </div>
        `)
    $('#data-check-wil2').html('')
    if (category == 'provinsi') {
        $('#check-select-all-poly2').html('')
        $('#check-select-all-poly2').append(`
            <input type="checkbox" class="form-control-check" id="select_all_poly2" onchange="check_poly2('provinsi')"><span class="ms-1">All</span>
        `)
        provinsi.forEach((v) => {
            $('#data-check-wil2').append(`
                <li class="list-group-item">
                    <input type="checkbox" class="me-2 check-poly2" name="poly2" onchange="check_poly2('provinsi')" value="${v.prov_id}">
                    <span class="">${v.prov_name}</span>
                </li>
            `)
        })
    }

    if (category == 'kota') {
        $('#check-select-all-poly2').html('')
        $('#check-select-all-poly2').append(`
            <input type="checkbox" class="form-control-check" id="select_all_poly2" onchange="check_poly2('kota')"><span class="ms-1">All</span>
        `)
        get_kota(`prov_id=${$('select[name="provinsi"]').val()}`).then((data) => {
            data.forEach((v) => {
                $('#data-check-wil2').append(`
                    <li class="list-group-item">
                        ${v.polygon != null ? `
                        <input type="checkbox" class="me-2 check-poly2" name="poly2" onchange="check_poly2('kota')" value="${v.polres_id}">
                        <span class="">${v.city_name} / ${v.polres}</span>` 
                        : `
                        <span class="">${v.city_name} / ${v.polres}</span> 
                        `}
                    </li>
                `)
            })
        })
    }

    //select all polygon
    $("#select_all_poly2").change(function(){
        var status = this.checked;
        $('.check-poly2').each(function(){
        this.checked = status;
        });
    });

    $('.check-poly2').change(function(){ 
        if(this.checked == false){
        $("#select_all_poly2")[0].checked = false;
        }
        
        if ($('.check-poly2:checked').length == $('.check-poly2').length ){ 
        $("#select_all_poly2")[0].checked = true;
        }
    });
}

function check_poly2(category) {
	prom_check_poly2(category).then(function (hasil) {
		prom_clearPoly2().then(function (data) {
			create_poly2(hasil,category);
		});
	});
}

function prom_check_poly2(category) {
	return new Promise(function (resolve, reject) {
		var check_poly2 = [];
		setTimeout(() => {
            let arr
			$('input[name="poly2"]:checked').each(function () {
                // alert(typeof(this.value))
                switch (category) {
                    case "provinsi" :
                        arr = provinsi.find(o => o.prov_id === parseInt(this.value))
                        break;
                    case "kota" :
                        arr = kota.find(o => o.polres_id === this.value)
                        break;

                    default:
                        break;
                }
                check_poly2.push(arr)
			});
			resolve(check_poly2);
		}, 300);
	});
}

function create_poly2(data,category) {
    return new Promise(function (resolve, reject) {
        poly_arr2 = [];
        let paths =[]
        let arr = []
        let arr2 = []
        let title
        let wilayah
        let filters = {}
        for (let i = 0; i < data.length; i++) {
            switch (category) {
                case "provinsi" :
                    title = 'PROVINSI '+data[i].prov_name
                    wilayah = data[i].prov_name
                    filters = {"polda_id" : data[i].polda_id, "polda" : data[i].polda}
                    break;
                
                case "kota" :
                    title = 'KOTA '+data[i].city_name
                    wilayah = data[i].city_name
                    filters = {"polda_id" : data[i].polda_id, "polda" : data[i].polda, "polres_id" : data[i].polres_id, "polres" : data[i].polres}
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

                    poly2 = new google.maps.Polygon({
                        paths: paths[c],
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 1,
                        // fillColor: "#FF0000",
                        // fillOpacity: 0.35,
                        name: title,
                        wilayah : wilayah,
                        filters : filters
                    });

                    // console.log(poly);
                    
                    
                    poly2.setMap(map);
                    poly2.addListener("click", showInfowindowPolygon2);
                    arr2.push(poly2)
                    poly_arr2.push(poly2)
                  }
                  paths = []
                  poly_arr2.push(arr2)
                  // console.log(poly_arr2);
                  arr2 =[]
        }
        resolve(poly_arr2)
    })
}

function showInfowindowPolygon2(event) {
    if (infoWindowPoly2 != undefined) {
        infoWindowPoly2.close();
    }
    infoWindowPoly2 = new google.maps.InfoWindow();
    
    let category = this.name.split(" ")


    var contentString =
      `<div class="border-bottom pb-2">
        <b>${this.name}</b>
      </div>
      <div class="container">
        <table class="w-100" id="t_tableau">
        </table>
      </div>
      `

    showToast()
    setTimeout(() => {
        get_data_tableau_global_dashboard(this.name.split(' ')[0],this.wilayah)
    }, 100);
  
    // Replace the info window's content and position.
    infoWindowPoly2.setContent(contentString);
    infoWindowPoly2.setPosition(event.latLng);
    infoWindowPoly2.open(map);
  }

function prom_clearPoly2() {
	return new Promise(function (resolve, reject) {
    if (poly_arr2) {
			for (let i = 0; i < poly_arr2.length; i++) {
          for (let c = 0; c < poly_arr2[i].length; c++) {
            poly_arr2[i][c].setMap(null);
            
          }
      }
      poly_arr2 = []
		}
    resolve(poly_arr2);
	});
}

function search_data_wil2() {
	let f_ = $('#search-data-wil2').val().toLowerCase()
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

function get_data_tableau_global_dashboard(cat='',wilayah='') {
    var filter = ''
    let t_s = $('#f_date_from').val()
	let t_e = $('#f_date_to').val()
	if (t_s != '' && t_s != undefined) {
		filter += `&start_date=${t_s}`
	}
	if (t_e != '' && t_e != undefined) {
		filter += `&end_date=${t_e}`
	}
    if (cat == "PROVINSI") {
        filter += `&provinsi=${wilayah}`
    }
    if (cat == "KOTA") {
        filter += `&kota=${wilayah}`
    }
	return new Promise(function (resolve, reject) {
        // showToast()
        $('#t_tableau').html()
		getDataApi(`${global_url_api}sm_integration/kamseltibcar?${filter}`, {}, token_sm).then((data) => {
			if (data.status != false) {
                data.data.forEach(e => {
                    $('#t_tableau').append(`
                        <tr>
                            <td><b>${e.form_data}</b></td>
                            <td>:</td>
                            <td>${parseFloat(e.jumlah) === Math.floor(parseFloat(e.jumlah)) ? e.jumlah : parseFloat(e.jumlah).toFixed(2)}</td>
                        </tr>
                    `)
                });
			}else {
                $('#t_tableau').append(`
                <tr>
                    <td><b>Keamanan</b></td>
                    <td>:</td>
                    <td>0</td>
                </tr>
                <tr>
                    <td><b>Ketertiban</b></td>
                    <td>:</td>
                    <td>0</td>
                </tr>
                <tr>
                    <td><b>Keselamatan</b></td>
                    <td>:</td>
                    <td>0</td>
                </tr>
                <tr>
                    <td><b>Kelancaran</b></td>
                    <td>:</td>
                    <td>0</td>
                </tr>
                `)
			}
			closeToast()
		})
	})
}