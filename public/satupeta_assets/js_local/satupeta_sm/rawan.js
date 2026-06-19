
var rawan_link = []
var rawan_arr = []
var i_rwn = []
let ctg_rawan = []

function get_list_rawan(){
	if ($('#cstm-content').hasClass('d-none')) {
		$('#cstm-content').removeClass('d-none')
		$('#cstm-content').append(`
		<div class="accordion">
			<div class="accordion-item">
				<h2 class="accordion-header" id="panelsStayOpen-headingOne">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
					RAWAN
				</button>
				</h2>
				<div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
				<div class="accordion-body">
					<div>
						<input type="text" class="form-control" placeholder="Cari..." id="search-rawan" onkeyup="search_rawan()">
					</div>
					<ul class="list-group list_kategori_rawan mt-2" id="list_kategori_rawan" style="height:200px!important; overflow:auto;">
					</ul>
				</div>
				</div>
			</div>
		</div>
		`)
		prom_get_kategori_rawan()
	}else{
		$('#cstm-content').html('')
		$('#cstm-content').addClass('d-none')
	}
	
}

function show_rawan(id="",img='') {
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
	
	if ($(`#rawan-${id}`).hasClass("check")) {
		clearMarkerRawan(id);
		const index = ctg_rawan.indexOf(id);
		if (index > -1) {
			ctg_rawan.splice(index, 1);
		}
		$(`#rawan-${id}`).removeClass("check");
		$(`#jml_data_item_rawan_${id}`).remove()
	} else {
		showToast()
		getDataSmNew(global_url_api+`data_collecting/wilayah_rawan?jenis_kerawanan_id=${id}${filter}`).then(function (data) {
			if (data.status) {
				rawan_link[id] = data.data;
				i_rwn[id] = []
				create_titik_rawan(id,img,rawan_link[id]).then(e => {
					$('#jml_data_group').append(`
						<div class="jml_data mt-2 jml_data_rawan" id="jml_data_item_rawan_${id}">
								<span id="jml_data">Jumlah ${data.data[0].kerawanan} <img src="${img}" class="jml_data_img"> (<span><b id="counter_rawan_${id}"></b></span>)</span>
						</div>
					`)
					const obj = document.getElementById(`counter_rawan_${id}`);
						animateValue(obj, 0, rawan_link[id].length, 200)
				})
				ctg_rawan.push(id)
				$(`#rawan-${id}`).addClass("check");
			}else {
				swal(
					'Warning !',
					'Data Tidak Ditemukan !',
					'warning'
				);
			}
			closeToast()
		});
	}
}

// SET INTERSECTION OBSERVER
intersectionObserver();

function create_titik_rawan(id, img, arr) {
	return new Promise(async function (resolve, reject) {
		const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

		// GET DROP ANIMATION CSS
		initMarkerDropAnimationCSS();

		let m_rawan = [];

		for (let i = 0; i < rawan_link[id].length; i++) {
			const image = document.createElement('img');
			image.src = img;
			image.style.width = '30px';
			image.style.height = '30px';

			const content = image;
			content.style.setProperty('--delay-time', `${Math.random() + 0.5}s`);
			content.style.opacity = '0';

			const marker = new AdvancedMarkerElement({
				position: {
					lat: parseFloat(rawan_link[id][i].lat),
					lng: parseFloat(rawan_link[id][i].lng)
				},
				map: map,
				content: content
			});

			content.addEventListener('animationend', () => {
				content.classList.remove('marker-animation');
				content.style.opacity = '1';
			});

			// ANIMATION OF THE PATH AS IT DROPS
			intersectionObserver.observe(content);

			m_rawan.push(marker);
		}

		i_rwn[id].push(m_rawan);
		setInfoWindowsRawan(rawan_link[id], i_rwn[id][0], id);

		resolve(rawan_link[id]);
	});
}

function setInfoWindowsRawan(arr,titik,id) {
const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
infowindow.close(); // Close previously opened infowindow
	for (let i = 0; i < arr.length; i++) {
		google.maps.event.addListener(titik[i], 'click', function(){
			map.setZoom(15);
			map.setCenter({ lat : parseFloat(rawan_link[id][i].lat), lng : parseFloat(rawan_link[id][i].lng)});
			if (id == 5) {
				infowindow.setContent(`
				<div><p><b>TITIK RAWAN</b></p>
				<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
				<table class="w-100">
				<tr>
				<td><b>Kerawanan</b></td>
				<td>:</td>
				<td>${rawan_link[id][i].kerawanan}</td>
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
				<td><b>Jenis Bencana</b></td>
				<td>:</td>
				<td>${rawan_link[id][i].jenis_bencana}</td>
				</tr>
				<tr>
				<td><b>Kategori Bencana</b></td>
				<td>:</td>
				<td>${rawan_link[id][i].kategori_bencana}</td>
				</tr>
				<tr>
				<td><b>Penyebab Kerawanan</b></td>
				<td>:</td>
				<td>${rawan_link[id][i].penyebab_kerawanan}</td>
				</tr>
				<tr>
				<td><b>Potensi Terdampak</b></td>
				<td>:</td>
				<td>${rawan_link[id][i].potensi_terdampak}</td>
				</tr>
				</table>
				</div>
				`);
			} else {
				infowindow.setContent(`
				<div><p><b>TITIK RAWAN</b></p>
				<hr style="margin-top:0 !important;margin-bottom:1rem !important;">
				<table class="w-100">
				<tr>
				<td><b>Kerawanan</b></td>
				<td>:</td>
				<td>${rawan_link[id][i].kerawanan}</td>
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
				<td><b>Penyebab Kerawanan</b></td>
				<td>:</td>
				<td>${rawan_link[id][i].penyebab_kerawanan}</td>
				</tr>
				</table>
				</div>
				`);
			}
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
	// getRToken().then(async r=>{
	// 	if (r.status != true) {
    //         window.location.href = `${global_url_ci}Auth/logout`
    //     }else {
			return new Promise(function (resolve, reject) {
				$.ajax({
					type: "GET",
					url: `${global_url_api}sm_master_data/kerawanan?${filter}`,
					dataType: "json",
					headers: {
						// "second_bearer" : r.data.second_bearer
						"secondbearer" : `${global_sbearer}`
					  },
					success: function (r) {
						$('#list_kategori_rawan').html('')
						if (r.data.length > 0) {
							r.data.forEach((e) => {
								if (e.kerawanan != 'RAWAN MACET') {
									let icon = e.icon != null ? e.url_icon : './my/logo/menu_rawan.png'
									$("#list_kategori_rawan").append(`<li class="list-group-item d-flex align-items-center list-group-item-action" onclick="show_rawan('${e.rowid}','${icon}')" style="cursor:pointer;">
									  <img class="" width="30" src="${icon}" alt="image">
									  <div class="text-wrap ms-3">
										<span class="mb-0">${e.kerawanan}</span>
									  </div>
									  <div class="${ctg_rawan.includes(`${e.rowid}`) ? 'check' : ''}" id="rawan-${e.rowid}"></div>
									  </li>`);
								}
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
	// 	}
	// })
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
