var jalur = [];
var routingControl = [];

var directionsDisplay, directionsService, request;

function vip(param = "") {
	prom_get_vip().then(function (data) {
		list_vip();
	});
}

function prom_get_vip() {
	let f_date_from = $('#f_date_from').val()
	let f_date_to = $('#f_date_to').val()
	let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()
	return new Promise(function (resolve, reject) {
		$.ajax({
			type: "POST",
			url: "./API/intan/API/get_rengiat_vip",
			dataType: "json",
			headers: {
				"token": "5b3dac76aaee24d14185cbc3d010fd20",
			  },
			data: {from : f_date_from, to : f_date_to, polda_id : polda_id, polres_id : polres_id},
			success: function (r) {
				vip_link = r;
				resolve(vip_link);
			},
		});
	});
}

function list_vip() {
	$("#list_vip").html("");
	setTimeout(() => {
		var no = 0;
		vip_link.values.forEach((e) => {
			$("#list_vip")
				.append(`
				<li class="list-group-item list-group-item-action">
					<div class="form-check">
						<input class="form-check-input check-jalan-vip" type="radio" onchange="check_vip(${e.rengiatid})" name="vip[]" value="${no++}">
						<label class="form-check-label" for="flexRadioDefault1">
							${e.obyeklain != '' ? e.obyeklain : e.obyek} - ${e.pejabat} <br> <button class="btn btn-primary btn-sm" onclick="prom_detail_vip(${e.rowid})" data-bs-toggle="modal" data-bs-target="#vipModal">detail</button>
						</label>
					</div>
				</li>
				`);
		});
	}, 300);
}

// function check_vip(id = '') {
// 	get_vvip(id).then(function (data) {
//         vvip_route(data);
// 	});

// }

function check_vip(id='') {
    prom_check_vip().then(function (e) {
        // clear_vip().then(function (a) {
			clear_vip()
            get_vvip(id).then(function (data) {
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

function vvip_route(locations) {
	directionsDisplay = new google.maps.DirectionsRenderer();
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
	  marker = new google.maps.Marker({
		position: new google.maps.LatLng(locations[i][1], locations[i][2]),
	  });
  
	  google.maps.event.addListener(marker, 'click', (function(marker, i) {
		return function() {
		  infowindow.setContent(locations[i][0]);
		  infowindow.open(map, marker);
		}
	  })(marker, i));
  
	  if (i == 0) request.origin = marker.getPosition();
	  else if (i == locations.length - 1) request.destination = marker.getPosition();
	  else {
		if (!request.waypoints) request.waypoints = [];
		request.waypoints.push({
		  location: marker.getPosition(),
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


function get_vvip(id = '') {
	
	return new Promise (function(resolve, reject){
        jalur = [];
		$.ajax({
			type: "POST",
			url: "./API/intan/API/get_rengiat_vip_detail",
			dataType: "json",
			data: {rengiatid : id},
			headers: {
				"token": "5b3dac76aaee24d14185cbc3d010fd20",
			  },
			success: function (r) {
				r.values.forEach(v => {
					// jalur.push(L.latLng(v.lat, v.lng));
					// jalur.push(new google.maps.Marker({
					// 	position: new google.maps.LatLng(v.lat, v.lng),
					//   }));
					jalur.push([v.lokasi,v.lat,v.lng])
				});
				resolve(jalur);

			},
		})
	});

}

function clear_vip() {
	if (directionsDisplay != undefined) {
		$('#panel-direction-vip').addClass('d-none')
		directionsDisplay.setMap(null)
	}
}


$('#hapusVip').on('click', function() {
    // mymap.removeControl(routingControl.pop());
	clear_vip()
    $('input[name="vip[]"]').prop('checked',false);
    // mymap.setZoom(18);
});


