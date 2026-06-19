var nik = "";
var marker_petugas = [];
var pet_arr = [];
var pet_detail = [];
var pet_api = {
	petugas: [],
	route: [],
    nama: [],
};

$(document).ready(function () {

	// getDataApi("./Api_indicar/get_token").then((data) => {
	// 	indicarKey = data;
	// });
});

function get_list_petugas(){
		var filter = ''
		let da = $('#polda_id').val()
		let res = $('#polres_id').val()
		if (da != '' && da != undefined) {
			filter += `&polda_id=${da}`
		}
		if (res != '' && res != undefined) {
			filter += `&polres_id=${res}`
		}
		getDataApi(
			`${global_url_api}sm_master_data/petugas?activity=1${filter}`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			$('#list_petugas').html('');
			$('#list_petugas').append(`<option value="">-- Pilih --</option>`);
            $('#list_petugas').append(`<option value="">Tampilkan Semua Petugas</option>`);
			data.data.forEach((m) => {
				$('#list_petugas').append(`
					<option value="${m.nik}">${m.nama_petugas}</option>
				`)
			});
			
			if ($('#jml_data_item_petugas').length == 0) {
				$('#jml_data_group').append(`
				<div class="jml_data mt-2" id="jml_data_item_petugas">
					<span id="jml_data">Jumlah Petugas Aktif <img src="./my/satupeta/petugas.png" class="jml_data_img"> (<span><b id="counter_petugas"></b></span>)</span>
				</div>
				`)
				const obj = document.getElementById(`counter_petugas`);
				animateValue(obj, 0, data.data.length, 200)
			}else {
				$('#jml_data_item_petugas').remove()
			}
			setTimeout(() => {
				$('#list_petugas').select2()
			}, 1000);
		});
        // nopol = $('#list_kendaraan').val()
        // get_petugas(nopol);
}

var t_out = null;
var t_int = null;
function clear_petugas(){
    for (var i = 0; i < marker_petugas.length; i++) {
        marker_petugas[i].setMap(null);
    }
    marker_petugas = [];
    pet_arr = [];
    pet_detail = [];
    pet_api = {
        petugas: [],
        route: [],
        nama: [],
    };
    clearInterval(t_int);
    clearTimeout(t_out)
}
function get_petugas(nik) {
    clear_petugas();
    t_out = setTimeout(() => {
       t_int = setInterval(() => {
                if (nik != "") {
                    realtime_petugasNik(nik);
                }else{
                    realtime_petugas()
                }       
        }, 1500);
	}, 1000);
}
function realtime_petugasNik(nik) {
    if (pet_api.petugas.length < 1) {
		getMakeMarkerPetugasNik(nik)
			.then((x) => {
				x.petugas.forEach((m) => {
					updateMarker(
						m,
						m.position.lat(),
						m.position.lng(),
						"#795548",
						// m.rotation,
						"",
						m.nama_petugas
					);
				});
			})
			.catch((err) => {});
	} else {
		var i = 0;
		getDataApi(
			`${global_url_api}sm_master_data/petugas?nik=${nik}`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			data.data.forEach((m) => {
                // console.log(m.latitude)
				updateMarker(
					pet_api.petugas[m.rowid],
					m.lat,
					m.lng,
					"#795548",
					// m.angle,
					"",
					m.nama_petugas
				);
                map.setZoom(15);
				map.setCenter({ lat : parseFloat(m.lat), lng : parseFloat(m.lng) });
			});
		});
	}
}
function getMakeMarkerPetugasNik(nik) {
	return new Promise(function (resolve, reject) {
		var marker = {};
		getDataApi(
			`${global_url_api}sm_master_data/petugas?nik=${nik}`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			data.data.forEach((e) => {

				symbol = {
					url : "./my/satupeta/petugas.png",
					scaledSize: new google.maps.Size(30, 30), // scaled size
				}

				marker = new google.maps.Marker({
					position: { lat : parseFloat(e.lat), lng : parseFloat(e.lng)},
					map,
					icon : symbol
				  })

				// createLabel(marker, e.vehiclename);
                marker_petugas.push(marker)
                pet_arr.push(marker)
				pet_detail.push(e)
				pet_api.petugas[e.rowid] = marker;
				// pet_api.route[e.rowid] = e.angle;
                pet_api.nama[e.rowid] = e.nama_petugas;
			});

			setInfoWindowPetugas(pet_arr, pet_detail)
			resolve(pet_api);
            // console.log(marker_petugas)
		});
	});
}

