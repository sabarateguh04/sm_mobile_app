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

let display = GetURLParameter("display")
let show_data = GetURLParameter("show_data")
let center = GetURLParameter("center")
let polda_id =  GetURLParameter("polda_id")
let polres_id =  GetURLParameter("polres_id")
let tgl_start =  GetURLParameter("tgl_start")
let tgl_end =  GetURLParameter("tgl_end")
let car = GetURLParameter("car")
let btn_out = GetURLParameter("btn_out")
let u_mgmt_route = GetURLParameter("mgmt_route")
var urlParameter = {
	display: display,
    show_data: show_data,
    center: center,
	polda_id: polda_id,
	polres_id: polres_id,
	tgl_start: tgl_start,
	tgl_end: tgl_end,
	car : car,
	btn_out : btn_out,
	u_mgmt_route : u_mgmt_route
};

var da,res,t_s,t_e
let rawan_arr =[];
let rawan_link =[];
var rawan = [];
var c = [];
var fasum = [];
var ov = [];
var rg = [];
var rop = [];
$(function () {
	if (urlParameter["display"] == "off") {
		$('#menu-panel').remove();
		$('#menu-filter').remove();
		$('#menu-trfflow').remove();
		$('#menu-trfkcl').remove();
		$('#h').remove();
	}
	if (urlParameter["polda_id"] != undefined && urlParameter["polda_id"] != "") {
		da = polda_id
	}
	if (urlParameter["polres_id"] != undefined && urlParameter["polres_id"] != "") {
		res = polres_id
	}
	if (urlParameter["tgl_start"] != undefined && urlParameter["tgl_start"] != "") {
		t_s = tgl_start
	}
	if (urlParameter["tgl_end"] != undefined && urlParameter["tgl_end"] != "") {
		t_e = tgl_end
	}
    if (urlParameter["show_data"] != undefined && urlParameter["show_data"] != "") {
        // alert(show_data)
		show_titik_new(show_data);
	}
	if (urlParameter["car"] != undefined && urlParameter["car"] != "") {
        // alert(car)
		get_mobil("");
	}
	if (urlParameter["btn_out"] == "off") {
		$('.button-logout').remove();
	}
	if (urlParameter["u_mgmt_route"] != undefined && urlParameter["u_mgmt_route"] != "") {
		$('#custom-content').append(`
	  <div id="mgmt_route" class="panel2" style="right:60px; z-index:10; height: 400px; overflow:auto;">
		<div class="ml-2 row mx-0" style="text-align:left!important;">
		  <div class="col-3">
			<button onclick="hapusMgmtRouteE()" class="btn btn-primary">Clear</button>
		  </div>
		  <div class="col">
			<input type="text" class="form-control" placeholder="Cari Route..." id="search_mgmt_route" onkeyup="search_mgmt_route()">
		  </div>
		</div>
		<ul class="list-group mt-2 pt-2 border-top text-start list_mgmt_route" id="list_mgmt_route">
		</ul>
	  </div>
		`)
        mgmt_route()
	}
    if (urlParameter["center"] != undefined && urlParameter["center"] != "") {
        let kordinat = center.split(",")
        // console.log(kordinat);
		let trafficLayer
        setTimeout(() => {
            map.setOptions({ center: { lat: parseFloat(kordinat[0]) , lng: parseFloat(kordinat[1]) } });
			const arn = map.styles.filter(function (x) {
				return x.featureType != 'poi' &&
					   x.featureType != 'poi.park' &&
					   x.featureType != 'transit' &&
					   x.featureType != 'transit.station' &&
					   x.featureType != 'transit.line'
			  });
		  
			  const arm = [...arn, ...styles.hiding]
			  map.setOptions({ styles: arm });
			trafficLayer = new google.maps.TrafficLayer();
			trafficLayer.setMap(map);
        }, 100);
    }
});


