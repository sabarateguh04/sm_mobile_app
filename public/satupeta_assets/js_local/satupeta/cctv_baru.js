var cctv_link = []
var cctv_arr = []
var g_rtsp = []
var s_rtsp

var hostname = window.location.hostname != 'sp.kodal.id' ? 'sp.kodal.id' : window.location.hostname;

function cctv(pd_id,ps_id) {
	cctv_link = [];
	prom_get_cctv(pd_id,ps_id).then(function (data) {
		list_cctv();
		render_all_cctv_on_map();
	});
}

function render_all_cctv_on_map() {
	const rows = Object.values(cctv_link || {}).filter(Boolean);
	if (rows.length === 0) {
		return;
	}

	var icn = [];
	for (let i = 0; i < rows.length; i++) {
		if (rows[i].instansi == 'korlantas') {
			if(rows[i].jenis_cctv == 'Analytic') {
				icn.push("./my/satupeta/cctv_icon_analytic1.png");
			}else if(rows[i].jenis_cctv == 'Analytic checkpoint'){
				icn.push("./my/satupeta/cctv_icon_analytic2.png");
			}else{
				icn.push("./my/satupeta/cctv_icon_korlantas.png");
			}
		} else if(rows[i].instansi == 'dishub') {
			if(rows[i].jenis_cctv == 'Analytic') {
				icn.push("./my/satupeta/cctv_icon_analytic.png");
			}else{
				icn.push("./my/satupeta/cctv_icon_dishub.png");
			}
		} else {
			icn.push("./my/satupeta/cctv_icon_korlantas.png");
		}
	}

	prom_clearMarkerCctv().then(function () {
		create_cctv_map(rows, label, icn);
	});
}

function create_cctv_map(arrx, item, icon) {
	prom_create_cctv_map(arrx, item, icon).then(function (data) {
		setInfoWindowsCCTV(item);
	});
}

function prom_create_cctv_map(arrx, item, icn) {
	return new Promise(function (resolve, reject) {
		cctv_arr = [];
		arr = cctv_link;
		if (arrx) arr = arrx;
		// if (icon) icons = icon;
		// const marker = []
		var icon = {
			url: '', // url
			scaledSize: new google.maps.Size(30, 30), // scaled size
		  };
		  for (let i = 0; i < arr.length; i++) {
			  icon.url = icn[i]
				const marker = new google.maps.Marker({
				  position: { lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)},
				  map,
				  icon : icon,
				//   animation: google.maps.Animation.DROP,
				  jenis_cctv : arr[i].jenis_cctv,
				  dahua : arr[i].channel_id 
				})
			cctv_arr[arr[i].rowid] = marker;
		}

		resolve(cctv_arr);
	});
}

