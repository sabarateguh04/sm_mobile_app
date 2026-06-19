var cctv_link = []
var cctv_arr = []

var hostname = window.location.hostname != 'backoffice.elingsolo.com' ? 'backoffice.elingsolo.com' : window.location.hostname;

function cctv(dinas = "") {
	cctv_link = [];
	prom_get_cctv(dinas).then(function (data) {
		list_cctv(dinas);
	});
}

function create_cctv_map(arrx, item, icon, dinas) {
	prom_create_cctv_map(arrx, item, icon, dinas).then(function (data) {
		setInfoWindowsCCTV(item);
	});
}

function prom_create_cctv_map(arrx, item, icn, dinas) {
	return new Promise(function (resolve, reject) {
		cctv_arr = [];
		arr = cctv_link;
		if (arrx) arr = arrx;
		// if (icon) icons = icon;
		// const marker = []
		var icon = {
			url: icn, // url
			scaledSize: new google.maps.Size(30, 30), // scaled size
		  };
	  
		  
		  for (let i = 0; i < arr.length; i++) {
				const marker = new google.maps.Marker({
				  position: { lat : arr[i].kordinat[0], lng : arr[i].kordinat[1]},
				  map,
				  icon : icon,
				//   animation: google.maps.Animation.DROP,
				  dinas : dinas
				})
			cctv_arr[arr[i].id] = marker;
		}

		resolve(cctv_arr);
	});
}

