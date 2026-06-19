var pengaduan_link = []
var pengaduan_arr = []

function pengaduan() {
	pengaduan_link = [];
	prom_get_pengaduan().then(function (data) {
		list_pengaduan();
	});
}

function create_pengaduan_map(arrx, item, icon) {
	prom_create_pengaduan_map(arrx, item, icon).then(function (data) {
		setInfoWindowsPengaduan(item);
	});
}

function prom_create_pengaduan_map(arrx, item, icn) {
	return new Promise(function (resolve, reject) {
		pengaduan_arr = [];
		arr = pengaduan_link;
		if (arrx) arr = arrx;
		// if (icon) icons = icon;
		// const marker = []
		var icon = {
			url: icn, // url
			scaledSize: new google.maps.Size(30, 30), // scaled size
		  };
		  for (let i = 0; i < arr.length; i++) {
				const marker = new google.maps.Marker({
				  position: { lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)},
				  map,
				  icon : icon,
				//   animation: google.maps.Animation.DROP,
				})
			pengaduan_arr[arr[i].rowid] = marker;
		}

		resolve(pengaduan_arr);
	});
}

function setInfoWindowsPengaduan(item2="", n_titik="TITIK PENGADUAN") {
	var info = "";
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
		var arr = [];
		pengaduan_arr.forEach((v,i)=> {
				google.maps.event.addListener(pengaduan_arr[i], 'click', function(){
					map.setZoom(15);
					map.setCenter({ lat : parseFloat(pengaduan_link[i].lat), lng : parseFloat(pengaduan_link[i].lng)});
						infowindow.setContent(`<div><p><b>${n_titik}</b></p>
						<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
						<div class="mt-3">
						<table class="w-100">
						<tr>
						<td><b>Tanggal</b></td>
						<td>:</td>
						<td>${isoDateToDate(pengaduan_link[i].tanggal)}</td>
						</tr>
						<tr>
						<td><b>Nama Pelapor</b></td>
						<td>:</td>
						<td>${pengaduan_link[i].nama_pelapor}</td>
						</tr>
						<tr>
						<td><b>Telpon Pelapor</b></td>
						<td>:</td>
						<td>${pengaduan_link[i].telp_pelapor}</td>
						</tr>
						<tr>
						<td><b>Kategori</b></td>
						<td>:</td>
						<td>${pengaduan_link[i].kategori}</td>
						</tr>
						<tr>
						<td><b>Sub Kategori</b></td>
						<td>:</td>
						<td>${pengaduan_link[i].sub_kategori}</td>
						</tr>
						<tr>
						<td><b>Saluran</b></td>
						<td>:</td>
						<td>${pengaduan_link[i].saluran}</td>
						</tr>
						<tr>
						<td><b>Kordinat</b></td>
						<td>:</td>
						<td>${pengaduan_link[i].lat},${pengaduan_link[i].lng}</td>
						</tr>
						<tr>
						<td><b>Nama Jalan</b></td>
						<td>:</td>
						<td>${pengaduan_link[i].nama_jalan_s}</td>
						</tr>
						</table>
						</div>
						<div class="row" style="margin-right:0 !important;">
						</div>
						</div>`);
						infowindow.setOptions({minWidth:600})
					infowindow.open(map, pengaduan_arr[i]);
				});
			
		})
}

function search_pengaduan() {
	let f_pengaduan = $('#search-pengaduan').val().toLowerCase()
	let l_pengaduan = $('.list_pengaduan')
	let i_pengaduan = $('.list_pengaduan>.list-group-item')
	// var input = document.getElementById("cariKat");
	// var filter = input.value.toLowerCase();
	// var ul = document.getElementById("daftarKategori");
	// var li = document.querySelectorAll("li")
	for(var i = 0; i<i_pengaduan.length; i++){
		let span = $('.list-group-item>label')[i];
		if(span.innerHTML.toLowerCase().indexOf(f_pengaduan) > -1){
			i_pengaduan[i].style.display = "";
		}else{
			i_pengaduan[i].style.display = "none";
		}
	}
}

function list_pengaduan() {
	$("#list_pengaduan").html("");
	setTimeout(() => {
		var no = 0;
		if (pengaduan_link.length > 0) {
			pengaduan_link.forEach((e) => {
			$("#list_pengaduan")
					.append(`<li class="list-group-item list-group-item-action">
			  <input type="checkbox" class="form-check-input me-1 check-pengaduan" onchange="check_pengaduan()" name="pengaduan" value="${e.rowid}">
			  <label class="form-check-label name_pengaduan">${e.nama_pelapor} (${e.kategori} - ${e.sub_kategori}) </label></li>`);
			});
		}else {
			$('#list_pengaduan').append(`
				<li class="list-group-item list-group-item-action text-center">Tidak Ada Data</li>
			`)
		}
	}, 300);
}

// var icn = []
function check_pengaduan() {
	// alert('tes')
	prom_check_pengaduan().then(function (hasil) {
		prom_clearMarkerPengaduan().then(function (data) {
			// pengaduan = hasil;
			create_pengaduan_map(hasil, label, './my/satupeta/emergency1.png');
		});
	});
}

function prom_clearMarkerPengaduan() {
	return new Promise(function (resolve, reject) {
		if (pengaduan_arr) {
				pengaduan_arr.forEach((v,i) => {
				pengaduan_arr[i].setMap(null);
				// mymap.removeLayer(pengaduan_arr[i]);
			});
			resolve(pengaduan_arr);
		}
	});
}

function prom_check_pengaduan() {
	return new Promise(function (resolve, reject) {
		var check_pengaduan = [];
		setTimeout(() => {
			$('input[name="pengaduan"]:checked').each(function () {
				check_pengaduan.push(pengaduan_link[this.value]);
			});
			resolve(check_pengaduan);
		}, 300);
	});
}

function prom_get_pengaduan(dinas = "", item = "") {
	let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()
	let filter = ''

	if (polda_id != '') {
		filter += `&polda_id=${polda_id}`
	}

	if (polres_id != '') {
		filter += `&polres_id=${polres_id}`
	}

	return new Promise(function (resolve, reject) {
		showToast()
		$.ajax({
			type: "GET",
			url: `${global_url_api}opsdal/emergency_masyarakat?${filter}`,
			dataType: "json",
			headers: {
				"token": "5b3dac76aaee24d14185cbc3d010fd20"
			  },
			success: function (r) {
				// console.log(r);
				r.data.forEach(o => {
					pengaduan_link[o.rowid] = o;
				});

				closeToast()
				resolve(pengaduan_link);
			},
		});
	});
}

function clearMarkerPengaduan(arr) {
	if (pengaduan_arr) {
		debugger;
			pengaduan_arr.forEach((v,i) => {
				pengaduan_arr[i].setMap(null);
			});
	}
}