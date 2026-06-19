let rengiat_arr =[];
let rengiat_link =[];
var rgprs = [];
var rgrsc = [];
var rgs = [];
var rgp = [];
var rgrss = [];

function rengiat(p="") {
	var item = '';
	if (p == 'PAM Road Savety') {
		item = 'rgprs';
		if ($("#rgprs").hasClass("check")) {
			clearMarkerRengiat(item);
			$("#rgprs").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rengiat?filter=",p).then(function (data) {
				rengiat_link[item] = data;
				create_titik_rengiat(item,'./my/satupeta/giatmasyarakat1.png',rengiat_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah PAM Road Safety <img src="./my/satupeta/giatmasyarakat1.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rengiat_link[item].length, 200)
				})
			});
			$("#rgprs").addClass("check");
		}
	}
	if (p == 'Road Savety Campaign') {
		item = 'rgrsc';
		if ($("#rgrsc").hasClass("check")) {
			clearMarkerRengiat(item);
			$("#rgrsc").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rengiat?filter=",p).then(function (data) {
				rengiat_link[item] = data;
				create_titik_rengiat(item,'./my/satupeta/giatmasyarakat1.png',rengiat_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Road Safety Campaign <img src="./my/satupeta/giatmasyarakat1.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rengiat_link[item].length, 200)
				})
			});
			$("#rgrsc").addClass("check");
		}
	}
	if (p == 'Sosialisasi') {
		item = 'rgs';
		if ($("#rgs").hasClass("check")) {
			clearMarkerRengiat(item);
			$("#rgs").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rengiat?filter=",p).then(function (data) {
				rengiat_link[item] = data;
				create_titik_rengiat(item,'./my/satupeta/giatmasyarakat1.png',rengiat_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Sosialisasi <img src="./my/satupeta/giatmasyarakat1.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rengiat_link[item].length, 200)
				})
			});
			$("#rgs").addClass("check");
		}
	}
	if (p == 'Publikasi') {
		item = 'rgp';
		if ($("#rgp").hasClass("check")) {
			clearMarkerRengiat(item);
			$("#rgp").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rengiat?filter=",p).then(function (data) {
				rengiat_link[item] = data;
				create_titik_rengiat(item,'./my/satupeta/giatmasyarakat1.png',rengiat_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Publikasi <img src="./my/satupeta/giatmasyarakat1.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rengiat_link[item].length, 200)
				})
			});
			$("#rgp").addClass("check");
		}
	}
	if (p == 'Survey') {
		item = 'rgrss';
		if ($("#rgrss").hasClass("check")) {
			clearMarkerRengiat(item);
			$("#rgrss").removeClass("check");
			$(`#jml_data_item_${item}`).remove()
		} else {
			prom_get_data("./Pemetaan/get_rengiat?filter=",p).then(function (data) {
				rengiat_link[item] = data;
				create_titik_rengiat(item,'./my/satupeta/giatmasyarakat1.png',rengiat_link[item]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2" id="jml_data_item_${item}">
								<span id="jml_data">Jumlah Road Safety Survay <img src="./my/satupeta/giatmasyarakat1.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_${item}`);
					  animateValue(obj, 0, rengiat_link[item].length, 200)
				})
			});
			$("#rgrss").addClass("check");
		}
	}
}

function create_titik_rengiat(item, img, arr) {
	return new Promise(function (resolve, reject) {
		  let m_rawan = [];
	  
		  var icon = {
		url: img, // url
		scaledSize: new google.maps.Size(30, 30), // scaled size
	  };
  
	  
	  for (let i = 0; i < rengiat_link[item].length; i++) {
		// window.setTimeout(() => {
		  
		  m_rawan.push(
			new google.maps.Marker({
			  position: { lat : rengiat_link[item][i].lat, lng : rengiat_link[item][i].lng},
			  map,
			  icon : icon,
			  animation: google.maps.Animation.DROP,
			})
		  );
		// }, i * 200);
	  }
  
	  if (item == "rgprs") {
		rgprs.push(m_rawan);
		setInfoWindowsRengiat(rengiat_link[item], rgprs[0], item)
	  }

	  if (item == "rgrsc") {
		rgrsc.push(m_rawan);
		setInfoWindowsRengiat(rengiat_link[item], rgrsc[0], item)
	  }

	  if (item == "rgs") {
		rgs.push(m_rawan);
		setInfoWindowsRengiat(rengiat_link[item], rgs[0], item)
	  }
	  
	  if (item == "rgp") {
		rgp.push(m_rawan);
		setInfoWindowsRengiat(rengiat_link[item], rgp[0], item)
	  }

	  if (item == "rgrss") {
		rgrss.push(m_rawan);
		setInfoWindowsRengiat(rengiat_link[item], rgrss[0], item)
	  }
  
		  resolve(rengiat_link[item]);
	  })
  }

