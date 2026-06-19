var jalur_mgmt_route = {};
// var routingControl = [];
var mgmt_route_link

var directionsDisplayMgmtRoute, directionsDisplayMgmtRoutePengalihan, directionsDisplayMgmtRouteTitikPenutupan, directionsServiceMgmtRoute, directionsServiceMgmtRoutePengalihan, directionsServiceMgmtRouteTitikPenutupan, requestMgmtRoute, requestMgmtRoutePengalihan, requestMgmtRouteTitikPenutupan;

function mgmt_route(param = "") {
	prom_mgmt_route().then(function (data) {
		list_mgmt_route();
	});
}

function prom_mgmt_route() {
	let f_date_from = $('#f_date_from').val()
	let f_date_to = $('#f_date_to').val()
	let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()
	
	let filter = ''

	if (polda_id != '') {
		filter += `&polda_id=${polda_id}`
	}

	if (polres_id != '') {
		filter += `&polres_id=${polres_id}`
	}

	if (f_date_from != '') {
		filter += `&tgl_start=${f_date_from}`
	}

	if (f_date_to != '') {
		filter += `&tgl_end=${f_date_to}`
	}

	// return new Promise(function (resolve, reject) {
	// 	showToast()
	// 	$.ajax({
	// 		type: "GET",
	// 		url: `${global_url_api}data_collecting/management_route?${filter}`,
	// 		dataType: "json",
	// 		headers: {
	// 			"token": "5b3dac76aaee24d14185cbc3d010fd20",
	// 		  },
	// 		success: function (r) {
	// 			mgmt_route_link = r;
	// 			closeToast()
	// 			resolve(mgmt_route_link);
	// 		},
	// 	});
	// });
	return new Promise(function (resolve, reject) {
		showToast()
		getDataSmNew(`${global_url_api}data_collecting/management_route?${filter}`).then(r => {
			if (r.status == true) {
				mgmt_route_link = r
			}else {
				mgmt_route_link = { data : [], status : false, msg: r.msg}
			}
			closeToast()
			resolve(mgmt_route_link)
		});
	})
}

function list_mgmt_route() {
	$("#list_mgmt_route").html("");
	if ($('#jml_data_item_mgmt_route').length == 0) {
		$('#jml_data_group').append(`
		<div class="jml_data mt-2" id="jml_data_item_mgmt_route">
			<span id="jml_data">Jumlah Management Route <img src="./my/satupeta/location2.png" class="jml_data_img"> (<span><b id="counter_mgmt_route"></b></span>)</span>
		</div>
		`)
		const obj = document.getElementById(`counter_mgmt_route`);
		animateValue(obj, 0, mgmt_route_link.data.length, 200)
	}else {
		$('#jml_data_item_mgmt_route').remove()
	}
	setTimeout(() => {
		if (mgmt_route_link.data.length > 0) {
			var no = 0;
			mgmt_route_link.data.forEach((e) => {
				$("#list_mgmt_route")
					.append(`
					<li class="list-group-item list-group-item-action">
						<div class="form-check">
							<input class="form-check-input check-jalan-mgmt-route" type="radio" onchange="check_mgmt_route('${e.kode_data}')" name="mgmt-route[]" value="${no++}">
							<label class="form-check-label" for="flexRadioDefault1">
								${e.manage_route} (${e.lokasi_berangkat} - ${e.lokasi_tujuan})
							</label>
						</div>
					</li>
					`);
			});
		}else {
			$("#list_mgmt_route")
				.append(`
				<li class="list-group-item list-group-item-action">
					<div class="form-check">
						<label class="form-check-label" for="flexRadioDefault1">
							Tidak ada data
						</label>
					</div>
				</li>
			`);
		}
	}, 300);
}

function check_mgmt_route(kode='') {
    prom_check_mgmt_route().then(function (e) {
			clear_mgmt_route()
            get_mgmt_route(kode).then(function (data) {
				// console.log(data);
				// debugger
                mgmt_route_route(data)
            })
    })
}

function prom_check_mgmt_route() {
	return new Promise(function (resolve, reject) {
		var check_mgmt_route = [];
		setTimeout(() => {
			$('input[name="mgmt-route[]"]:checked').each(function () {
				check_mgmt_route.push(mgmt_route_link[this.value]);
			});
			resolve(check_mgmt_route);
		}, 300);
	});
}

