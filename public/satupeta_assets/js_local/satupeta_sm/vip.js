var jalur = [];
var routingControl = [];

var directionsDisplay, directionsService, request, checked_vip;

function get_list_vip(){
	if ($('#cstm-content').hasClass('d-none')) {
		$('#cstm-content').removeClass('d-none')
		$('#cstm-content').append(`
		<div class="accordion">
			<div class="accordion-item">
				<h2 class="accordion-header" id="panelsStayOpen-headingOne">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
					VIP
				</button>
				</h2>
				<div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
				<div class="accordion-body">
					<div class="ml-2 row mx-0" style="text-align:left!important;">
						<div class="col-3">
						<button id="hapusVip" class="btn btn-primary">Clear</button>
						</div>
						<div class="col">
						<input type="text" class="form-control" placeholder="Cari..." id="search-vip" onkeyup="search_vip()">
						</div>
					</div>
                  	<ul class="list-group mt-2 pt-2 border-top text-start list_vip" style="height:200px!important" id="list_vip"></ul>
				</div>
				</div>
			</div>
		</div>
		`)
		vip();
	}else{
		$('#jml_data_item_vip').remove()
		$('#cstm-content').html('')
		$('#cstm-content').addClass('d-none')
	}

	$('#hapusVip').on('click', function() {
		// mymap.removeControl(routingControl.pop());
		clear_vip()
		$('input[name="vip[]"]').prop('checked',false);
		// mymap.setZoom(18);
	});
	
}


function vip(param = "") {
	prom_get_vip().then(function (data) {
		list_vip();
	});
}

function prom_get_vip() {
	let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()
	
	let filter = ''

	if (polda_id != '') {
		filter += `&polda_id=${polda_id}`
	}

	if (polres_id != '') {
		filter += `&polres_id=${polres_id}`
	}

	if (f_date_from != '' && f_date_from != undefined) {
		filter += `&tgl_start=${f_date_from}`
	}

	if (f_date_to != '' && f_date_to != undefined) {
		filter += `&tgl_end=${f_date_to}`
	}

	return new Promise(function (resolve, reject) {
		showToast()
		getDataSmNew(`${global_url_api}data_collecting/rengiat_vvip?${filter}`).then(r => {
			if (r.status == true) {
				vip_link = r
			}else {
				vip_link = { data : [], status : false, msg: r.msg}
			}
			closeToast()
			resolve(vip_link)
		});
	})
}