function realtime_petugas() {
	if (pet_api.petugas.length < 1) {
		getMakeMarkerPetugas()
			.then((x) => {
				x.petugas.forEach((m) => {
					updateMarker(
						pet_api.petugas[m.rowid],
						parseFloat(m.lat),
						parseFloat(m.lng),
						"#795548",
						// m.angel,
						"",
						m.nama_petugas,
						m.rowid
					);
				});
			})
			.catch((err) => {});
	} else {
		var i = 0;
		getDataApi(
			`${global_url_api}sm_master_data/petugas?activity=1`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			data.data.forEach((m,i) => {
				updateMarker(
					pet_api.petugas[m.rowid],
					parseFloat(m.lat),
					parseFloat(m.lng),
					"#795548",
					// m.angel,
					"",
					m.nama_petugas,
					m.rowid
				);
			});
		});
	}
}
function getMakeMarkerPetugas() {
	return new Promise(function (resolve, reject) {
		var marker = {};
		getDataApi(
			`${global_url_api}sm_master_data/petugas?activity=1`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			data.data.forEach((e) => {
					symbol = {
						url : "./my/satupeta/petugas.png",
						scaledSize: new google.maps.Size(30, 30), // scaled size
					}
					var icn = "./my/satupeta/petugas.png";

                marker = new google.maps.Marker({
                    position: { lat : parseFloat(e.lat), lng : parseFloat(e.lng)},
                    map,
                    icon : symbol
                })
                marker_petugas.push(marker)
                pet_arr.push(marker)
				pet_detail.push(e)
				pet_api.petugas[e.rowid] = marker;
				// pet_api.route[e.rowid] = e.angle;
                pet_api.nama[e.rowid] = e.nama_petugas;
			});
			
			setInfoWindowPetugas(pet_arr, pet_detail)
		});
		resolve(pet_api);
	});
}


function updateMarker(marker, latitude, longitude, color, angel, vehiclename,vehicleid) {
    // console.log(marker);
	var newLatLng = new google.maps.LatLng(latitude, longitude);
	if (typeof newLatLng !== 'undefined') {
		marker.setPosition(newLatLng);
	}

    var icon = marker.getIcon();
    // icon.rotation = parseInt(angel);
    marker.setIcon(icon);
}
function setInfoWindowPetugas(marker = "", pet_detail = '') {
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
	infowindow.close(); // Close previously opened infowindow
	return new Promise(function (resolve, reject) {
		for (let i = 0; i < pet_detail.length; i++) {
			google.maps.event.addListener(marker[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(pet_detail[i].lat), lng : parseFloat(pet_detail[i].lng) });
				infowindow.setContent(`
				<div class="mt-3">
				<table class="w-100">
				<span id="live_pet"></span>
				<tr>
				</tr>
				<tr>
				<td><b>Nama Petugas</b></td>
				<td>:</td>
				<td>${pet_detail[i].nama_petugas}</td>
				</tr>
				<tr>
				<td><b>Kedinasan</b></td>
				<td>:</td>
				<td>${pet_detail[i].sotk}</td>
				</tr>
				</table>
				</div>
				<div class="mt-2">
					<button class="btn btn-primary btn-sm" onclick="history_petugas(${pet_detail[i].rowid})">history</button>
					<button onclick="myLiveStream('${pet_detail[i].nik}')"><img class="ml-2" src="./my/satupeta/live.png"></img></button>
					<a href="tel:${pet_detail[i].hp}"><img class="ml-2" src="./my/satupeta/tlp.png"></img></a>
					<a href="https://api.whatsapp.com/send?phone=${pet_detail[i].hp}"><img class="ml-2" src="./my/satupeta/wa.png"></img></a>
				</div>
				<div class="row" style="margin-right:0 !important;">
				</div>
				</div>
				`);
				infowindow.open(map, marker[i]);
			});
		}

	});
	
}
$('#filter_data_petugas').submit(function (x) { 
    x.preventDefault();
    // deleteMarkers();
    nik = $('#list_petugas').val()
    // removeMarkers(marker_petugas);
    get_petugas(nik);
}); 