function setInfoWindowsCCTV(item2="", n_titik="CCTV") {
	var info = "";
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
		var arr = [];
		cctv_arr.forEach((v,i)=> {
			if (n_titik == "CCTV" && item2 == "traffic_counting") {
				const item2 = "Traffic Counting";
	
				arr.push('#total_kend_'+cctv_link[i].id);
				arr.push('#tanggal_kend_'+cctv_link[i].id);
				arr.push('#waktu_kend_'+cctv_link[i].id);
				google.maps.event.addListener(cctv_arr[i], 'click', function(){
					map.setZoom(15);
					map.setCenter({ lat : cctv_link[i].kordinat[0], lng : cctv_link[i].kordinat[1]});
					infowindow.setContent(`<div><p><b>${n_titik} - ${item2}</b></p>
					<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
					<div class="embed-responsive embed-responsive-16by9" style="width:50vh;">
					<iframe class="embed-responsive-item" src="${
									  "https://" +
									  hostname +
									  ":5000/?u=" +
									  cctv_link[i].rtsp
								  }" allowfullscreen></iframe>
					</div>
					<div class="mt-3">
					<table class="w-100">
					<tr>
					<td><b>Nama</b></td>
					<td>:</td>
					<td>${cctv_link[i].nama}</td>
					</tr>
					<tr>
					<td><b>Kordinat</b></td>
					<td>:</td>
					<td>${cctv_link[i].kordinat}</td>
					</tr>
					<tr>
					<td><b>Total Kendaraan Terdata</b></td>
					<td>:</td>
					<td id="total_kend_${cctv_link[i].id}">0</td>
					</tr>
					<tr>
					<td><b>Tanggal Terdata</b></td>
					<td>:</td>
					<td id="tanggal_kend_${cctv_link[i].id}">-</td>
					</tr>
					<tr>
					<td><b>Waktu Terdata</b></td>
					<td>:</td>
					<td id="waktu_kend_${cctv_link[i].id}">-</td>
					</tr>
					</table>
					</div>
					<div class="row" style="margin-right:0 !important;">
					</div>
					</div>
					`);
					infowindow.open(map, cctv_arr[i]);
					traffic_counting(arr,cctv_link[i].channel_name)
				});
	
			}else if (n_titik == "CCTV" && item2 == "traffic_category") {
				const item2 = "Traffic Category";
	
				arr.push('#total_kend_tc_'+cctv_link[i].id);
				arr.push('#tanggal_kend_tc_'+cctv_link[i].id);
				arr.push('#waktu_kend_tc_'+cctv_link[i].id);
				arr.push('#pelanggaran_total_'+cctv_link[i].id);
				arr.push('#t_'+cctv_link[i].id);
				google.maps.event.addListener(cctv_arr[i], 'click', function(){
					map.setZoom(15);
					map.setCenter({ lat : cctv_link[i].kordinat[0], lng : cctv_link[i].kordinat[1]});
					infowindow.setContent(`<div><p><b>${n_titik} - ${item2}</b></p>
					<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
					<div class="embed-responsive embed-responsive-16by9" style="width:50vh">
					<iframe class="embed-responsive-item" src="${
									  "https://" +
									  hostname +
									  ":5000/?u=" +
									  cctv_link[i].rtsp
								  }" allowfullscreen></iframe>
					</div>
					<div class="mt-3">
					<table class="w-100">
					<tr>
					<td><b>Nama</b></td>
					<td>:</td>
					<td>${cctv_link[i].nama}</td>
					</tr>
					<tr>
					<td><b>Kordinat</b></td>
					<td>:</td>
					<td>${cctv_link[i].kordinat}</td>
					</tr>
					<tr>
					<td><b>Total Pelanggaran Terdata</b></td>
					<td>:</td>
					<td id="pelanggaran_total_${cctv_link[i].id}">0</td>
					</tr>
					<tr>
					<tr>
					<td><b>Total Kendaraan Terdata</b></td>
					<td>:</td>
					<td id="total_kend_tc_${cctv_link[i].id}">0</td>
					</tr>
					<tr>
					<td><b>Tanggal Terdata</b></td>
					<td>:</td>
					<td id="tanggal_kend_tc_${cctv_link[i].id}">-</td>
					</tr>
					<tr>
					<td><b>Waktu Terdata</b></td>
					<td>:</td>
					<td id="waktu_kend_tc_${cctv_link[i].id}">-</td>
					</tr>
					</table>
					<table class="table table-bordered mt-3">
					<thead>
					<tr>
					<th>Kendaraan</th>
					<th>Jumlah</th>
					</tr>
					</thead>
					<tbody id="t_${cctv_link[i].id}">
					   
					</tbody>
					</table>
					</div>
					<div class="row" style="margin-right:0 !important;">
					</div>
					</div>
					`);
					infowindow.open(map, cctv_arr[i]);
					
					traffic_category(arr,cctv_link[i].channel_name,cctv_link[i].channel_code)
				});
	
			} else if (n_titik == "CCTV" && item2 == "average_speed") {
				const item2 = "Average Speed";
	
				  arr.push('#avg_speed_'+cctv_link[i].id);
				  arr.push('#tanggal_avg_'+cctv_link[i].id);
				  arr.push('#waktu_avg_'+cctv_link[i].id);

				  google.maps.event.addListener(cctv_arr[i], 'click', function(){
					map.setZoom(15);
					map.setCenter({ lat : cctv_link[i].kordinat[0], lng : cctv_link[i].kordinat[1]});
					infowindow.setContent(`<div><p><b>${n_titik} - ${item2}</b></p>
					<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
					<div class="embed-responsive embed-responsive-16by9" style="width:50vh">
					<iframe class="embed-responsive-item" src="${
									  "http://" +
									  hostname +
									  ":5000/?u=" +
									  cctv_link[i].rtsp
								  }" allowfullscreen></iframe>
					</div>
					<div class="mt-3">
					<table class="w-100">
					<tr>
					<td><b>Nama</b></td>
					<td>:</td>
					<td>${cctv_link[i].nama}</td>
					</tr>
					<tr>
					<td><b>Kordinat</b></td>
					<td>:</td>
					<td>${cctv_link[i].kordinat}</td>
					</tr>
					<tr>
					<td><b>Kecepatan Rata Rata</b></td>
					<td>:</td>
					<td id="avg_speed_${cctv_link[i].id}">0</td>
					</tr>
					<tr>
					<td><b>Tanggal Terdata</b></td>
					<td>:</td>
					<td id="tanggal_avg_${cctv_link[i].id}">-</td>
					</tr>
					<tr>
					<td><b>Waktu Terdata</b></td>
					<td>:</td>
					<td id="waktu_avg_${cctv_link[i].id}">-</td>
					</tr>
					
					</table>
					</div>
					<div class="row" style="margin-right:0 !important;">
					</div>
					</div>
					`);
					infowindow.open(map, cctv_arr[i]);

					average_speed(arr,cctv_link[i].channel_name)

				});
			} else {
				google.maps.event.addListener(cctv_arr[i], 'click', function(){
					map.setZoom(15);
					map.setCenter({ lat : cctv_link[i].kordinat[0], lng : cctv_link[i].kordinat[1]});
					infowindow.setContent(`<div><p><b>${n_titik}</b></p>
					<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
					<div class="embed-responsive embed-responsive-16by9" style="width:50vh;">
					<iframe class="embed-responsive-item" src="${
						"https://" +
						hostname +
						":5000/?u=" +
						cctv_link[i].rtsp
					}" allowfullscreen></iframe>
					</div>
					<div class="mt-3">
					<table class="w-100">
					<tr>
					<td><b>Nama</b></td>
					<td>:</td>
					<td>${cctv_link[i].nama}</td>
					</tr>
					<tr>
					<td><b>Kordinat</b></td>
					<td>:</td>
					<td>${cctv_link[i].kordinat}</td>
					</tr>
					</table>
					</div>
					<div class="row" style="margin-right:0 !important;">
					</div>
					</div>`);
					infowindow.open(map, cctv_arr[i]);
				});
			}
			
		})
}