function mgmt_route_route(locations) {
	if (jalur_mgmt_route.main.length > 0) {
		directionsDisplayMgmtRoute = new google.maps.DirectionsRenderer({
			draggable:true,
			polylineOptions: {
				strokeColor: "blue",
				strokeWeight: 10
			}
		});
		directionsServiceMgmtRoute = new google.maps.DirectionsService();
	  
		directionsDisplayMgmtRoute.setMap(map);
		// $('#panel-direction-vip').html('')
		// $('#panel-direction-vip').removeClass('d-none')
		// directionsDisplayMgmtRoute.setPanel(document.getElementById("panel-direction-vip"));
	  
		var marker, i;
		var requestMgmtRoute = {
		  travelMode: google.maps.TravelMode.DRIVING
		};
	  
		for (i = 0; i < locations.main.length; i++) {
		  marker = new google.maps.Marker({
			position: new google.maps.LatLng(locations.main[i][0], locations.main[i][1]),
		  });
	  
		  google.maps.event.addListener(marker, 'click', (function(marker, i) {
			return function() {
			  infowindow.setContent(locations.main[i][0]);
			  infowindow.open(map, marker);
			}
		  })(marker, i));
	  
		  if (i == 0) requestMgmtRoute.origin = marker.getPosition();
		  else if (i == locations.main.length - 1) requestMgmtRoute.destination = marker.getPosition();
		  else {
			if (!requestMgmtRoute.waypoints) requestMgmtRoute.waypoints = [];
			requestMgmtRoute.waypoints.push({
			  location: marker.getPosition(),
			  stopover: true
			});
		  }
	  
		}
		directionsServiceMgmtRoute.route(requestMgmtRoute, function(result, status) {
		  if (status == google.maps.DirectionsStatus.OK) {
			directionsDisplayMgmtRoute.setDirections(result);
		  }
		});
	}

	if (locations.titik_penutupan.length > 0) {
		setTimeout(() => {
			directionsDisplayMgmtRouteTitikPenutupan = new google.maps.DirectionsRenderer({
				suppressMarkers: true,
				polylineOptions: {
				strokeColor: "red",
				strokeWeight: 10
			  }});
			directionsServiceMgmtRouteTitikPenutupan = new google.maps.DirectionsService();
		  
			directionsDisplayMgmtRouteTitikPenutupan.setMap(map);
		  
			var markerTitikPenutupan, i_titik_penutupan;
			var requestMgmtRouteTitikPenutupan = {
			  travelMode: google.maps.TravelMode.DRIVING
			};
		  
			for (i_titik_penutupan = 0; i_titik_penutupan < locations.titik_penutupan.length; i_titik_penutupan++) {
			  markerTitikPenutupan = new google.maps.Marker({
				position: new google.maps.LatLng(locations.titik_penutupan[i_titik_penutupan][0], locations.titik_penutupan[i_titik_penutupan][1]),
			  });
		  
			  google.maps.event.addListener(markerTitikPenutupan, 'click', (function(markerTitikPenutupan, i_titik_penutupan) {
				return function() {
				  infowindow.setContent(locations.titik_penutupan[i_titik_penutupan][0]);
				  infowindow.open(map, markerTitikPenutupan);
				}
			  })(markerTitikPenutupan, i_titik_penutupan));
		  
			  if (i_titik_penutupan == 0) requestMgmtRouteTitikPenutupan.origin = markerTitikPenutupan.getPosition();
			  else if (i_titik_penutupan == locations.titik_penutupan.length - 1) requestMgmtRouteTitikPenutupan.destination = markerTitikPenutupan.getPosition();
			  else {
				if (!requestMgmtRouteTitikPenutupan.waypoints) requestMgmtRouteTitikPenutupan.waypoints = [];
				requestMgmtRouteTitikPenutupan.waypoints.push({
				  location: markerTitikPenutupan.getPosition(),
				  stopover: true
				});
			  }
		  
			}
			directionsServiceMgmtRouteTitikPenutupan.route(requestMgmtRouteTitikPenutupan, function(result, status) {
			  if (status == google.maps.DirectionsStatus.OK) {
				directionsDisplayMgmtRouteTitikPenutupan.setDirections(result);
			  }
			});
		}, 300);
	}

	if (locations.pengalihan.length > 0) {
		directionsDisplayMgmtRoutePengalihan = new google.maps.DirectionsRenderer({
			// draggable:true,
			suppressMarkers: true,
			polylineOptions: {
				strokeColor: "green",
				strokeWeight: 10
			}
		});
		directionsServiceMgmtRoutePengalihan = new google.maps.DirectionsService();
	  
		directionsDisplayMgmtRoutePengalihan.setMap(map);
	  
		var markerPengalihan, i_pengalihan;
		var requestMgmtRoutePengalihan = {
		  travelMode: google.maps.TravelMode.DRIVING
		};
	  
		for (i_pengalihan = 0; i_pengalihan < locations.pengalihan.length; i_pengalihan++) {
		  markerPengalihan = new google.maps.Marker({
			position: new google.maps.LatLng(locations.pengalihan[i_pengalihan][0], locations.pengalihan[i_pengalihan][1]),
		  });
	  
		  google.maps.event.addListener(markerPengalihan, 'click', (function(markerPengalihan, i_pengalihan) {
			return function() {
			  infowindow.setContent(locations.pengalihan[i_pengalihan][0]);
			  infowindow.open(map, markerPengalihan);
			}
		  })(markerPengalihan, i_pengalihan));
	  
		  if (i_pengalihan == 0) requestMgmtRoutePengalihan.origin = markerPengalihan.getPosition();
		  else if (i_pengalihan == locations.pengalihan.length - 1) requestMgmtRoutePengalihan.destination = markerPengalihan.getPosition();
		  else {
			if (!requestMgmtRoutePengalihan.waypoints) requestMgmtRoutePengalihan.waypoints = [];
			requestMgmtRoutePengalihan.waypoints.push({
			  location: markerPengalihan.getPosition(),
			  stopover: true
			});
		  }
	  
		}
		directionsServiceMgmtRoutePengalihan.route(requestMgmtRoutePengalihan, function(result, status) {
		  if (status == google.maps.DirectionsStatus.OK) {
			directionsDisplayMgmtRoutePengalihan.setDirections(result);
		  }
		});
	}
  
  }