function clearAllMarkerRengiat() {
	if (rgprs.length != 0) {
		for (let i = 0; i < rgprs[0].length; i++) {
			rgprs[0][i].setMap(null);
		}

    rgprs = [];
	}
	if (rgrsc.length != 0) {
		for (let i = 0; i < rgrsc[0].length; i++) {
			rgrsc[0][i].setMap(null);
		}

    rgrsc = [];
	}
	if (rgs.length != 0) {
		for (let i = 0; i < rgs[0].length; i++) {
			rgs[0][i].setMap(null);
		}

    rgs = [];
	}
	if (rgp.length != 0) {
		for (let i = 0; i < rgp[0].length; i++) {
			rgp[0][i].setMap(null);
		}

    rgp = [];
	}
	if (rgrss.length != 0) {
		for (let i = 0; i < rgrss[0].length; i++) {
			rgrss[0][i].setMap(null);
		}

    rgrss = [];
	}
	$("#rgprs").removeClass("check");
	$("#rgrsc").removeClass("check");
	$("#rgs").removeClass("check");
	$("#rgp").removeClass("check");
	$("#rgrss").removeClass("check");
	$(`#jml_data_item_rgprs`).remove()
	$(`#jml_data_item_rgrsc`).remove()
	$(`#jml_data_item_rgs`).remove()
	$(`#jml_data_item_rgp`).remove()
	$(`#jml_data_item_rgrss`).remove()

}

function clearMarkerRengiat(arr) {
	if (arr == "rgprs") {
		for (let i = 0; i < rgprs[0].length; i++) {
			rgprs[0][i].setMap(null);
		}

    rgprs = [];
	}

	if (arr == "rgrsc") {
		for (let i = 0; i < rgrsc[0].length; i++) {
			rgrsc[0][i].setMap(null);
		}

    rgrsc = [];
	}

	if (arr == "rgs") {
		for (let i = 0; i < rgs[0].length; i++) {
			rgs[0][i].setMap(null);
		}

    rgs = [];
	}

	if (arr == "rgp") {
		for (let i = 0; i < rgp[0].length; i++) {
			rgp[0][i].setMap(null);
		}

    rgp = [];
	}

	if (arr == "rgrss") {
		for (let i = 0; i < rgrss[0].length; i++) {
			rgrss[0][i].setMap(null);
		}

    rgrss = [];
	}
}

function setInfoWindowsRengiat(arr,titik,item) {
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
	infowindow.close(); // Close previously opened infowindow
	  for (let i = 0; i < arr.length; i++) {
		google.maps.event.addListener(titik[i], 'click', function(){
			  map.setZoom(15);
			  map.setCenter({ lat : arr[i].lat, lng : arr[i].lng});
			infowindow.setContent(`
			  <div><p><b>${rengiat_link[item][i].jenis}</b></p>
              <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
              <div class="mt-3">
              <table class="w-100">
			  <tr>
              <td><b>Tanggal</b></td>
              <td>:</td>
              <td>${rengiat_link[item][i].tgldari} s/d ${rengiat_link[item][i].tglsampai}</td>
              </tr>
			  <tr>
              <td><b>Jam</b></td>
              <td>:</td>
              <td>${rengiat_link[item][i].jamdari} s/d ${rengiat_link[item][i].jamsampai}</td>
              </tr>
              <tr>
              <td><b>Nama</b></td>
              <td>:</td>
              <td>${rengiat_link[item][i].lokasi}</td>
              </tr>
              <tr>
              <td><b>Kordinat</b></td>
              <td>:</td>
              <td>${rengiat_link[item][i].lat}, ${rengiat_link[item][i].lng}</td>
              </tr>
			  <tr>
              <td><b>Jenis</b></td>
              <td>:</td>
              <td>${rengiat_link[item][i].jenis}</td>
              </tr>
			  <tr>
              <td><b>Rincian Kegiatan</b></td>
              <td>:</td>
              <td>${rengiat_link[item][i].rincian}</td>
              </tr>
			  <tr>
              <td><b>Sasaran</b></td>
              <td>:</td>
              <td>${rengiat_link[item][i].sasaran}</td>
              </tr>
              </table>
              </div>
              </div>
              `);
			infowindow.open(map, titik[i]);
		});
	  }
  }