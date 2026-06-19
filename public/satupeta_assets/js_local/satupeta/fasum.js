var fasum_link = []
var fasum_arr = []
var i_fsm = []

function show_fasum(id="",icon='') {
	let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()
	let filter = ''

	if (polda_id != '') {
		filter += `&polda_id=${polda_id}`
	}

	if (polres_id != '') {
		filter += `&polres_id=${polres_id}`
	}
	if ($(`#fasum-${id}`).hasClass("check")) {
		clearMarkerFasum(id);
		$(`#fasum-${id}`).removeClass("check");
		$(`#jml_data_item_fasum_${id}`).remove()
	} else {
		showToast()
		getDataSmNew(global_url_api+`data_collecting/fasilitas_umum?fasilitas_id=${id}${filter}`).then(function (data) {
			if (data.status) {
				fasum_link[id] = data.data;
				i_fsm[id] = []
				create_titik_fasum(id,icon,fasum_link[id]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2 jml_data_fasum" id="jml_data_item_fasum_${id}">
								<span id="jml_data">Jumlah ${data.data[0].fasilitas} <img src="${icon}" class="jml_data_img"> (<span><b id="counter_fasum_${id}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_fasum_${id}`);
						animateValue(obj, 0, fasum_link[id].length, 200)
				})
				
				$(`#fasum-${id}`).addClass("check");
			}else {
				alert('Data Fasilitas Kosong !')
			}
			closeToast()
		});
	}
}

function create_titik_fasum(id, img, arr) {
	return new Promise(function (resolve, reject) {
		  let m_fasum = [];
	  
		  var icon = {
		url: img, // url
		scaledSize: new google.maps.Size(30, 30), // scaled size
	  };
  
	  
	  for (let i = 0; i < fasum_link[id].length; i++) {
		// window.setTimeout(() => {
		  
		  m_fasum.push(
			new google.maps.Marker({
			  position: { lat : parseFloat(fasum_link[id][i].lat), lng : parseFloat(fasum_link[id][i].lng)},
			  map,
			  icon : icon,
			  animation: google.maps.Animation.DROP,
			  polda_id : fasum_link[id][i].polda_id,
              polres_id : fasum_link[id][i].polres_id
			})
		  );
		// }, i * 200);
	  }

		i_fsm[id].push(m_fasum);
		setInfoWindowsFasum(fasum_link[id], i_fsm[id][0], id)

		resolve(fasum_link[id]);
	  })
  }

  function setInfoWindowsFasum(arr,titik,id) {
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
	infowindow.close(); // Close previously opened infowindow
		for (let i = 0; i < arr.length; i++) {
		  google.maps.event.addListener(titik[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(fasum_link[id][i].lat), lng : parseFloat(fasum_link[id][i].lng)});
			  infowindow.setContent(`
			  <div><p><b>TITIK FASILITAS</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Fasilitas</b></td>
			  <td>:</td>
			  <td>${fasum_link[id][i].fasilitas}</td>
			  </tr>
			  <tr>
			  <td><b>Nama Jalan</b></td>
			  <td>:</td>
			  <td>${fasum_link[id][i].nama_jalan_s}</td>
			  </tr>
			  <tr>
			  <td><b>Kordinat</b></td>
			  <td>:</td>
			  <td>${fasum_link[id][i].lat + "," + fasum_link[id][i].lng}</td>
			  </tr>
			  <tr>
			  <td><b>Kapasitas Orang</b></td>
			  <td>:</td>
			  <td>${fasum_link[id][i].kapasitas_orang}</td>
			  </tr>
			  <tr>
			  <td><b>Kapasitas Parkir</b></td>
			  <td>:</td>
			  <td>${fasum_link[id][i].kapasitas_parkir}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
  }

  function clearMarkerFasum(id) {
		if (i_fsm?.[parseInt(id)]) {
			for (let i = 0; i < i_fsm[id][0].length; i++) {
				i_fsm[id][0][i].setMap(null);
			}

		i_fsm[id] = [];
		}
	}

	function clearAllMarkerFasum() {
		i_fsm.forEach(e => {
			$(`.jml_data_fasum`).remove()
			for (let i = 0; i < e[0].length; i++) {
				e[0][i].setMap(null);
			}
		});
	}


function prom_get_kategori_fasum() {
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
	getRToken().then(async r=>{
		if (r.status != true) {
            window.location.href = `${global_url_ci}Auth/logout`
        }else {
			return new Promise(function (resolve, reject) {
				$.ajax({
					type: "GET",
					url: `${global_url_api}sm_master_data/fasilitas?${filter}`,
					dataType: "json",
					headers: {
						"token": "5b3dac76aaee24d14185cbc3d010fd20",
						"secondbearer" : r.data.second_bearer
					  },
					success: function (r) {
						$('#list_kategori_fasum').html('')
						if (r.data.length > 0) {
							r.data.forEach((e) => {
							let icon = e.icon != null ? e.url_icon : './my/satupeta/fasum.png'
							$("#list_kategori_fasum").append(`<li class="list-group-item d-flex align-items-center" onclick="show_fasum('${e.rowid}','${icon}')" style="cursor:pointer;">
							  <img class="" width="30" src="${icon}" alt="image">
							  <div class="text-wrap ms-3">
								<span class="mb-0">${e.fasilitas}</span>
							  </div>
							  <div id="fasum-${e.rowid}"></div>
							  </li>`);
							});
						}else {
							$('#list_kategori_fasum').append(`
								<li class="list-group-item d-flex align-items-center">Tidak Ada Data</li>
							`)
						}
						closeToast();
					},
				});
			});
		}
	})
}

function search_fasum() {
	let f_fasum = $('#search-fasum').val().toLowerCase()
	let i_fasum = $('.list_kategori_fasum>.list-group-item')
	for(var i = 0; i<i_fasum.length; i++){
		let span = $('.list-group-item>div>span')[i];
		if(span.innerHTML.toLowerCase().indexOf(f_fasum) > -1){
			i_fasum[i].style.display = "";
		}else{
			i_fasum[i].style = "display:none!important;";
		}
	}
}