var circle_bs = []
var circle_ts = []
var clr_bs = []
var clr_ts = []
var clr_rawan = []
var clr_fasum = []
var clr_ov = []
var clr_rg = []
var clr_rop = []
function create_titik_map_new(item, img, arr) {
	return new Promise(function (resolve, reject) {
		  titik_arr = [];
		  let m_titik = [];
	  
		  var icon = {
		url: img, // url
		// url : "my/satupeta/blackspot2.png",
		scaledSize: new google.maps.Size(30, 30), // scaled size
	  };
  
	  
	  for (let i = 0; i < titik_link.data.length; i++) {
		// window.setTimeout(() => {
		  
		  m_titik.push(
			new google.maps.Marker({
			  position: { lat : parseFloat(titik_link.data[i].lat), lng : parseFloat(titik_link.data[i].lng)},
			  map,
			  icon : icon,
			  animation: google.maps.Animation.DROP,
			  polda_id : titik_link.data[i].polda_id,
			  polres_id : titik_link.data[i].polres_id
			})
		  );
		// }, i * 200);
		if (item == "black_spot") {
			circle_bs.push(new google.maps.Circle({
			  map: map,
			  // clickable: false,
			  // metres
			  radius: 250,
			  fillColor: '#F80404',
			  fillOpacity: .3,
			  strokeColor: '#313131',
			  strokeOpacity: .4,
			  strokeWeight: .8
			}))
			circle_bs[i].bindTo('center', m_titik[i], 'position');
		  }
		  if (item == "trouble_spot") {
			circle_ts.push(new google.maps.Circle({
			  map: map,
			  // clickable: false,
			  // metres
			  radius: 250,
			  fillColor: '#F80404',
			  fillOpacity: .3,
			  strokeColor: '#313131',
			  strokeOpacity: .4,
			  strokeWeight: .8
			}))
			circle_ts[i].bindTo('center', m_titik[i], 'position');
		  }
	  }
  
	  if (item == "black_spot") {
		bs.push(m_titik);
		clr_bs.push(new MarkerClusterer( map, bs[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
		setInfoWindowsNew(arr, bs[0], "Black Spot")
	  }
  
	  if (item == "trouble_spot") {
		ts.push(m_titik);
		clr_ts.push(new MarkerClusterer( map, ts[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
		setInfoWindowsNew(arr, ts[0], "Trouble Spot")
	  }

	  if (item == "rawan") {
		rawan.push(m_titik)
		clr_rawan.push(new MarkerClusterer( map, rawan[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
		setInfoWindowsNew(arr, rawan[0], "Rawan")
	  }

	  if (item == "cctv") {
		c.push(m_titik)
		setInfoWindowsNew(arr, c[0], "Cctv")
	  }

	  if (item == "fasum") {
		fasum.push(m_titik)
		clr_fasum.push(new MarkerClusterer( map, fasum[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
		setInfoWindowsNew(arr, fasum[0], "Fasum")
	  }

	  if (item == "objek_vital") {
		ov.push(m_titik)
		clr_ov.push(new MarkerClusterer( map, ov[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
		setInfoWindowsNew(arr, ov[0], "Objek Vital")
	  }

	  if (item == "rengiat") {
		rg.push(m_titik)
		clr_rg.push(new MarkerClusterer( map, rg[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
		setInfoWindowsNew(arr, rg[0], "Ren Giat")
	  }

	  if (item == "renops_pol") {
		rop.push(m_titik)
		clr_rop.push(new MarkerClusterer( map, rop[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
		setInfoWindowsNew(arr, rop[0], "Ren Ops Pol")
	  }
  
		  resolve(titik_link.data);
	  })
  }

  function setInfoWindowsNew(arr,titik,n_titik) {
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
	infowindow.close(); // Close previously opened infowindow
	if (n_titik=="Black Spot" || n_titik== "Trouble Spot") {
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
			<td>${arr[i].nama_jalan_s}</td>
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
			<td><b>Status</b></td>
			<td>:</td>
			<td>${arr[i].status}</td>
			</tr>
			</table>
			</div>
			`);
			infowindow.open(map, titik[i]);
		});
	  }
	}

	if (n_titik=="Rawan") {
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
			  <td>${arr[i].nama_jalan_s}</td>
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
			  <td><b>Rawan</b></td>
			  <td>:</td>
			  <td>${arr[i].kerawanan}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
	  }

	if (n_titik=="Cctv") {
		for (let i = 0; i < arr.length; i++) {
		  google.maps.event.addListener(titik[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
			  infowindow.setContent(`
			  <div><p><b>${n_titik}</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Nama CCTV</b></td>
			  <td>:</td>
			  <td>${arr[i].nama_cctv}</td>
			  </tr>
			  <tr>
			  <td><b>Kordinat</b></td>
			  <td>:</td>
			  <td>${arr[i].lat + "," + arr[i].lng}</td>
			  </tr>
			  <tr>
			  <td><b>Status</b></td>
			  <td>:</td>
			  <td>${arr[i].status}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
	  }
	
	if (n_titik=="Fasum") {
		for (let i = 0; i < arr.length; i++) {
		  google.maps.event.addListener(titik[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
			  infowindow.setContent(`
			  <div><p><b>${n_titik}</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Fasilitas Umum</b></td>
			  <td>:</td>
			  <td>${arr[i].fasilitas}</td>
			  </tr>
			  <tr>
			  <td><b>Nama Jalan</b></td>
			  <td>:</td>
			  <td>${arr[i].nama_jalan_s}</td>
			  </tr>
			  <tr>
			  <td><b>Kordinat</b></td>
			  <td>:</td>
			  <td>${arr[i].lat + "," + arr[i].lng}</td>
			  </tr>
			  <tr>
			  <td><b>Kapasitas Orang</b></td>
			  <td>:</td>
			  <td>${arr[i].kapasitas_orang}</td>
			  </tr>
			  <tr>
			  <td><b>Kapasitas Parkir</b></td>
			  <td>:</td>
			  <td>${arr[i].kapasitas_parkir}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
	  }

	if (n_titik=="Objek Vital") {
		for (let i = 0; i < arr.length; i++) {
		  google.maps.event.addListener(titik[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
			  infowindow.setContent(`
			  <div><p><b>${n_titik}</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Fasilitas Umum</b></td>
			  <td>:</td>
			  <td>${arr[i].fasilitas}</td>
			  </tr>
			  <tr>
			  <td><b>Nama Jalan</b></td>
			  <td>:</td>
			  <td>${arr[i].nama_jalan_s}</td>
			  </tr>
			  <tr>
			  <td><b>Kordinat</b></td>
			  <td>:</td>
			  <td>${arr[i].lat + "," + arr[i].lng}</td>
			  </tr>
			  <td>
			  <td><b>Kapasitas Orang</b></td>
			  <td>:</td>
			  <td>${arr[i].kapasitas_orang}</td>
			  </tr>
			  <tr>
			  <td><b>Kapasitas Parkir</b></td>
			  <td>:</td>
			  <td>${arr[i].kapasitas_parkir}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
	  }
	
	if (n_titik=="Ren Giat") {
		for (let i = 0; i < arr.length; i++) {
		  google.maps.event.addListener(titik[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
			  infowindow.setContent(`
			  <div><p><b>${n_titik}</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Jenis Kegiatan</b></td>
			  <td>:</td>
			  <td>${arr[i].jenis_kegiatan}</td>
			  </tr>
			  <tr>
			  <td><b>Kategori Kegiatan</b></td>
			  <td>:</td>
			  <td>${arr[i].kategori_kegiatan}</td>
			  </tr>
			  <tr>
			  <td><b>Nama Jalan</b></td>
			  <td>:</td>
			  <td>${arr[i].nama_jalan_s}</td>
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
			  <td><b>Sasaran Giat</b></td>
			  <td>:</td>
			  <td>${arr[i].sasaran_giat}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
	  }
	
	if (n_titik=="Ren Ops Pol") {
		for (let i = 0; i < arr.length; i++) {
		  google.maps.event.addListener(titik[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
			  infowindow.setContent(`
			  <div><p><b>${n_titik}</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Jenis Kegiatan</b></td>
			  <td>:</td>
			  <td>${arr[i].jenis_kegiatan}</td>
			  </tr>
			  <tr>
			  <td><b>Kategori Kegiatan</b></td>
			  <td>:</td>
			  <td>${arr[i].kategori_kegiatan}</td>
			  </tr>
			  <tr>
			  <td><b>Nama Jalan</b></td>
			  <td>:</td>
			  <td>${arr[i].nama_jalan_s}</td>
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
			  <td><b>Sasaran Giat</b></td>
			  <td>:</td>
			  <td>${arr[i].sasaran_giat}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
	  }
  }

function show_titik_new(items="") {
	var filter = ''
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

    var item = ''
    // let url = 'http://localhost:8082/api/V.0.1/' // server local
    // let url = 'https://dev.estrada.co.id:8082/api/V.0.1/' // server prod
    let url = 'https://backend.elingbali.com:8082/api/V.0.1/' // server prod
	// let url = 'http://smbali.estrada.co.id:8082/' // server dev
    
    switch (items) {
        case 'black_spot':
            url += `data_collecting/black_trouble_spot?status=black spot${filter}`
            break;
        case 'trouble_spot':
            url += `data_collecting/black_trouble_spot?status=trouble spot${filter}`
            break;
        case 'rawan_laka':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=2${filter}`
            break;
        case 'rawan_macet':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=3${filter}`
            break;
        case 'rawan_tindak_pidana':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=5${filter}`
            break;
        case 'rawan_banjir':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=6${filter}`
            break;
        case 'rawan_longsor':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=7${filter}`
            break;
        case 'rawan_pohon_tumbang':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=9${filter}`
            break;
        case 'cctv':
            url += `patroli_cctv/cctv?${filter}`
            break;
		case 'fasum':
			url += `data_collecting/fasilitas_umum?${filter}`
			break;
		case 'objek_vital':
			url += `data_collecting/objek_vital?${filter}`
			break;
		case 'rengiat':
			url += `data_collecting/rengiat?${filter}`
			break;
		case 'renops_pol':
			url += `data_collecting/renops_pol?${filter}`
			break;
        default:
            break;
    }

    setTimeout(() => {

      if (items == "black_spot") {
        varr = bs;
          icon = "./my/satupeta/blackspot2.png";
          getDataSmNew(url).then(r => {
            titik_link = r
            create_titik_map_new(items, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${items}">
                  <span id="jml_data">Jumlah Black Spot <img src="./my/satupeta/blackspot2.png" class="jml_data_img"> (<span><b id="counter_${items}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${items}`);
              animateValue(obj, 0, r.data.length, 200)
            });
          });
      }
      if (items == "trouble_spot") {
        varr = ts;
          icon = "./my/satupeta/troublespot2.png";
          getDataSmNew(url).then(r => {
            titik_link = r
            create_titik_map_new(items, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${items}">
                  <span id="jml_data">Jumlah Trouble Spot <img src="./my/satupeta/troublespot2.png" class="jml_data_img"> (<span><b id="counter_${items}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${items}`);
              animateValue(obj, 0, r.data.length, 200)
            });
          });
      }

      if (items == 'rawan_laka') {
		item = 'rawan';
			getDataSmNew(url).then(function (r) {
				titik_link = r
				create_titik_map_new(item,'./my/satupeta/rawan-laka.png',r.data).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Laka <img src="./my/satupeta/rawan-laka.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, r.data.length, 200)
				})
			});
	}
	if (items == 'rawan_macet'){
		item = 'rawan';
			getDataSmNew(url).then(function (r) {
				titik_link = r
				create_titik_map_new(item,'./my/satupeta/rawan-macet.png',r.data).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Macet <img src="./my/satupeta/rawan-macet.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, r.data.length, 200)
				})
			});
	}
	if (items == 'rawan_longsor'){
		item = 'rawan';
			getDataSmNew(url).then(function (r) {
				titik_link = r;
				create_titik_map_new(item,'./my/satupeta/rawan-longsor.png',r.data).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Longsor <img src="./my/satupeta/rawan-longsor.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, r.data.length, 200)
				})
			});
	}
	if (items == 'rawan_banjir'){
		item = 'rawan';
			getDataSmNew(url).then(function (r) {
				titik_link = r;
				create_titik_map_new(item,'./my/satupeta/rawan-banjir.png',r.data).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Banjir <img src="./my/satupeta/rawan-banjir.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, r.data.length, 200)
				})
			});
	}
	if (items == 'rawan_pohon_tumbang'){
		item = 'rawan';
			getDataSmNew(url).then(function (r) {
				titik_link = r;
				create_titik_map_new(item,'./my/satupeta/rawan-pohontumbang.png',r.data).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Pohon Tumbang <img src="./my/satupeta/rawan-pohontumbang.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, r.data.length, 200)
				})
			});
	}
	if (items == 'rawan_tindak_pidana'){
		item = 'rawan';
			getDataSmNew(url).then(function (r) {
				titik_link = r;
				create_titik_map_new(item,'./my/satupeta/rawan-tindakpidana.png',r.data).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Tindak Pidana <img src="./my/satupeta/rawan-tindakpidana.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, r.data.length, 200)
				})
			});
	}

    if (items == 'cctv') {
        icon = "./my/satupeta/cctv_icon_korlantas.png";
          getDataSmNew(url).then(r => {
            titik_link = r
            create_titik_map_new(items, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_s_${items}">
                  <span id="jml_data">Jumlah CCTV <img src="./my/satupeta/cctv_icon_korlantas.png" class="jml_data_img"> (<span><b id="counter_${items}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${items}`);
              animateValue(obj, 0, r.data.length, 200)
            });
          });
    }

	if (items == "fasum") {
          icon = "./my/satupeta/fasum.png";
          getDataSmNew(url).then(r => {
            titik_link = r
            create_titik_map_new(items, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${items}">
                  <span id="jml_data">Jumlah Fasilitas Umum <img src="./my/satupeta/fasum.png" class="jml_data_img"> (<span><b id="counter_${items}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${items}`);
              animateValue(obj, 0, r.data.length, 200)
            });
          });
      }

	  if (items == "objek_vital") {
          icon = "./my/satupeta/objek-vital2.png";
          getDataSmNew(url).then(r => {
            titik_link = r
            create_titik_map_new(items, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${items}">
                  <span id="jml_data">Jumlah Objek Vital <img src="./my/satupeta/objek-vital2.png" class="jml_data_img"> (<span><b id="counter_${items}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${items}`);
              animateValue(obj, 0, r.data.length, 200)
            });
          });
      }

	  if (items == "rengiat") {
		icon = "./my/satupeta/giatmasyarakat1.png";
		getDataSmNew(url).then(r => {
		  titik_link = r
		  create_titik_map_new(items, icon, r.data).then(e => {
			$('#jml_data_group').append(`
			  <div class="jml_data mt-2" id="jml_data_item_${items}">
				<span id="jml_data">Jumlah Rengiat <img src="./my/satupeta/giatmasyarakat1.png" class="jml_data_img"> (<span><b id="counter_${items}"></b></span>)</span>
			  </div>
			`)
			const obj = document.getElementById(`counter_${items}`);
			animateValue(obj, 0, r.data.length, 200)
		  });
		});
	}

	if (items == "renops_pol") {
		icon = "./my/satupeta/renopspol.png";
		getDataSmNew(url).then(r => {
		  titik_link = r
		  create_titik_map_new(items, icon, r.data).then(e => {
			$('#jml_data_group').append(`
			  <div class="jml_data mt-2" id="jml_data_item_${items}">
				<span id="jml_data">Jumlah Rensops Opl <img src="./my/satupeta/renopspol.png" class="jml_data_img"> (<span><b id="counter_${items}"></b></span>)</span>
			  </div>
			`)
			const obj = document.getElementById(`counter_${items}`);
			animateValue(obj, 0, r.data.length, 200)
		  });
		});
	}
      
    }, 150);
  
  }