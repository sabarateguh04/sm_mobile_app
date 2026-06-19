// Lokasi
var lokasi_arr = [];
var markersArray = [];

function lokasi(name = "") {
	prom_get_lokasi(name).then(function (hasil) {
		list_lokasi(hasil, name);
		// prom_check_lokasi().then(function(hasil2) {
		//   prom_clearMarkerCctv().then(function(data) {
		//     create_lokasi_map(hasil2,label,{
		//       url: "../my/images/cctv.png", // url
		//       scaledSize: new google.maps.Size(20, 20), // scaled size
		//       origin: new google.maps.Point(0,0), // origin
		//       anchor: new google.maps.Point(0,0) // anchor
		//     });
		//   });
		// });
	});
}

function list_lokasi(arr = [], name = "") {
	lokasi_arr = arr;
	$("#list_lokasi_all").html("");
	setTimeout(() => {
		var no = 0;
		arr.forEach((e) => {
			$("#list_lokasi_all")
				.append(`<li class="list-group-item list-group-item-action">
          <input type="checkbox" class="me-2 check-lokasi-all" onchange="check_lokasi('${name}')" name="lokasi_all[]" value="${no++}">
          <span class="name_cctv">${e.nama_lokasi} </span></li>`);
		});
	}, 300);
}

function prom_get_lokasi(name = "") {
	return new Promise(function (resolve, reject) {
		$.ajax({
			type: "POST",
			url: "./Pemetaan/get_poi?kategori=" + name + "&json=json",
			dataType: "json",
			data: {
				for: name,
			},
			success: function (r) {
				if (name == "polisi") polisi_link = r.data;
				if (name == "dishub") dishub_link = r.data;
				if (name == "rumah_sakit") rumah_sakit_link = r.data;
				if (name == "damkar") damkar_link = r.data;
				lokasi_link = r;
				resolve(r);
			},
		});
	});
}

function create_lokasi_info_window(arr, data_link, name) {
	return new Promise(function (resolve, reject) {
		var info = "";
		for (let i = 0; i < arr.length; i++) {
			info = `<div><p><b>${data_link[i].nama_lokasi}</b></p>
          <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
          <div class="mt-3">
          <table class="w-100">
          <tr>
          <td><b>Alamat</b></td>
          <td>:</td>
          <td>${data_link[i].deskripsi}</td>
          </tr>
          <tr style="margin-top:10px;">
          <td><b>Kordinat</b></td>
          <td>:</td>
          <td>${data_link[i].lat} , ${data_link[i].lng}</td>
          </tr>
          </table>
          </div>
          </div>`;

			mymap.setView(
				{
					lat: parseFloat(data_link[i].lat),
					lng: parseFloat(data_link[i].lng),
				},
				13,
				{ animation: true }
			);

			arr[i].bindPopup(info, {
				minWidth: 480,
			});

			arr[i].on("click", function (e) {
				mymap.setView("Hellow");
			});
		}

		resolve(true);
	});
}

function check_lokasi() {
	clearOverlays();
	$('input[name="lokasi_all[]"]:checked').each(function () {
		//debugger;
		var data=lokasi_arr[this.value];
		addMarker(data);
	});
}

function toggle_check_all(t){
	$('input[name="lokasi_all[]"]').prop("checked",t.checked);
	check_lokasi();
}

function clearOverlays() {
  for (var i = 0; i < markersArray.length; i++ ) {
    markersArray[i].setMap(null);
  }
  markersArray = [];
}

function addMarker(data) {
  var position={lat: parseFloat(data['lat']), lng:parseFloat(data['lng'])};
  const marker = new google.maps.Marker({
    position,
    map,
  });
  
  const infowindow = new google.maps.InfoWindow({
    content: '<div><p><b>'+data["nama_lokasi"]+'</b></p>'+
          '<hr style="margin-top:0 !important;margin-bottom:1rem !important;">'+
          '<div class="mt-3">'+
          '<table class="w-100">'+
          '<tr>'+
          //'<td><b>Deskripsi</b></td>'+
          //'<td>:</td>'+
          '<td>'+data["deskripsi"]+'</td>'+
          '</tr>'+
          '<tr style="margin-top:10px;">'+
          '<td><b>Koordinat : </b>'+
          data['lat']+' , '+data['lng']+'</td>'+
          '</tr>'+
          '</table>'+
          '</div>'+
          '</div>',
  });
  
  marker.addListener("click", () => {
    infowindow.open({
      anchor: marker,
      map,
      shouldFocus: true,
    });
  });
  markersArray.push(marker);
}