// var locations = [
// 	['-8.434504751551263', '115.24505352810313'],
// 	['-8.434771885028812', '115.24665896976484'],
// 	['-8.432899744823006', '115.24683703052872'],
// 	['-8.43273200203563', '115.24530562739227']
// ]
// var locations = [
// 	{ lat : -8.434504751551263, lng: 115.24505352810313},
// 	{ lat : -8.434771885028812, lng: 115.24665896976484},
// 	{ lat : -8.432899744823006, lng: 115.24683703052872},
// 	{ lat : -8.43273200203563, lng: 115.24530562739227}
// ]
// var markerss
var directionsDisplayL, directionsServiceL, requestL;
var lineHistoryPetugas = []
function history_petugas(rowid) {
	let filter = ''
	let t_s = $('#f_date_from').val()
	let t_e = $('#f_date_to').val()
	if (t_s != '' && t_s != undefined) {
		filter += `&tgl_start=${t_s}`
	}
	if (t_e != '' && t_e != undefined) {
		filter += `&tgl_end=${t_e}`
	}
	clear_route_history_petugas()
	getDataSmNew(global_url_api+`sm_master_data/petugas/log_petugas/${rowid}?${filter}`).then(r => {
		// getDataSmNew('https://backoffice.elingsolo.com:8082/api/V.0.1/sm_master_data/petugas/log_petugas/60').then(r => {
		// directionsDisplayL = new google.maps.DirectionsRenderer({
		// 	suppressMarkers: true,
		// 	polylineOptions: {
		// 		strokeColor: "blue"
		// 	}
		// });
		// directionsServiceL = new google.maps.DirectionsService();
	  
		// directionsDisplayL.setMap(map);
		// // $('#panel-direction-vip').html('')
		// // $('#panel-direction-vip').removeClass('d-none')
		// // directionsDisplayL.setPanel(document.getElementById("panel-direction-vip"));
	  
		// var marker, i;
		// var requestL = {
		//   travelMode: google.maps.TravelMode.WALKING
		// };
	  
		// for (i = 0; i < r.data.length; i++) {
		//   marker = new google.maps.Marker({
		// 	position: new google.maps.LatLng(r.data[i].lat, r.data[i].lng),
		//   });
	  
		//   google.maps.event.addListener(marker, 'click', (function(marker, i) {
		// 	return function() {
		// 	  infowindow.setContent(r.data[i]);
		// 	  infowindow.open(map, marker);
		// 	}
		//   })(marker, i));
	  
		//   if (i == 0) requestL.origin = marker.getPosition();
		//   else if (i == r.data.length - 1) requestL.destination = marker.getPosition();
		//   else {
		// 	if (!requestL.waypoints) requestL.waypoints = [];
		// 	requestL.waypoints.push({
		// 	  location: marker.getPosition(),
		// 	  stopover: true
		// 	});
		//   }
	  
		// }
		// directionsServiceL.route(requestL, function(result, status) {
		//   if (status == google.maps.DirectionsStatus.OK) {
		// 	directionsDisplayL.setDirections(result);
		//   }
		// });

		const flightPlanCoordinates = [];

		for (let i = 0; i < r.data.length; i++) {
			flightPlanCoordinates.push({lat : parseFloat(r.data[i].lat), lng : parseFloat(r.data[i].lng)})
		}

		  const flightPath = new google.maps.Polyline({
			path: flightPlanCoordinates,
			geodesic: true,
			strokeColor: "#FF0000",
			strokeOpacity: 1.0,
			strokeWeight: 2,
		  });
		
		  lineHistoryPetugas.push(flightPath)
		  flightPath.setMap(map);
	})
	// symbol = {
	// 	url : "./my/satupeta/petugas.png",
	// 	scaledSize: new google.maps.Size(30, 30), // scaled size
	// }

	// markerss = new google.maps.Marker({
	// 	position: { lat : parseFloat(locations[0][0]), lng : parseFloat(locations[0][1])},
	// 	map,
	// 	icon : symbol
	// })
}

function clear_route_history_petugas() {
	// if (directionsDisplayL != undefined) {
	// 	// $('#panel-direction-vip').addClass('d-none')
	// 	directionsDisplayL.setMap(null)
	// }
	if (lineHistoryPetugas.length > 0) {
		for (let i = 0; i < lineHistoryPetugas.length; i++) {
			lineHistoryPetugas[i].setMap(null)
		}
	}
}

function myLiveStream(nik = "") {
	// var x = document.getElementById("live_pet");
	let x = $('#live_pet')
	if ($("#live_pet").hasClass("checks")) {
        $('#live_pet').addClass('d-none');
        $("#live_pet").removeClass("checks");
		x.html('')
      } else {
		x.append(`<iframe class="w-100" style="width: 500px!important;height: 300px!important;" src="${"https://avi.elingbali.com:8000/callpetugas?id=opsdal&nik=" + nik}" allow="camera;microphone" allowfullscreen></iframe>`);

		// x.append(`
		// <iframe width="560" height="315" src="https://www.youtube.com/embed/hpmn_v-zi7s" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
		// `);
        $('#live_pet').removeClass('d-none');
        $("#live_pet").addClass("checks");
      }
  }