function get_mgmt_route(kode = '') {
	let a = getRToken().then(async r=>{
		if (r.status != true) {
            window.location.href = `${global_url_ci}Auth/logout`
        }else {
			return new Promise (function(resolve, reject){
				jalur_mgmt_route = {
					'main' : [],
					'pengalihan' : [],
					'titik_penutupan' : []
				};
				$.ajax({
					type: "GET",
					url: `${global_url_api}data_collecting/route_management_route/${kode}`,
					dataType: "json",
					headers: {
						"token": "5b3dac76aaee24d14185cbc3d010fd20",
						"secondbearer": r.data.second_bearer,
					  },
					success: function (r) {
						r.data.forEach(v => {
							if (v.lat_tujuan !== '' && v.lng_tujuan !='') {
								jalur_mgmt_route.main.push([v.lat_tujuan,v.lng_tujuan])
							}
							if (v.lat_pengalihan !== '' && v.lng_pengalihan !='') {
								jalur_mgmt_route.pengalihan.push([v.lat_pengalihan,v.lng_pengalihan])
							}
						});
						if (jalur_mgmt_route.pengalihan.length >0) {
							jalur_mgmt_route.titik_penutupan.push([jalur_mgmt_route.pengalihan[0][0],jalur_mgmt_route.pengalihan[0][1]],[jalur_mgmt_route.pengalihan[jalur_mgmt_route.pengalihan.length - 1][0],jalur_mgmt_route.pengalihan[jalur_mgmt_route.pengalihan.length - 1][1]])
						}
						resolve(jalur_mgmt_route);
		
					},
				})
			});
		}
	})
	return a
}

function clear_mgmt_route() {
	if (directionsDisplayMgmtRoute != undefined) {
		// $('#panel-direction-vip').addClass('d-none')
		directionsDisplayMgmtRoute.setMap(null)
	}
	if (directionsDisplayMgmtRoutePengalihan != undefined) {
		// $('#panel-direction-vip').addClass('d-none')
		directionsDisplayMgmtRoutePengalihan.setMap(null)
	}
	if (directionsDisplayMgmtRouteTitikPenutupan != undefined) {
		// $('#panel-direction-vip').addClass('d-none')
		directionsDisplayMgmtRouteTitikPenutupan.setMap(null)
	}
}


$('#hapusMgmtRoute').on('click', function() {
    // mymap.removeControl(routingControl.pop());
	clear_mgmt_route()
    $('input[name="mgmt-route[]"]').prop('checked',false);
    // mymap.setZoom(18);
});

function hapusMgmtRouteE() {
	clear_mgmt_route()
    $('input[name="mgmt-route[]"]').prop('checked',false);
}

function search_mgmt_route() {
	let f_mgmt_route = $('#search_mgmt_route').val().toLowerCase()
	let i_mgmt_route = $('.list_mgmt_route>.list-group-item')
	for(var i = 0; i<i_mgmt_route.length; i++){
		let span = $('.list-group-item>div>label')[i];
		if(span.innerHTML.toLowerCase().indexOf(f_mgmt_route) > -1){
			i_mgmt_route[i].style.display = "";
		}else{
			i_mgmt_route[i].style.display = "none";
		}
	}
}