function list_vip() {
	$("#list_vip").html("");
	if ($('#jml_data_item_vip').length == 0) {
		$('#jml_data_group').append(`
		<div class="jml_data mt-2" id="jml_data_item_vip">
			<span id="jml_data">Jumlah Jalur VIP <img src="./my/logo/menu_vip.png" class="jml_data_img"> (<span><b id="counter_vip"></b></span>)</span>
		</div>
		`)
		const obj = document.getElementById(`counter_vip`);
		animateValue(obj, 0, vip_link.data.length, 200)
	}else {
		$('#jml_data_item_vip').remove()
	}
	setTimeout(() => {
		if (vip_link.data.length > 0) {
			var no = 0;
			vip_link.data.forEach((e) => {
				$("#list_vip")
					.append(`
					<li class="list-group-item list-group-item-action">
						<div class="form-check">
							<input class="form-check-input check-jalan-vip" type="radio" ${checked_vip == e.kode_data ? 'checked':''}  onchange="check_vip('${e.kode_data}')" name="vip[]" value="${no++}">
							<label class="form-check-label" for="flexRadioDefault1">
								${e.obyeklain != '' ? e.obyeklain : e.obyek} - ${e.pejabat} 
							</label>
						</div>
					</li>
					`);
			});
		}else {
			$("#list_vip")
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

function check_vip(kode='') {
    prom_check_vip().then(function (e) {
        // clear_vip().then(function (a) {
			clear_vip()
			checked_vip = kode
            get_vvip(kode).then(function (data) {
                vvip_route(data)
            })
        // })
    })
}

function prom_check_vip() {
	return new Promise(function (resolve, reject) {
		var check_vip = [];
		setTimeout(() => {
			$('input[name="vip[]"]:checked').each(function () {
				check_vip.push(vip_link[this.value]);
			});
			resolve(check_vip);
		}, 300);
	});
}

function prom_detail_vip(id='') {
	$('#tbl_detail_vip').html('')
	return new Promise (function(resolve, reject){
        jalur = [];
		$.ajax({
			type: "POST",
			url: "./API/intan/API/get_rengiat_vip_by_id",
			dataType: "json",
			data: {id : id},
			headers: {
				"token": "5b3dac76aaee24d14185cbc3d010fd20",
			  },
			success: function (r) {
				let v = r.values
				$('#tbl_detail_vip').append(`
					<tr>
						<td>${v[0].tanggal}</td>
						<td>${v[0].obyeklain != '' ? v[0].obyeklain : v[0].obyek}</td>
						<td>${v[0].pejabat}</td>
						<td>${v[0].asal}</td>
						<td>${v[0].tujuan}</td>
						<td>${v[0].wasdal}</td>
						<td>${v[0].anggota1}</td>
						<td>${v[0].anggota2}</td>
						<td>${v[0].anggota3}</td>
					</tr>
				`)

			},
		})
	});
}

function prom_clearMarkerVip() {
	return new Promise(function (resolve, reject) {
		if (routingControl) {
			for (let i = 0; i < routingControl.length; i++) {
				mymap.removeControl(routingControl[i]);
			}
			resolve(routingControl);
		}
	});
}

async function vvip_route(locations) {
	const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
	directionsDisplay = new google.maps.DirectionsRenderer({
		draggable:true,
		polylineOptions: {
			strokeColor: "red",
			strokeWeight: 5,
			strokeOpacity: 0.4
		}
	});
	directionsService = new google.maps.DirectionsService();
  
	directionsDisplay.setMap(map);
	$('#panel-direction-vip').html('')
	$('#panel-direction-vip').removeClass('d-none')
	directionsDisplay.setPanel(document.getElementById("panel-direction-vip"));
  
	var marker, i;
	var request = {
	  travelMode: google.maps.TravelMode.DRIVING
	};
  
	for (i = 0; i < locations.length; i++) {
	  marker = new AdvancedMarkerElement({
		position: new google.maps.LatLng(locations[i][1], locations[i][2]),
	  });
  
	  google.maps.event.addListener(marker, 'click', (function(marker, i) {
		return function() {
		  infowindow.setContent(locations[i][0]);
		  infowindow.open(map, marker);
		}
	  })(marker, i));
  
	  if (i == 0) request.origin = new google.maps.LatLng(locations[i][1], locations[i][2]);
	  else if (i == locations.length - 1) request.destination = new google.maps.LatLng(locations[i][1], locations[i][2]);
	  else {
		if (!request.waypoints) request.waypoints = [];
		request.waypoints.push({
		  location: new google.maps.LatLng(locations[i][1], locations[i][2]),
		  stopover: true
		});
	  }
  
	}
	directionsService.route(request, function(result, status) {
	  if (status == google.maps.DirectionsStatus.OK) {
		directionsDisplay.setDirections(result);
	  }
	});
  
  }


function get_vvip(kode = '') {
	// let a = getRToken().then(async r=>{
	// 	if (r.status != true) {
    //         window.location.href = `${global_url_ci}Auth/logout`
    //     }else {
			return new Promise (function(resolve, reject){
				jalur = [];
				$.ajax({
					type: "GET",
					url: `${global_url_api}data_collecting/route_rengiat_vvip/${kode}`,
					// url: `http://202.134.4.213:8082/api/V.0.1/data_collecting/route_rengiat_vvip/${kode}`,
					// url: `http://localhost:8082/api/V.0.1/data_collecting/route_rengiat_vvip/${kode}`,
					dataType: "json",
					headers: {
						"token": "5b3dac76aaee24d14185cbc3d010fd20",
						// "second_bearer" : r.data.second_bearer
						"secondbearer" : `${global_sbearer}`
					},
					success: function (r) {
						r.data.forEach(v => {
							// jalur.push(L.latLng(v.lat, v.lng));
							// jalur.push(new google.maps.Marker({
							// 	position: new google.maps.LatLng(v.lat, v.lng),
							//   }));
							jalur.push([v.nama_jalan_s,v.lat,v.lng])
						});
						resolve(jalur);

					},
				})
			});
	// 	}
	// })

	// return a

}

function clear_vip() {
	if (directionsDisplay != undefined) {
		$('#panel-direction-vip').addClass('d-none')
		directionsDisplay.setMap(null)
		checked_vip = ''
	}
}


function search_vip() {
	let f_vip = $('#search-vip').val().toLowerCase()
	let i_vip = $('.list_vip>.list-group-item')
	for(var i = 0; i<i_vip.length; i++){
		let span = $('.list-group-item>div>label')[i];
		if(span.innerHTML.toLowerCase().indexOf(f_vip) > -1){
			i_vip[i].style.display = "";
		}else{
			i_vip[i].style.display = "none";
		}
	}
}