function setInfoWindowsCCTV(item2="", n_titik="CCTV") {
	var info = "";
	if (g_rtsp.length != '') {
		for (let i = 0; i < g_rtsp.length; i++) {
			stream_jump('stop', { source : g_rtsp[i]}).then((data) => {
				console.log('stop stream');
				g_rtsp = []
				localStorage.setItem('rtsp',JSON.stringify([]))
			})
		}
	}
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
	setInterval(function ()
	{
		if(!infowindow.getMap()) {
			if (g_rtsp.length != '') {
				for (let i = 0; i < g_rtsp.length; i++) {
					if (s_rtsp != g_rtsp[i]) {
						stream_jump('stop', { source : g_rtsp[i]}).then((data) => {
							console.log('stop stream');
							g_rtsp = []
							localStorage.setItem('rtsp',JSON.stringify([]))
						})
					}
				}
			}
		}
	}, 1000);
		var arr = [];
		cctv_arr.forEach((v,i)=> {
			google.maps.event.addListener(cctv_arr[i], 'click', function(){
				if (g_rtsp.length != '') {
					for (let i = 0; i < g_rtsp.length; i++) {
						stream_jump('stop', { source : g_rtsp[i]}).then((data) => {
							console.log('stop stream');
							g_rtsp = []
							localStorage.setItem('rtsp',JSON.stringify([]))
						})
					}
				}
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(cctv_link[i].lat), lng : parseFloat(cctv_link[i].lng)});
				if (cctv_arr[i].jenis_cctv == "Analytic" || cctv_arr[i].jenis_cctv == "Analytic checkpoint") {
					if (cctv_arr[i].dahua != '') {
						stream_jump('play',{ source : cctv_link[i].rtsp_cctv}).then((data) => {
							g_rtsp.push(data.streamname)
							localStorage.setItem('rtsp', JSON.stringify(g_rtsp));
							s_rtsp = data.streamname
							setTimeout(() => {
								infowindow.setContent(`<div><p><b>${n_titik}</b></p>
								<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
								<div class="w-100" style="height:40vh;">
									<div class="loader" style='position:absolute; left:auto; right:40%; top:20%;'></div>
									<iframe class="w-100" src="" allowfullscreen id="stream_tes"></iframe>
								</div>
								<div class="mt-3">
								<table class="w-100">
								<tr>
								<td><b>Total kendaraan Terdata</b></td>
								<td>:</td>
								<td><span id="dtc_jml"></span></td>
								</tr>
								<tr>
								<tr>
								<td><b>Tanggal</b></td>
								<td>:</td>
								<td>${current_date()}</td>
								</tr>
								<tr>
								<td><b>Jam Terdata</b></td>
								<td>:</td>
								<td><span id="dtc_start"></span> - <span id="dtc_end"></span></td>
								</tr>
								<tr>
								<td><b>Nama</b></td>
								<td>:</td>
								<td>${cctv_link[i].nama_cctv}</td>
								</tr>
								<tr>
								<td><b>Jenis cctv</b></td>
								<td>:</td>
								<td>${cctv_link[i].jenis_cctv}</td>
								</tr>
								<tr>
								<td><b>Kordinat</b></td>
								<td>:</td>
								<td>${cctv_link[i].lat},${cctv_link[i].lng}</td>
								</tr>
								</table>
								<div id="t_dahua_category">
								</div>
								</div>
								<div class="row" style="margin-right:0 !important;">
								</div>
								</div>`);
								showToast()
								setTimeout(() => {
									get_data_dahua_traffic_counting(cctv_link[i].channel_id)
									setTimeout(() => {
										$('.loader').addClass('d-none');
										$('#stream_tes').attr('src', `${"https://" + hostname + ":5050/?u=" +data.rtsp }`)
									}, 5000);
								}, 1000);
								infowindow.setOptions({minWidth:600})
							}, 200);
						})
					}else {
						infowindow.setContent(`<div><p><b>${n_titik}</b></p>
						<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
						<div class="row">
							<div class="col-6">
								<div class="w-100" style="height:30vh;">
								<iframe class="w-100" src="${
									"https://" +
									hostname +
									":5000/?u=" +
									cctv_link[i].rtsp_cctv
								}" allowfullscreen></iframe>
								</div>
							</div>
							<div class="col-6">
								<p>Tanggal : ${current_date()} </p>
								<div id="chart-sst"></div>
							</div>
						</div>
						<div class="mt-3">
						<table class="w-100">
						<tr>
						<td><b>Nama</b></td>
						<td>:</td>
						<td>${cctv_link[i].nama_cctv}</td>
						</tr>
						<tr>
						<td><b>Jenis cctv</b></td>
						<td>:</td>
						<td>${cctv_link[i].jenis_cctv}</td>
						</tr>
						<tr>
						<td><b>Kordinat</b></td>
						<td>:</td>
						<td>${cctv_link[i].lat},${cctv_link[i].lng}</td>
						</tr>
						</table>
						</div>
						<div class="row" style="margin-right:0 !important;">
						</div>
						</div>`);
						setTimeout(() => {
							show_chart_sst(cctv_link[i].channel_code)
						}, 1000);
						infowindow.setOptions({minWidth:1000})
					}
				} else {
					stream_jump('play',{ source : cctv_link[i].rtsp_cctv}).then((data) => {
						g_rtsp.push(data.streamname)
						localStorage.setItem('rtsp', JSON.stringify(g_rtsp));
						s_rtsp = data.streamname
						setTimeout(() => {
							infowindow.setContent(`<div><p><b>${n_titik}</b></p>
							<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
							<div class="w-100" style="height:40vh;">
								<div class="loader" style='position:absolute; left:auto; right:40%; top:20%;'></div>
								<iframe class="w-100" src="" allowfullscreen id="stream_tes"></iframe>
							</div>
							<div class="mt-3">
							<table class="w-100">
							<tr>
							<td><b>Nama</b></td>
							<td>:</td>
							<td>${cctv_link[i].nama_cctv}</td>
							</tr>
							<tr>
							<td><b>Jenis cctv</b></td>
							<td>:</td>
							<td>${cctv_link[i].jenis_cctv}</td>
							</tr>
							<tr>
							<td><b>Kordinat</b></td>
							<td>:</td>
							<td>${cctv_link[i].lat},${cctv_link[i].lng}</td>
							</tr>
							</table>
							</div>
							<div class="row" style="margin-right:0 !important;">
							</div>
							</div>`);
							setTimeout(() => {
								setTimeout(() => {
									$('.loader').addClass('d-none');
									$('#stream_tes').attr('src', `${"https://" + hostname + ":5050/?u=" +data.rtsp }`)
								}, 5000);
							}, 1000);
							infowindow.setOptions({minWidth:600})
						},200);
					})
				}
				infowindow.open(map, cctv_arr[i]);
			});
			google.maps.event.addListener(cctv_arr[i],'closeclick',function(){
				if (g_rtsp.length != '') {			
					for (let i = 0; i < g_rtsp.length; i++) {
						stream_jump('stop', { source : g_rtsp[i]}).then((data) => {
							console.log('stop stream');
							g_rtsp = []
						})
					}
				}
			 });
		})
}

