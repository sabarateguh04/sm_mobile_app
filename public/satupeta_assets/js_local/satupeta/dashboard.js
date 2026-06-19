var poly = []
var poly_arr = []
var infoWindowPoly
var hirarki
var pd_ctv
var ps_ctv

function GetURLParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split("&");
	for (var i = 0; i < sURLVariables.length; i++) {
		var sParameterName = sURLVariables[i].split("=");
		if (sParameterName[0] == sParam) {
			return sParameterName[1];
		}
	}
}

// let u = GetURLParameter("u")
let ctg = GetURLParameter("ctg")
var urlParameter = {
  ctg : ctg,
};

$(function () {
    setInterval(() => {
      cek_sso(`${id_unique}`)
    }, 3000);
    let polda_id = $('#polda_id').val()
    let polres_id = $('#polres_id').val()
    let kota = $('#kot').val()
    if (polda_id == '' && polres_id == '') {
        get_provinsi().then((data) => {
            console.log('berhasil mengambil data seluruh provinsi');
        })
        hirarki = 'mabes'
        check_poly('provinsi')
    } else if (polda_id != '' && polres_id == '') {
        get_kota(`polda_id=${polda_id}`).then((data) => {
            console.log('berhasil mengambil data kota');
        })
        hirarki = 'polda'
        check_poly('kota')
        if (ctg == 'traffic') {
            $('#menu-panel').removeClass('d-none')
        }
    } else if (polda_id != '' && polres_id != '') {
        get_kota(`polda_id=${polda_id}&city_name=${kota.toUpperCase()}`).then((data) => {
            console.log('berhasil mengambil data kota');
        })
        hirarki = 'polres'
        check_poly('kota')
        if (ctg == 'traffic') {
            $('#menu-panel').removeClass('d-none')
        }
    }

});

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
		// setTimeout(() => {
            let arr
			// $('input[name="poly"]:checked').each(function () {
                // alert(typeof(this.value))
                switch (category) {
                    case "provinsi" :
                        arr = provinsi
                        break;
                    case "kota" :
                        arr = kota
                        break;

                    default:
                        break;
                }
                check_poly.push(arr)
			// });
			resolve(check_poly);
		// }, 300);
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
        setTimeout(() => {
            for (let y = 0; y < data.length; y++) {
                for (let i = 0; i < data[y].length; i++) {
                    switch (category) {
                        case "provinsi" :
                            title = 'POLDA '+data[y][i].prov_name
                            filters = {"prov_id" : data[y][i].prov_id,"polda_id" : data[y][i].polda_id, "polda" : data[y][i].polda}
                            break;
                        
                        case "kota" :
                            title = 'POLRES '+data[y][i].city_name
                            filters = {"prov_id" : data[y][i].prov_id,"polda_id" : data[y][i].polda_id, "polda" : data[y][i].polda, "polres_id" : data[y][i].polres_id, "polres" : data[y][i].polres}
                            break;
        
                        default:
                            break;
                    }
                    if (data[y][i].polygon != null && data[y][i].polygon != '') {
                        var randomColor = Math.floor(Math.random()*16777215).toString(16);
                        for (let c = 0; c < data[y][i].polygon.length; c++) {
                                data[y][i].polygon[c][0].forEach((v,b) => {
                                    // console.log(v);
                                    arr.push({ lat : v[1], lng : v[0] })
                                    // console.log(b+' = '+v);
                                });
                                paths.push(arr)
                                arr = []
            
                                poly = new google.maps.Polygon({
                                    paths: paths[c],
                                    strokeColor: `#${randomColor}`,
                                    strokeOpacity: 0.8,
                                    strokeWeight: 1,
                                    fillColor: `#${randomColor}`,
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
                    }
                          paths = []
                          poly_arr.push(arr2)
                          // console.log(poly_arr);
                          arr2 =[]
                }
            }
        }, 1000);
        resolve(poly_arr)
    })
}

