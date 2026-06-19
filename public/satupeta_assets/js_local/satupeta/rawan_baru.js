var rawan_link = []
var rawan_arr = []
var i_rwn = []

function show_rawan(id="",icon='') {
	if ($(`#rawan-${id}`).hasClass("check")) {
		clearMarkerRawan(id);
		$(`#rawan-${id}`).removeClass("check");
		$(`#jml_data_item_rawan_${id}`).remove()
	} else {
		showToast()
		getDataSmNew(global_url_api+`data_collecting/wilayah_rawan?jenis_kerawanan_id=${id}`).then(function (data) {
			if (data.status) {
				rawan_link[id] = data.data;
				i_rwn[id] = []
				create_titik_rawan(id,icon,rawan_link[id]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2 jml_data_rawan" id="jml_data_item_rawan_${id}">
								<span id="jml_data">Jumlah ${data.data[0].kerawanan} <img src="${icon}" class="jml_data_img"> (<span><b id="counter_rawan_${id}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_rawan_${id}`);
						animateValue(obj, 0, rawan_link[id].length, 200)
				})
				
				$(`#rawan-${id}`).addClass("check");
			}else {
				alert('Data Fasilitas Kosong !')
			}
			closeToast()
		});
	}
}

function create_titik_rawan(id, img, arr) {
	return new Promise(function (resolve, reject) {
		  let m_rawan = [];
	  
		  var icon = {
		url: img, // url
		scaledSize: new google.maps.Size(30, 30), // scaled size
	  };
  
	  
	  for (let i = 0; i < rawan_link[id].length; i++) {
		// window.setTimeout(() => {
		  
		  m_rawan.push(
			new google.maps.Marker({
			  position: { lat : parseFloat(rawan_link[id][i].lat), lng : parseFloat(rawan_link[id][i].lng)},
			  map,
			  icon : icon,
			  animation: google.maps.Animation.DROP,
			  polda_id : rawan_link[id][i].polda_id,
              polres_id : rawan_link[id][i].polres_id
			})
		  );
		// }, i * 200);
	  }

		i_rwn[id].push(m_rawan);
		setInfoWindowsRawan(rawan_link[id], i_rwn[id][0], id)

		resolve(rawan_link[id]);
	  })
  }

  function setInfoWindowsRawan(arr,titik,id) {
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
	infowindow.close(); // Close previously opened infowindow
		for (let i = 0; i < arr.length; i++) {
		  google.maps.event.addListener(titik[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(rawan_link[id][i].lat), lng : parseFloat(rawan_link[id][i].lng)});
			  infowindow.setContent(`
			  <div><p><b>TITIK FASILITAS</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Fasilitas</b></td>
			  <td>:</td>
			  <td>${rawan_link[id][i].fasilitas}</td>
			  </tr>
			  <tr>
			  <td><b>Nama Jalan</b></td>
			  <td>:</td>
			  <td>${rawan_link[id][i].nama_jalan_s}</td>
			  </tr>
			  <tr>
			  <td><b>Kordinat</b></td>
			  <td>:</td>
			  <td>${rawan_link[id][i].lat + "," + rawan_link[id][i].lng}</td>
			  </tr>
			  <tr>
			  <td><b>Kapasitas Orang</b></td>
			  <td>:</td>
			  <td>${rawan_link[id][i].kapasitas_orang}</td>
			  </tr>
			  <tr>
			  <td><b>Kapasitas Parkir</b></td>
			  <td>:</td>
			  <td>${rawan_link[id][i].kapasitas_parkir}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
  }

  function clearMarkerRawan(id) {
		if (i_rwn?.[parseInt(id)]) {
			for (let i = 0; i < i_rwn[id][0].length; i++) {
				i_rwn[id][0][i].setMap(null);
			}

		i_rwn[id] = [];
		}
	}

	function clearAllMarkerRawan() {
		i_rwn.forEach(e => {
			$(`.jml_data_rawan`).remove()
			for (let i = 0; i < e[0].length; i++) {
				e[0][i].setMap(null);
			}
		});
	}

function prom_get_kategori_rawan() {
	let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()
	let filter = ''

	if (polda_id != '') {
		filter += `&polda_id=${polda_id}`
	}

	if (polres_id != '') {
		filter += `&polres_id=${polres_id}`
	}

	showToast()
	return new Promise(function (resolve, reject) {
		$.ajax({
			type: "GET",
			url: `${global_url_api}sm_master_data/kerawanan?${filter}`,
			dataType: "json",
			headers: {
				"token": "5b3dac76aaee24d14185cbc3d010fd20"
			  },
			success: function (r) {
				$('#list_kategori_rawan').html('')
				if (r.data.length > 0) {
					r.data.forEach((e) => {
					let icon = e.icon != null ? e.url_icon : './my/satupeta/ambanggangguan1.png'
					$("#list_kategori_rawan").append(`<li class="list-group-item d-flex align-items-center" onclick="show_rawan('${e.rowid}','${icon}')" style="cursor:pointer;">
					  <img class="" width="30" src="${icon}" alt="image">
					  <div class="text-wrap ms-3">
						<span class="mb-0">${e.kerawanan}</span>
					  </div>
					  <div id="rawan-${e.rowid}"></div>
					  </li>`);
					});
				}else {
					$('#list_kategori_rawan').append(`
						<li class="list-group-item d-flex align-items-center">Tidak Ada Data</li>
					`)
				}
				closeToast();
			},
		});
	});
}

function search_rawan() {
	let f_rawan = $('#search-rawan').val().toLowerCase()
	let i_rawan = $('.list_kategori_rawan>.list-group-item')
	for(var i = 0; i<i_rawan.length; i++){
		let span = $('.list-group-item>div>span')[i];
		if(span.innerHTML.toLowerCase().indexOf(f_rawan) > -1){
			i_rawan[i].style.display = "";
		}else{
			i_rawan[i].style = "display:none!important;";
		}
	}
}