function search_cctv(dinas="") {
	let f_cctv = $('#search-cctv-'+dinas).val().toLowerCase()
	let l_cctv = $('.list_cctv')
	let i_cctv = $('.list_cctv>.list-group-item')
	// var input = document.getElementById("cariKat");
	// var filter = input.value.toLowerCase();
	// var ul = document.getElementById("daftarKategori");
	// var li = document.querySelectorAll("li")
	for(var i = 0; i<i_cctv.length; i++){
		let span = $('.list-group-item>label')[i];
		if(span.innerHTML.toLowerCase().indexOf(f_cctv) > -1){
			i_cctv[i].style.display = "";
		}else{
			i_cctv[i].style.display = "none";
		}
	}
}

function list_cctv(dinas ='') {
	$("#list_cctv_"+dinas).html("");
	setTimeout(() => {
		var no = 0;
		if (cctv_link.length > 0) {
			cctv_link.forEach((e) => {
			$("#list_cctv_"+dinas)
					.append(`<li class="list-group-item list-group-item-action">
			  <input type="checkbox" class="form-check-input me-1 check-cctv-${dinas}" onchange="check_cctv('${dinas}')" name="cctv" value="${e.id}">
			  <label class="form-check-label name_cctv">${e.nama} </label></li>`);
			});
		}else {
			$('#list_cctv_'+dinas).append(`
				<li class="list-group-item list-group-item-action text-center">Tidak Ada Data</li>
			`)
		}
	}, 300);
}

function check_cctv(dinas='') {
	if (dinas == 'korlantas') {
		icn = "./my/satupeta/cctv_icon_korlantas.png"
	}

	if(dinas == 'dishub') {
		icn = "./my/satupeta/cctv_icon_dishub.png"
	}

	prom_check_cctv().then(function (hasil) {
		prom_clearMarkerCctv().then(function (data) {
			// cctv = hasil;
			create_cctv_map(hasil, label, icn, dinas);
		});
	});
}

function prom_clearMarkerCctv() {
	return new Promise(function (resolve, reject) {
		if (cctv_arr) {
				cctv_arr.forEach((v,i) => {
					
				cctv_arr[i].setMap(null);
				// mymap.removeLayer(cctv_arr[i]);
			});
			
			resolve(cctv_arr);
		}
	});
}

function prom_check_cctv() {
	return new Promise(function (resolve, reject) {
		var check_cctv = [];
		setTimeout(() => {
			$('input[name="cctv"]:checked').each(function () {
				check_cctv.push(cctv_link[this.value]);
			});
			resolve(check_cctv);
		}, 300);
	});
}

function prom_get_cctv(dinas = "", item = "") {
	let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()

	return new Promise(function (resolve, reject) {
		$.ajax({
			type: "POST",
			url: "./Pemetaan/get_cctv",
			dataType: "json",
			data: {
				a: item,
				dinas : dinas,
				polda_id : polda_id,
				polres_id : polres_id
			},
			success: function (r) {
				r.forEach(o => {
					cctv_link[o.id] = o;
				});

				resolve(cctv_link);
			},
		});
	});
}

function clearMarkerCctv(arr) {
	if (cctv_arr) {
		debugger;
			cctv_arr.forEach((v,i) => {
				cctv_arr[i].setMap(null);
			});
	}
}