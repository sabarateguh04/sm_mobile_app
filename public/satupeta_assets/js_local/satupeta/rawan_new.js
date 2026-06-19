let rawan_arr =[];
let rawan_link =[];
var rl = [];
var rm = [];
var rlong = [];
var rb = [];
var rpt = [];
var rtp = [];

function rawan(p="") {
	var item = '';
	if (p == 'Rawan Laka') {
		item = 'laka';
		if ($("#rl").hasClass("check")) {
			clearMarkerRawan(item);
			$("#rl").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rawan?filter=",p).then(function (data) {
				rawan_link[item] = data;
				create_titik_rawan(item,'./my/satupeta/rawan-laka.png',rawan_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Laka <img src="./my/satupeta/rawan-laka.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rawan_link[item].length, 200)
				})
			});
			$("#rl").addClass("check");
		}
	}
	if (p == 'Rawan Macet'){
		item = 'macet';
		if ($("#rm").hasClass("check")) {
			clearMarkerRawan(item);
			$("#rm").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rawan?filter=",p).then(function (data) {
				rawan_link[item] = data;
				create_titik_rawan(item,'./my/satupeta/rawan-macet.png',rawan_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Macet <img src="./my/satupeta/rawan-macet.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rawan_link[item].length, 200)
				})
			});
			$("#rm").addClass("check");
		}
	}
	if (p == 'Rawan Longsor'){
		item = 'longsor';
		if ($("#rlong").hasClass("check")) {
			clearMarkerRawan(item);
			$("#rlong").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rawan?filter=",p).then(function (data) {
				rawan_link[item] = data;
				create_titik_rawan(item,'./my/satupeta/rawan-longsor.png',rawan_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Longsor <img src="./my/satupeta/rawan-longsor.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rawan_link[item].length, 200)
				})
			});
			$("#rlong").addClass("check");
		}
	}
	if (p == 'Rawan Banjir'){
		item = 'banjir';
		if ($("#rb").hasClass("check")) {
			clearMarkerRawan(item);
			$("#rb").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rawan?filter=",p).then(function (data) {
				rawan_link[item] = data;
				create_titik_rawan(item,'./my/satupeta/rawan-banjir.png',rawan_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Banjir <img src="./my/satupeta/rawan-banjir.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rawan_link[item].length, 200)
				})
			});
			$("#rb").addClass("check");
		}
	}
	if (p == 'Rawan Pohon Tumbang'){
		item = 'pohon_tumbang';
		if ($("#rpt").hasClass("check")) {
			clearMarkerRawan(item);
			$("#rpt").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rawan?filter=",p).then(function (data) {
				rawan_link[item] = data;
				create_titik_rawan(item,'./my/satupeta/rawan-pohontumbang.png',rawan_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Pohon Tumbang <img src="./my/satupeta/rawan-pohontumbang.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rawan_link[item].length, 200)
				})
			});
			$("#rpt").addClass("check");
		}
	}
	if (p == 'Rawan Tindak Pidana'){
		item = 'tindak_pidana';
		if ($("#rtp").hasClass("check")) {
			clearMarkerRawan(item);
			$("#rtp").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rawan?filter=",p).then(function (data) {
				rawan_link[item] = data;
				create_titik_rawan(item,'./my/satupeta/rawan-tindakpidana.png',rawan_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Rawan Tindak Pidana <img src="./my/satupeta/rawan-tindakpidana.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rawan_link[item].length, 200)
				})
			});
			$("#rtp").addClass("check");
		}
	}
}

function create_titik_rawan(item, img, arr) {
	return new Promise(function (resolve, reject) {
		  let m_rawan = [];
	  
		  var icon = {
		url: img, // url
		scaledSize: new google.maps.Size(30, 30), // scaled size
	  };
  
	  
	  for (let i = 0; i < rawan_link[item].length; i++) {
		// window.setTimeout(() => {
		  
		  m_rawan.push(
			new google.maps.Marker({
			  position: { lat : parseFloat(rawan_link[item][i].lat), lng : parseFloat(rawan_link[item][i].lng)},
			  map,
			  icon : icon,
			  animation: google.maps.Animation.DROP,
			  polda_id : rawan_link[item][i].polda_id,
              polres_id : rawan_link[item][i].polres_id
			})
		  );
		// }, i * 200);
	  }
  
	  if (item == "laka") {
		rl.push(m_rawan);
		setInfoWindowsRawan(rawan_link[item], rl[0], item)
	  }
  
	  if (item == "macet") {
		rm.push(m_rawan);
		setInfoWindows(rawan_link[item], rm[0], item)
	  }

	  if (item == "longsor") {
		rlong.push(m_rawan);
		setInfoWindows(rawan_link[item], rlong[0], item)
	  }

	  if (item == "banjir") {
		rb.push(m_rawan);
		setInfoWindows(rawan_link[item], rb[0], item)
	  }

	  if (item == "pohon_tumbang") {
		rpt.push(m_rawan);
		setInfoWindows(rawan_link[item], rpt[0], item)
	  }

	  if (item == "tindak_pidana") {
		rtp.push(m_rawan);
		setInfoWindows(rawan_link[item], rtp[0], item)
	  }
  
		  resolve(rawan_link[item]);
	  })
  }