function search_cctv() {
	let f_cctv = $('#search-cctv').val().toLowerCase()
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

function list_cctv() {
	$("#list_cctv").html("");
	setTimeout(() => {
		var no = 0;
		if (cctv_link.length > 0) {
			cctv_link.forEach((e) => {
			$("#list_cctv")
					.append(`<li class="list-group-item list-group-item-action">
			  <input type="checkbox" class="form-check-input me-1 check-cctv" onchange="check_cctv()" name="cctv" value="${e.rowid}">
			  <label class="form-check-label name_cctv">${e.nama_cctv} </label></li>`);
			});
		}else {
			$('#list_cctv').append(`
				<li class="list-group-item list-group-item-action text-center">Tidak Ada Data</li>
			`)
		}
	}, 300);
}

// var icn = []
function check_cctv() {
	prom_check_cctv().then(function (hasil) {
		var icn = []
		if (g_rtsp.length != '') {	
			for (let i = 0; i < g_rtsp.length; i++) {
				stream_jump('stop', { source : g_rtsp[i]}).then((data) => {
					console.log('stop stream');
					g_rtsp = []
				})
			}
		}
		for (let i = 0; i < hasil.length; i++) {
			if (hasil[i].instansi == 'korlantas') {
				if(hasil[i].jenis_cctv == 'Analytic') {
					icn.push("./my/satupeta/cctv_icon_analytic1.png")
				}else if(hasil[i].jenis_cctv == 'Analytic checkpoint'){
					icn.push("./my/satupeta/cctv_icon_analytic2.png")
				}else{
					icn.push("./my/satupeta/cctv_icon_korlantas.png")
				}
			}
		
			if(hasil[i].instansi == 'dishub') {
				if(hasil[i].jenis_cctv == 'Analytic') {
					icn.push("./my/satupeta/cctv_icon_analytic.png")
				}else{
					icn.push("./my/satupeta/cctv_icon_dishub.png")
				}
			}

			if (hasil[i].instansi == null) {
				icn.push("./my/satupeta/cctv_icon_korlantas.png")
			}
		}
		prom_clearMarkerCctv().then(function (data) {
			// cctv = hasil;
			create_cctv_map(hasil, label, icn);
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

function prom_get_cctv(pd_id = "", ps_id = "") {
	let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()
	let filter = ''

	if (polda_id != '') {
		filter += `&polda_id=${polda_id}`
	}else if(pd_id != '') {
		filter += `&polda_id=${pd_id}`
	}

	if (polres_id != '') {
		filter += `&polres_id=${polres_id}`
	}else if(ps_id != ''){
		filter += `&polres_id=${ps_id}`
	}

	showToast()
	let a = getRToken().then(async r=>{
        //Default options are marked with *
        if (r.status != true) {
            alert('gagal get new bearer')
        }else {
			return new Promise(function (resolve, reject) {
				$.ajax({
					type: "GET",
					url: `${global_url_api}patroli_cctv/cctv?${filter}`,
					dataType: "json",
					headers: {
						"token": "5b3dac76aaee24d14185cbc3d010fd20",
						"secondbearer" : r.data.second_bearer
					  },
					success: function (r) {
						// console.log(r);
						r.data.forEach(o => {
							cctv_link[o.rowid] = o;
						});
		
						closeToast()
						resolve(cctv_link);
					},
				});
			});
        }
    })
    return a
}

function auto_show_cctv_on_map() {
	let retry = 0;
	const maxRetry = 20;

	const timer = setInterval(function () {
		retry++;
		if (typeof map !== 'undefined' && map) {
			clearInterval(timer);
			cctv();
		}

		if (retry >= maxRetry) {
			clearInterval(timer);
		}
	}, 500);
}

$(function () {
	auto_show_cctv_on_map();
});

function clearMarkerCctv(arr) {
	if (cctv_arr) {
		debugger;
			cctv_arr.forEach((v,i) => {
				cctv_arr[i].setMap(null);
			});
	}
}

function show_chart_sst(camname='') {
	prom_get_sst_by_cam(camname).then((data) => {
		show_chart(data)
	})
}

function prom_get_sst_by_cam(camname) {
	return new Promise(function (resolve, reject) {
		showToast()
		$.ajax({
			type: "GET",
			url: `./Api_sst/get_dashanalitik_by_cam?korelasi=${camname}`,
			dataType: "json",
			success: function (r) {
				closeToast()
				resolve(r);
			},
		});
	});
}

function get_data_dahua_traffic_counting(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    var yyyy = today.getFullYear();
	let c_date = yyyy + '-' + mm + '-' + dd
	let stamp_start = today.setTime(today.getTime() - 30*60*1000)
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime())
	let s_end = String(stamp_end).slice(0, -3);
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(`${global_url_api}sm_integration/traffic_counting?channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(s_start)}&time_end=${convert_timestamp_to_time(s_end)}`, {}, token_sm).then((data) => {
			$('#t_dahua_category').html('')
			if (data.status != false) {
				get_data_dahua_traffic_category(cid,convert_timestamp_to_time(s_start),convert_timestamp_to_time(s_end),c_date)
				$('#dtc_jml').html(data.data[0].jml)
				$('#dtc_start').html(convert_timestamp_to_time(s_start))
				$('#dtc_end').html(convert_timestamp_to_time(s_end))
			}else {
				$('#dtc_jml').html('0')
				$('#dtc_start').html(convert_timestamp_to_time(s_start))
				$('#dtc_end').html(convert_timestamp_to_time(s_end))
			}
			closeToast()
		})
	})
}

function get_data_dahua_traffic_category(cid,s_start,s_end,c_date) {
	return new Promise(function (resolve, reject) {
		getDataSmNew(`${global_url_api}sm_integration/traffic_category?channel_id=${cid}&tanggal=${c_date}&time_start=${s_start}&time_end=${s_end}`, {}, token_sm).then((data) => {
			$("#t_dahua_category").append(`
			<table class="table table-bordered w-100">
				<thead>
					<tr>
						<th>Jumlah</th>
						<th>Klasifikasi</th>
					</tr>
				</thead>
				<tbody id="tb_dahua_category">
				</tbody>
			</table>
			`)
			data.data.forEach(e => {
				$('#tb_dahua_category').append(`
				<tr>
					<td>${e.jml}</td>
					<td>${e.jenis_kendaraan}</td>
				</tr>
				`)
			});
		})
	})
}

function show_chart(data= {}) {
	var options = {
		series: [{
		name : "Total",
		data : data.total
		}, {
		name: 'Motor',
		data: data.motor
	  	}, {
		name: 'Mobil',
		data: data.mobil
	  }, {
		  name : "Bis",
		  data : data.bis
	  }, {
		  name : "Truk",
		  data : data.truk
	  }],
		chart: {
		height: 300,
		type: 'area'
	  },
	  dataLabels: {
		enabled: false
	  },
	  stroke: {
		curve: 'smooth'
	  },
	  xaxis: {
		// type: 'datetime',
		// categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z"]
		categories: ["00.00", "01.00", "02.00", "03.00", "04.00", "05.00", "06.00", "07.00", "08.00", "09.00", "10.00", "11.00", "11.00", "12.00", "13.00", "14.00", "15.00", "16.00", "17.00", "18.00", "19.00", "20.00", "21.00", "22.00", "23.00"]
	  },
	  tooltip: {
		// x: {
		//   format: 'dd/MM/yy HH:mm'
		// },
	  },
	  };

	  var chart = new ApexCharts(document.querySelector("#chart-sst"), options);
	  chart.render();
}

async function stream_jump(stat = '',data={}) {
	const base = (typeof global_url_stream === 'string' && global_url_stream)
		? global_url_stream.replace(/\/$/, '')
		: 'https://satupeta.kodal.id:3000';
	const response = await fetch(`${base}/${stat}`, {
		method: "POST", // *GET, POST, PUT, DELETE, etc.
		mode: "cors", // no-cors, *cors, same-origin
		cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
		credentials: "same-origin", // include, *same-origin, omit
		headers: {
			"Content-Type": "application/json",
			Accept: "application/json",
		},
		redirect: "follow", // manual, *follow, error
		referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
		body: JSON.stringify(data), // body data type must match "Content-Type" header
	});
	return response.json(); // parses JSON response into native JavaScript objects
}