function showInfowindowPolygon(event) {
    let filters = JSON.stringify(this.filters)
    if (infoWindowPoly != undefined) {
        infoWindowPoly.close();
    }
    if (hirarki == 'mabes') {
        map.setCenter(new google.maps.LatLng(event.latLng.lat(), event.latLng.lng()));
        map.setZoom(10);
    }else if(hirarki == 'polda'){
        map.setCenter(new google.maps.LatLng(event.latLng.lat(), event.latLng.lng()));
        map.setZoom(13);
    }
    // map.setCenter({ lat : parseFloat(event.latLng.lat), lng : parseFloat(event.latLng.lng)});
    infoWindowPoly = new google.maps.InfoWindow({minWidth: 500});
    let jml_badge = ''
    let category = this.name.split(" ")

    var contentString =
      `<div class="border-bottom pb-2">
        <b>${this.name}</b>&nbsp;
        ${(hirarki=='mabes' || hirarki == 'polda') ? `<button class="btn btn-sm btn-primary" onclick='detail_poly(${filters})'>Detail</button>` : ''}
      </div>
      <div class="container">
        <div id="chart_dashboard"></div>
      </div>
      `

    get_data_dashboard('',this.filters).then((data) => {
        if (ctg == 'peristiwa') {
            setTimeout(() => {
                pie_chart('#chart_dashboard',{
                    data: data.data[0][`grafik_${ctg}`][0].data,
                    name: data.data[0][`grafik_${ctg}`][0].name,
                },'render')
            }, 200);
        }else if (ctg == 'traffic') {
            setTimeout(() => {
                bar_chart('#chart_dashboard',{
                    series: [{ data :data.data[0][`grafik_${ctg}`][0].data }],
                    categories: data.data[0][`grafik_${ctg}`][0].name,
                },'render')
            }, 200);
        }else if (ctg == 'accident') {
            setTimeout(() => {
                $('#chart_dashboard').append(`
                    <table class="table table-bordered mt-2" id="tbl_dsh">
                        <thead>
                            <tr id='t_head_dsh'></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                `)
                for (let i = 0; i < Object.keys(data.data[0][`grafik_${ctg}`][0]).length; i++) {
                    $('#t_head_dsh').append(`
                        <th>${Object.keys(data.data[0][`grafik_${ctg}`][0])[i]}</th>
                    `)
                }

                let datass = []
                let dataset = []
                data.data[0][`grafik_${ctg}`].forEach((e, i) => {
                    datass.push(Object.values(e));
                });
                for (let i = 0; i < datass.length; i++) {
                    dataset.push(datass[i].map(String))
                }
                $('#tbl_dsh').DataTable({
                    data: dataset,
                    columns: [
                        { title: 'No' },
                        { title: 'Total Kecelakaan' },
                        { title: 'MD' },
                        { title: 'LB.' },
                        { title: 'LR' },
                        { title: 'KERMAT' },
                    ],
                });
            }, 200);
        }else if (ctg == 'people_mobility') {
            setTimeout(() => {
                $('#chart_dashboard').append(`
                    <table class="table table-bordered mt-2" id="tbl_dsh">
                        <thead>
                            <tr id='t_head_dsh'></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                `)
                for (let i = 0; i < Object.keys(data.data[0][`grafik_${ctg}`][0]).length; i++) {
                    $('#t_head_dsh').append(`
                        <th>${Object.keys(data.data[0][`grafik_${ctg}`][0])[i]}</th>
                    `)
                }

                let datass = []
                let dataset = []
                data.data[0][`grafik_${ctg}`].forEach((e, i) => {
                    datass.push(Object.values(e));
                });
                for (let i = 0; i < datass.length; i++) {
                    dataset.push(datass[i].map(String))
                }
                $('#tbl_dsh').DataTable({
                    data: dataset,
                    columns: [
                        { title: 'No' },
                        { title: 'Tanggal' },
                        { title: 'Fasilitas' },
                        { title: 'Kedatangan.' },
                        { title: 'Keberangkatan' },
                    ],
                });
            }, 200);
        }else if (ctg == 'wilayah_rawan') {
            setTimeout(() => {
                bar_chart('#chart_dashboard',{
                    series: [{ data :data.data[0][`grafik_${ctg}`][0].data }],
                    categories: data.data[0][`grafik_${ctg}`][0].name,
                },'render')
            }, 200);
        }
    })
  
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

function get_data_dashboard(url="", params={}) {
    var hak_akses
    var polda_id
    var polres_id
    let t_s = $('#f_date_from').val()
	let t_e = $('#f_date_to').val()
    if (params.polres_id != undefined) {
        hak_akses = 'polres'
        polda_id = params.polda_id
        polres_id = params.polres_id
    }else {
        hak_akses = 'polda'
        polda_id = params.polda_id
    }
    return new Promise(function (resolve, reject) {
        let u = []
        let s = []
        
        var params = {
                    hak_akses : hak_akses,
                    tgl_start : t_s,
                    tgl_end : t_e,
                    polda_id : polda_id,
                    polres_id : polres_id,
                }
        var url = `${global_url_api}sm_smarts_ops/dashboard_eksekutif?`
        
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
        getDataApi(url, {} , '5b3dac76aaee24d14185cbc3d010fd20').then((data) => {
            resolve(data)
        })

    })
}

function detail_poly(filters) {
    infoWindowPoly.close();
    if (filters.polres_id != undefined) {
        get_kota(`prov_id=${filters.prov_id}&city_name=${filters.polres.replace(/POLRES |POLRESTA /gi, "")}`).then((data) => {
            console.log('berhasil mengambil data kota');
        })
        hirarki = 'polres'
        ps_ctv = filters.polres_id
        filters.hirarki = '1' // status_approved 1 = polres
        if (ctg == 'peristiwa') {
            show_titik_dashboard('peristiwa',filters)
        }else if (ctg == 'traffic') {
            $('#menu-panel').removeClass('d-none')
            show_titik_dashboard('traffic',filters)
        }else if (ctg == 'accident') {
            show_titik_dashboard('accident',filters)
        }else if (ctg == 'people_mobility') {
            show_titik_dashboard('people_mobility',filters)
        }else if (ctg == 'wilayah_rawan') {
            show_titik_dashboard('wilayah_rawan',filters)
        }
        check_poly('kota')
    }else {
        get_kota(`prov_id=${filters.prov_id}`).then((data) => {
            console.log('berhasil mengambil data kota');
        })
        hirarki = 'polda'
        pd_ctv = filters.polda_id
        filters.hirarki = '2' // status_approved 2 = polda
        if (ctg == 'peristiwa') {
            show_titik_dashboard('peristiwa',filters)
        }else if (ctg == 'traffic') {
            $('#menu-panel').removeClass('d-none')
            show_titik_dashboard('traffic',filters)
        }else if (ctg == 'accident') {
            show_titik_dashboard('accident',filters)
        }else if (ctg == 'people_mobility') {
            show_titik_dashboard('people_mobility',filters)
        }else if (ctg == 'wilayah_rawan') {
            show_titik_dashboard('wilayah_rawan',filters)
        }
        check_poly('kota')
    }
}

var prta = []
var clr_prta = []
var trf = []
var clr_trf = []
var act = []
var clr_act = []
var pemo = []
var clr_pemo = []
var wraw = []
var clr_wraw = []

function show_titik_dashboard(item="",filters) {
    var filter = ''
    let t_s = $('#f_date_from').val()
	let t_e = $('#f_date_to').val()
    let da = filters.polda_id
    let res = filters.polres_id
	if (da != '' && da != undefined) {
		filter += `&polda_id=${da}`
	}
	if (res != '' && res != undefined) {
		filter += `&polres_id=${res}`
	}
	if (t_s != '' && t_s != undefined) {
		filter += `&tgl_start=${t_s}`
	}
	if (t_e != '' && t_e != undefined) {
		filter += `&tgl_end=${t_e}`
	}
    if (filters.hirarki != '' && filters.hirarki != undefined) {
		filter += `&status=${filters.hirarki}`
	}
    
    let g_url = global_url_api // server prod bali

    setTimeout(() => {
      if (item == "peristiwa") {
        let url = g_url + `sm_smarts_ops/laphar_ops?${filter}`
        varr = prta;
        if (prta.length != '') {
            clearMarkerTitikDashboard(item);
        }
        //   icon = "./my/satupeta/blackspot2.png";
          icon = "";
          showToast()
          getDataSmNew(url).then(r => {
            if (r.status == true) {
              titik_link = r
              create_titik_map_dashboard(item, icon, r.data).then(e => {
                // $('#jml_data_group').append(`
                //   <div class="jml_data mt-2" id="jml_data_item_${item}">
                //     <span id="jml_data">Jumlah Black Spot <img src="./my/satupeta/blackspot2.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                //   </div>
                // `)
                // const obj = document.getElementById(`counter_${item}`);
                // animateValue(obj, 0, r.data.length, 200)
                closeToast()
              });
            }else {
                if (prta.length != '') {
                    clearMarkerTitikDashboard(item);
                }
              swal(
                'Warning !',
                'Data Lokasi Peristiwa Tidak Ditemukan !',
                'warning'
              );
              closeToast()
            }
          });
      }else if (item == "traffic") {
        let url = g_url + `sm_smarts_ops/laphar_ops?${filter}`
        varr = trf;
        if (trf.length != '') {
            clearMarkerTitikDashboard(item);
        }
        //   icon = "./my/satupeta/blackspot2.png";
          icon = "";
          showToast()
          getDataSmNew(url).then(r => {
            if (r.status == true) {
              titik_link = r
              create_titik_map_dashboard(item, icon, r.data).then(e => {
                // $('#jml_data_group').append(`
                //   <div class="jml_data mt-2" id="jml_data_item_${item}">
                //     <span id="jml_data">Jumlah Black Spot <img src="./my/satupeta/blackspot2.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                //   </div>
                // `)
                // const obj = document.getElementById(`counter_${item}`);
                // animateValue(obj, 0, r.data.length, 200)
                closeToast()
              });
            }else {
                if (trf.length != '') {
                    clearMarkerTitikDashboard(item);
                }
              swal(
                'Warning !',
                'Data Lokasi Peristiwa Tidak Ditemukan !',
                'warning'
              );
              closeToast()
            }
          });
      }else if (item == "accident") {
        let url = g_url + `sm_smarts_ops/laphar_ops?sub_kategori_id=8${filter}`
        varr = act;
        if (act.length != '') {
            clearMarkerTitikDashboard(item);
        }
        //   icon = "./my/satupeta/blackspot2.png";
          icon = "";
          showToast()
          getDataSmNew(url).then(r => {
            if (r.status == true) {
              titik_link = r
              create_titik_map_dashboard(item, icon, r.data).then(e => {
                // $('#jml_data_group').append(`
                //   <div class="jml_data mt-2" id="jml_data_item_${item}">
                //     <span id="jml_data">Jumlah Black Spot <img src="./my/satupeta/blackspot2.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                //   </div>
                // `)
                // const obj = document.getElementById(`counter_${item}`);
                // animateValue(obj, 0, r.data.length, 200)
                closeToast()
              });
            }else {
                if (act.length != '') {
                    clearMarkerTitikDashboard(item);
                }
              swal(
                'Warning !',
                'Data Lokasi Peristiwa Tidak Ditemukan !',
                'warning'
              );
              closeToast()
            }
          });
      }else if (item == "people_mobility") {
        let url = g_url + `sm_smarts_ops/people_mobility?${filter}`
        varr = pemo;
        if (pemo.length != '') {
            clearMarkerTitikDashboard(item);
        }
        //   icon = "./my/satupeta/blackspot2.png";
          icon = "";
          showToast()
          getDataSmNew(url).then(r => {
            if (r.status == true) {
              titik_link = r
              create_titik_map_dashboard(item, icon, r.data).then(e => {
                // $('#jml_data_group').append(`
                //   <div class="jml_data mt-2" id="jml_data_item_${item}">
                //     <span id="jml_data">Jumlah Black Spot <img src="./my/satupeta/blackspot2.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                //   </div>
                // `)
                // const obj = document.getElementById(`counter_${item}`);
                // animateValue(obj, 0, r.data.length, 200)
                closeToast()
              });
            }else {
                if (pemo.length != '') {
                    clearMarkerTitikDashboard(item);
                }
              swal(
                'Warning !',
                'Data Lokasi Peristiwa Tidak Ditemukan !',
                'warning'
              );
              closeToast()
            }
          });
      }else if (item == "wilayah_rawan") {
        let url = g_url + `sm_smarts_ops/wilayah_rawan?${filter}`
        varr = wraw;
        if (wraw.length != '') {
            clearMarkerTitikDashboard(item);
        }
        //   icon = "./my/satupeta/blackspot2.png";
          icon = "";
          showToast()
          getDataSmNew(url).then(r => {
            if (r.status == true) {
              titik_link = r
              create_titik_map_dashboard(item, icon, r.data).then(e => {
                // $('#jml_data_group').append(`
                //   <div class="jml_data mt-2" id="jml_data_item_${item}">
                //     <span id="jml_data">Jumlah Black Spot <img src="./my/satupeta/blackspot2.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                //   </div>
                // `)
                // const obj = document.getElementById(`counter_${item}`);
                // animateValue(obj, 0, r.data.length, 200)
                closeToast()
              });
            }else {
                if (wraw.length != '') {
                    clearMarkerTitikDashboard(item);
                }
              swal(
                'Warning !',
                'Data Lokasi Peristiwa Tidak Ditemukan !',
                'warning'
              );
              closeToast()
            }
          });
      }
    }, 150);
  
  }

  function create_titik_map_dashboard(item, img, arr) {
    return new Promise(function (resolve, reject) {
          titik_arr = [];
          let m_titik = [];
      
          var icon = {
                url: img, // url
                scaledSize: new google.maps.Size(30, 30), // scaled size
            };
  
      
      for (let i = 0; i < titik_link.data.length; i++) {
        // window.setTimeout(() => {
          
          m_titik.push(
            new google.maps.Marker({
              position: { lat : parseFloat(titik_link.data[i].lat), lng : parseFloat(titik_link.data[i].lng)},
              map,
            //   icon : icon,
              animation: google.maps.Animation.DROP,
              polda_id : titik_link.data[i].polda_id,
              polres_id : titik_link.data[i].polres_id
            })
          );
        // }, i * 200);
      }
  
      if (item == "peristiwa") {
        prta.push(m_titik);
        clr_prta.push(new MarkerClusterer( map, prta[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
        setInfoWindowsDashboard(arr, prta[0], "Peristiwa")
      }

      if (item == "traffic") {
        trf.push(m_titik);
        clr_trf.push(new MarkerClusterer( map, trf[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
        setInfoWindowsDashboard(arr, trf[0], "Traffic")
      }

      if (item == "accident") {
        act.push(m_titik);
        clr_act.push(new MarkerClusterer( map, act[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
        setInfoWindowsDashboard(arr, act[0], "Accident")
      }

      if (item == "people_mobility") {
        pemo.push(m_titik);
        clr_pemo.push(new MarkerClusterer( map, pemo[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
        setInfoWindowsDashboard(arr, pemo[0], "People Mobility")
      }

      if (item == "wilayah_rawan") {
        pemo.push(m_titik);
        clr_pemo.push(new MarkerClusterer( map, pemo[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
        setInfoWindowsDashboard(arr, pemo[0], "Wilayah Rawan")
      }
  
      // new MarkerClusterer( map, m_titik ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'});
          resolve(titik_link.data);
      })
  }
  
  function clearMarkerTitikDashboard(arr) {
        if (arr == "peristiwa") {
            for (let i = 0; i < prta[0].length; i++) {
                prta[0][i].setMap(null);
            }
        prta = [];
        for (let i = 0; i < clr_prta.length; i++) {
        clr_prta[i].clearMarkers()
            }
        clr_prta = []
        }

        if (arr == "traffic") {
            for (let i = 0; i < trf[0].length; i++) {
                trf[0][i].setMap(null);
            }
        trf = [];
        for (let i = 0; i < clr_trf.length; i++) {
        clr_trf[i].clearMarkers()
            }
        clr_trf = []
        }

        if (arr == "accident") {
            for (let i = 0; i < act[0].length; i++) {
                act[0][i].setMap(null);
            }
        act = [];
        for (let i = 0; i < clr_act.length; i++) {
        clr_act[i].clearMarkers()
            }
        clr_act = []
        }

        if (arr == "people_mobility") {
            for (let i = 0; i < pemo[0].length; i++) {
                pemo[0][i].setMap(null);
            }
        pemo = [];
        for (let i = 0; i < clr_pemo.length; i++) {
        clr_pemo[i].clearMarkers()
            }
        clr_pemo = []
        }

        if (arr == "wilayah_rawan") {
            for (let i = 0; i < wraw[0].length; i++) {
                wraw[0][i].setMap(null);
            }
        wraw = [];
        for (let i = 0; i < clr_wraw.length; i++) {
        clr_wraw[i].clearMarkers()
            }
        clr_wraw = []
        }
    }

    function setInfoWindowsDashboard(arr,titik,n_titik) {
        const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
        infowindow.close(); // Close previously opened infowindow
      
        if (n_titik=="Peristiwa") {
            for (let i = 0; i < arr.length; i++) {
            google.maps.event.addListener(titik[i], 'click', function(){
                map.setZoom(15);
                map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
              infowindow.setContent(`
              <div><p><b>${n_titik+' '+arr[i].sub_kategori}</b></p>
              <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
              <table class="w-100">
              <tr>
              <td><b>Nama Jalan</b></td>
              <td>:</td>
              <td>${arr[i].nama_jalan}</td>
              </tr>
              <tr>
              <td><b>Kordinat</b></td>
              <td>:</td>
              <td>${arr[i].lat + "," + arr[i].lng}</td>
              </tr>
              <tr>
              <td><b>Tanggal</b></td>
              <td>:</td>
              <td>${isoDateToDate(arr[i].tanggal)}</td>
              </tr>
              <tr>
              <td><b>Korban MD</b></td>
              <td>:</td>
              <td>${arr[i].korban_md}</td>
              </tr>
              <tr>
              <td><b>Korban LB</b></td>
              <td>:</td>
              <td>${arr[i].korban_lb}</td>
              </tr>
              <tr>
              <td><b>Korban LR</b></td>
              <td>:</td>
              <td>${arr[i].korban_lr}</td>
              </tr>
              <tr>
              <td><b>Kermat</b></td>
              <td>:</td>
              <td>${arr[i].kermat}</td>
              </tr>
              </table>
              </div>
              `);
              infowindow.open(map, titik[i]);
            });
            }
          }else if (n_titik=="Traffic") {
            for (let i = 0; i < arr.length; i++) {
            google.maps.event.addListener(titik[i], 'click', function(){
                map.setZoom(15);
                map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
              infowindow.setContent(`
              <div><p><b>${n_titik+' Status'}</b></p>
              <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
              <table class="w-100">
              <tr>
              <td><b>Nama Jalan</b></td>
              <td>:</td>
              <td>${arr[i].nama_jalan}</td>
              </tr>
              <tr>
              <td><b>Kordinat</b></td>
              <td>:</td>
              <td>${arr[i].lat + "," + arr[i].lng}</td>
              </tr>
              <tr>
              <td><b>Tanggal</b></td>
              <td>:</td>
              <td>${isoDateToDate(arr[i].tanggal)}</td>
              </tr>
              <tr>
              <td><b>Situasi Lalin</b></td>
              <td>:</td>
              <td>${arr[i].situasi_lalin}</td>
              </tr>
              </table>
              </div>
              `);
              infowindow.open(map, titik[i]);
            });
            }
          }else if (n_titik=="Accident") {
            for (let i = 0; i < arr.length; i++) {
            google.maps.event.addListener(titik[i], 'click', function(){
                map.setZoom(15);
                map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
              infowindow.setContent(`
              <div><p><b>${n_titik+' '+arr[i].sub_kategori}</b></p>
              <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
              <table class="w-100">
              <tr>
              <td><b>Nama Jalan</b></td>
              <td>:</td>
              <td>${arr[i].nama_jalan}</td>
              </tr>
              <tr>
              <td><b>Kordinat</b></td>
              <td>:</td>
              <td>${arr[i].lat + "," + arr[i].lng}</td>
              </tr>
              <tr>
              <td><b>Tanggal</b></td>
              <td>:</td>
              <td>${isoDateToDate(arr[i].tanggal)}</td>
              </tr>
              <tr>
              <td><b>Korban MD</b></td>
              <td>:</td>
              <td>${arr[i].korban_md}</td>
              </tr>
              <tr>
              <td><b>Korban LB</b></td>
              <td>:</td>
              <td>${arr[i].korban_lb}</td>
              </tr>
              <tr>
              <td><b>Korban LR</b></td>
              <td>:</td>
              <td>${arr[i].korban_lr}</td>
              </tr>
              <tr>
              <td><b>Kermat</b></td>
              <td>:</td>
              <td>${arr[i].kermat}</td>
              </tr>
              </table>
              </div>
              `);
              infowindow.open(map, titik[i]);
            });
            }
          }else if (n_titik=="People Mobility") {
            for (let i = 0; i < arr.length; i++) {
            google.maps.event.addListener(titik[i], 'click', function(){
                map.setZoom(15);
                map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
              infowindow.setContent(`
              <div><p><b>${n_titik}</b></p>
              <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
              <table class="w-100">
              <tr>
              <td><b>Nama Jalan</b></td>
              <td>:</td>
              <td>${arr[i].nama_jalan}</td>
              </tr>
              <tr>
              <td><b>Nama Fasilitas</b></td>
              <td>:</td>
              <td>${arr[i].nama_fasilitas}</td>
              </tr>
              <tr>
              <td><b>Kordinat</b></td>
              <td>:</td>
              <td>${arr[i].lat + "," + arr[i].lng}</td>
              </tr>
              <tr>
              <td><b>Tanggal</b></td>
              <td>:</td>
              <td>${isoDateToDate(arr[i].tanggal)}</td>
              </tr>
              <tr>
              <td><b>Pengunjung Datang</b></td>
              <td>:</td>
              <td>${arr[i].pengunjung_datang}</td>
              </tr>
              <tr>
              <td><b>Pengunjung Berangkat</b></td>
              <td>:</td>
              <td>${arr[i].pengunjung_berangkat}</td>
              </tr>
              <tr>
              <td><b>Kendaraan Datang</b></td>
              <td>:</td>
              <td>${arr[i].kendaraan_datang}</td>
              </tr>
              <tr>
              <td><b>Kendaraan Berangkat</b></td>
              <td>:</td>
              <td>${arr[i].kendaraan_berangkat}</td>
              </tr>
              <tr>
              <td><b>Keterangan</b></td>
              <td>:</td>
              <td>${arr[i].keterangan}</td>
              </tr>
              </table>
              </div>
              `);
              infowindow.open(map, titik[i]);
            });
            }
          }else if (n_titik=="Wilayah Rawan") {
            for (let i = 0; i < arr.length; i++) {
            google.maps.event.addListener(titik[i], 'click', function(){
                map.setZoom(15);
                map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
              infowindow.setContent(`
              <div><p><b>${n_titik}</b></p>
              <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
              <table class="w-100">
              <tr>
              <td><b>Nama Jalan</b></td>
              <td>:</td>
              <td>${arr[i].nama_jalan}</td>
              </tr>
              <tr>
              <td><b>Kordinat</b></td>
              <td>:</td>
              <td>${arr[i].lat + "," + arr[i].lng}</td>
              </tr>
              <tr>
              <td><b>Tanggal</b></td>
              <td>:</td>
              <td>${isoDateToDate(arr[i].tanggal)}</td>
              </tr>
              <tr>
              <td><b>Kerawanan</b></td>
              <td>:</td>
              <td>${arr[i].kerawanan}</td>
              </tr>
              <tr>
              <td><b>Penyebab Kerawanan</b></td>
              <td>:</td>
              <td>${arr[i].penyebab_kerawanan}</td>
              </tr>
              <tr>
              <td><b>Keterangan</b></td>
              <td>:</td>
              <td>${arr[i].keterangan}</td>
              </tr>
              </table>
              </div>
              `);
              infowindow.open(map, titik[i]);
            });
            }
          }
      }

function cctv_cstm() {
    cctv(pd_ctv,ps_ctv)
}