function clearMarkerRawan(arr) {
	if (arr == "laka") {
		for (let i = 0; i < rl[0].length; i++) {
			rl[0][i].setMap(null);
		}

    rl = [];
	}

	if (arr == "macet") {
		for (let i = 0; i < rm[0].length; i++) {
			rm[0][i].setMap(null);
		}

    rm = [];
	}

	if (arr == "longsor") {
		for (let i = 0; i < rlong[0].length; i++) {
			rlong[0][i].setMap(null);
		}

    rlong = [];
	}

	if (arr == "banjir") {
		for (let i = 0; i < rb[0].length; i++) {
			rb[0][i].setMap(null);
		}

    rb = [];
	}

	if (arr == "pohon_tumbang") {
		for (let i = 0; i < rpt[0].length; i++) {
			rpt[0][i].setMap(null);
		}

    rpt = [];
	}

	if (arr == "tindak_pidana") {
		for (let i = 0; i < rtp[0].length; i++) {
			rtp[0][i].setMap(null);
		}

    rtp = [];
	}

}

function clearAllMarkerRawan() {
	if (rl.length != 0) {
		for (let i = 0; i < rl[0].length; i++) {
			rl[0][i].setMap(null);
		}

    rl = [];
	}
	if (rm.length != 0) {
		for (let i = 0; i < rm[0].length; i++) {
			rm[0][i].setMap(null);
		}

    rm = [];
	}
	if (rlong.length != 0) {
		for (let i = 0; i < rlong[0].length; i++) {
			rlong[0][i].setMap(null);
		}

    rlong = [];
	}
	if (rb.length != 0) {
		for (let i = 0; i < rb[0].length; i++) {
			rb[0][i].setMap(null);
		}

    rb = [];
	}
	if (rpt.length != 0) {
		for (let i = 0; i < rpt[0].length; i++) {
			rpt[0][i].setMap(null);
		}

    rpt = [];
	}
	if (rtp.length != 0) {
		for (let i = 0; i < rtp[0].length; i++) {
			rtp[0][i].setMap(null);
		}

    rtp = [];
	}
	$("#rl").removeClass("check");
	$("#rm").removeClass("check");
	$("#rlong").removeClass("check");
	$("#rb").removeClass("check");
	$("#rpt").removeClass("check");
	$("#rtp").removeClass("check");
	$(`#jml_data_item_laka`).remove()
	$(`#jml_data_item_macet`).remove()
	$(`#jml_data_item_longsor`).remove()
	$(`#jml_data_item_banjir`).remove()
	$(`#jml_data_item_pohon_tumbang`).remove()
	$(`#jml_data_item_tindak_pidana`).remove()
}

function setInfoWindowsRawan(arr,titik,item) {
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
	infowindow.close(); // Close previously opened infowindow
	  for (let i = 0; i < arr.length; i++) {
		google.maps.event.addListener(titik[i], 'click', function(){
			  map.setZoom(15);
			  map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
			infowindow.setContent(`
			<div><p><b>TITIK RAWAN</b></p>
			<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			<div class="mt-3">
			<table class="w-100">
			<tr>
			<td><b>Nama</b></td>
			<td>:</td>
			<td>${rawan_link[item][i].namajalan}</td>
			</tr>
			<tr>
			<td><b>Kordinat</b></td>
			<td>:</td>
			<td>${rawan_link[item][i].lat}, ${rawan_link[item][i].lng}</td>
			</tr>
			<tr>
			<td><b>Status</b></td>
			<td>:</td>
			<td>${rawan_link[item][i].status}</td>
			</tr>
			</table>
			</div>
			</div>
			`);
			infowindow.open(map, titik[i]);
		});
	  }
  }