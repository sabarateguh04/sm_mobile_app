let nik = "";
let marker_petugas = [];
let pet_arr = [];
let pet_detail = [];
let pet_api = {
	petugas: [],
	route: [],
    nama: [],
};
let video_pet
let url_st_pet
let retrying_pet
let isLoadingPet = false;
let hideErrorMsgPet = true;
let retry_pet
let player_pet
let hls_pet
let selected_petugas;
let infowindow_pet;

// INIT PETUGAS
function initPetugasMap() {
	if (!infowindow_pet) {
			infowindow_pet = new google.maps.InfoWindow();
	}
}

window.addEventListener('load', function() {
	if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
			initPetugasMap();
	}
});

function get_list_petugas(){
	if ($('#cstm-content').hasClass('d-none')) {
		$('#cstm-content').removeClass('d-none')
		$('#cstm-content').append(`
		<div class="accordion">
			<div class="accordion-item">
				<h2 class="accordion-header" id="panelsStayOpen-headingOne">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
					CARI PETUGAS
				</button>
				</h2>
				<div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
				<div class="accordion-body">
					<form action="javascript:void(0);" method="post" id="filter_data_petugas">
					<div class="form-group">
						<label for="" class="">Nama Petugas</label>
						<select name="" id="list_petugas" class="form-control">
						</select>
					</div>
					<div class="d-grid mt-2">
						<button type="submit" class="btn btn-primary">
							<div class="spinner-border d-none loadingPage" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
							<div class="btnSubmitSearch">Cari</div>
						</button>
					</div>
					</form>
				</div>
				</div>
			</div>
		</div>
		`);

		$('#filter_data_petugas').submit(function (x) { 
			$(".loadingPage").removeClass("d-none");
			$(".btnSubmitSearch").addClass("d-none");
			x.preventDefault();
			// deleteMarkers();
			nik = $('#list_petugas').val()
			// removeMarkers(marker_petugas);
			if (nik != 'undefined') {
				selected_petugas = nik
				get_petugas(nik);
			}else {
				selected_petugas = 'undefined';
				clear_petugas()
				$(".loadingPage").addClass("d-none");
				$(".btnSubmitSearch").removeClass("d-none");
			}
		}); 

		let filter = ''
		let da = $('#polda_id').val()
		let res = $('#polres_id').val()
		if (da != '' && da != undefined) {
			filter += `&polda_id=${da}`
		}
		if (res != '' && res != undefined) {
			filter += `&polres_id=${res}`
		}
		getDataSmNew(
			`${global_url_api}sm_master_data/petugas?activity=1${filter}`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			$('#list_petugas').html('');
			$('#list_petugas').append(`<option value="undefined">-- Pilih --</option>`);
            $('#list_petugas').append(`<option ${selected_petugas == '' ? 'selected':''} value="">Tampilkan Semua Petugas</option>`);
			data.data.forEach((m) => {
				$('#list_petugas').append(`
					<option ${selected_petugas == m.nik ? 'selected':''} value="${m.nik}">${m.nama_petugas}</option>
				`)
			});
			
			// if ($('#jml_data_item_petugas').length == 0) {
			// 	$('#jml_data_group').append(`
			// 	<div class="jml_data mt-2" id="jml_data_item_petugas">
			// 		<span id="jml_data">Jumlah Petugas Aktif <img src="./my/satupeta/petugas.png" class="jml_data_img"> (<span><b id="counter_petugas"></b></span>)</span>
			// 	</div>
			// 	`)
			// 	const obj = document.getElementById(`counter_petugas`);
			// 	animateValue(obj, 0, data.data.length, 200)
			// }else {
			// 	$('#jml_data_item_petugas').remove()
			// }
			setTimeout(() => {
				$('#list_petugas').select2()
			}, 1000);
		});
		
		// List Nama Petugas Online Offline
		getDataSmNew(
			`${global_url_api}sm_master_data/petugas?${filter}`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			if ($("#jml_data_item_petugas").length == 0) {
				$("#jml_data_group").append(`
				<div class="jml_data mt-2" id="jml_data_item_petugas">
					<span id="jml_data">Jumlah Petugas <img src="./my/satupeta/petugas.png" class="jml_data_img"> (<span><b id="counter_petugas"></b></span>) <img src="./my/logo/info.png" width="25" role="button" data-bs-toggle="collapse" href="#list_management_route"></span>
					<div id="list_management_route" class="collapse">
						<div class="accordion">
							<div class="m-2">
								<div class="bg-success w-100 fw-semibold text-white fs-6 rounded p-2 mt-4 mb-2 sticky-top">Online <span class="badge text-bg-light" id="total_on"></span></div>
								<ul id="petugasON" class="list-group list-group-flush"></ul>
								<div class="bg-secondary w-100 fw-semibold text-white fs-6 rounded p-2 mt-4 mb-2 sticky-top">Offline <span class="badge text-bg-light" id="total_on"></span></div>
								<ul id="petugasOFF" class="list-group list-group-flush"></ul>
							</div>
						</div>
					</div>
				</div>
				`);
				
				$("#jml_data_group").css("height", "350px");
				let sumPetugasOn = 0;
				let sumPetugasOff = 0;
				data.data.forEach(function (item) {
					if (item.activity == 1) {
						$("#petugasON").append("<li class='list-group-item' style='padding: 2px !important'>" + item.nama_petugas + "</li>");
						sumPetugasOn++;
					} else {
						$("#petugasOFF").append("<li class='list-group-item' style='padding: 2px !important'>" + item.nama_petugas + "</li>");
						sumPetugasOff++;
					}
				});

				$("#total_on").text(sumPetugasOn);
				$("#total_off").text(sumPetugasOff);

				const obj = document.getElementById(`counter_petugas`);
				animateValue(obj, 0, data.data.length, 200);
			} else {
				$("#jml_data_item_petugas").remove();
			}
		});
	}else{
		$('#jml_data_item_petugas').remove()
		$('#cstm-content').html('')
		$('#cstm-content').addClass('d-none')
		$("#jml_data_group").css("height", "");
	}
}

var t_out = null;
var t_int = null;
function clear_petugas(){
    for (var i = 0; i < marker_petugas.length; i++) {
        marker_petugas[i].setMap(null);
    }
    marker_petugas = [];
    pet_arr = [];
    pet_detail = [];
    pet_api = {
        petugas: [],
        route: [],
        nama: [],
    };
    clearInterval(t_int);
    clearTimeout(t_out)
}
function get_petugas(nik) {
    clear_petugas();
    t_out = setTimeout(() => {
       t_int = setInterval(() => {
                if (nik != "") {
                    realtime_petugasNik(nik);
                }else{
                    realtime_petugas()
                }       
        }, 1500);
	}, 1000);
}
function realtime_petugasNik(nik) {
    if (pet_api.petugas.length < 1) {
		getMakeMarkerPetugasNik(nik)
			.then((x) => {
				$(".loadingPage").addClass("d-none");
				$(".btnSubmitSearch").removeClass("d-none");
				x.petugas.forEach((m) => {
					updateMarkerPet(
						m,
						m.position.lat(),
						m.position.lng(),
						m.is_live_streaming,
						m.nama_petugas,
						m.sotk,
						m.nik
					);
				});
			})
			.catch((err) => {});
	} else {
		var i = 0;
		getDataSmNew(
			`${global_url_api}sm_master_data/petugas?nik=${nik}`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			data.data.forEach((m) => {
				updateMarkerPet(
					pet_api.petugas[m.rowid],
					parseFloat(m.lat),
					parseFloat(m.lng),
					m.is_live_streaming,
					m.nama_petugas,
					m.sotk,
					m.nik
				);
                map.setZoom(15);
				map.setCenter({ lat : parseFloat(m.lat), lng : parseFloat(m.lng) });
			});
		});
	}
}

function getMakeMarkerPetugasNik(nik) {
	return new Promise(async function (resolve, reject) {
		const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
		var marker = {};
		getDataSmNew(
			`${global_url_api}sm_master_data/petugas?nik=${nik}`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			data.data.forEach((e) => {
				const symbol = document.createElement('img');
				symbol.src = e.is_live_streaming == '1' ? './my/logo/petugas_live.svg' : './my/satupeta/petugas.png';
				symbol.style.width = '20px';
				symbol.style.height = '20px';

				marker = new AdvancedMarkerElement({
					position: {
						lat : parseFloat(e.lat),
						lng : parseFloat(e.lng)
					},
					map: map,
					content: symbol
				});

				marker_petugas.push(marker);

				// SET INFORMATION PROPERTY MARKER
				marker.petugasInfo = {
						nama_petugas: e.nama_petugas,
						sotk: e.sotk,
						is_live_streaming: e.is_live_streaming,
						nik: e.nik
				};

				// ADD EVENT LISTENER MARKER
				marker.addListener('click', () => {
						const content = `
							<div class="mt-3">
							<span id="live_pet"></span>
							<table class="w-100">
							<tr>
							</tr>
							<tr>
							<td><b>Nama Petugas</b></td>
							<td>:</td>
							<td>${e.nama_petugas}</td>
							</tr>
							<tr>
							<td><b>Kedinasan</b></td>
							<td>:</td>
							<td>${e.sotk}</td>
							</tr>
							</table>
							</div>
							${e.is_live_streaming == '1' ? `
							<div class="mt-2">
								<button onclick="myLiveStream('${e.nik}')"><img class="ml-2" src="./my/satupeta/live.png"></img></button>
							</div>` : ``}
							<div class="row" style="margin-right:0 !important;">
							</div>
							</div>
						`;

						infowindow_pet.setContent(content);
						infowindow_pet.open(map, marker);
				});

				pet_arr.push(marker);
				pet_detail.push(e);
				pet_api.petugas[e.rowid] = marker;
				pet_api.nama[e.rowid] = e.nama_petugas;
			});
			resolve(pet_api);
		});
	});
}

function realtime_petugas() {
	if (pet_api.petugas.length < 1) {
		getMakeMarkerPetugas()
			.then((x) => {
				$(".loadingPage").addClass("d-none");
				$(".btnSubmitSearch").removeClass("d-none");
				x.petugas.forEach((m) => {
					updateMarkerPet(
						pet_api.petugas[m.rowid],
						parseFloat(m.lat),
						parseFloat(m.lng),
						m.is_live_streaming,
						m.nama_petugas,
						m.sotk,
						m.nik
					);
				});
			})
			.catch((err) => {});
	} else {
		let filter = ''
		let da = $('#polda_id').val()
		let res = $('#polres_id').val()
		if (da != '' && da != undefined) {
			filter += `&polda_id=${da}`
		}
		if (res != '' && res != undefined) {
			filter += `&polres_id=${res}`
		}
		var i = 0;
		getDataSmNew(
			`${global_url_api}sm_master_data/petugas?activity=1${filter}`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			data.data.forEach((m,i) => {
				updateMarkerPet(
					pet_api.petugas[m.rowid],
					parseFloat(m.lat),
					parseFloat(m.lng),
					m.is_live_streaming,
					m.nama_petugas,
					m.sotk,
					m.nik
				);
			});
		});
	}
}

function getMakeMarkerPetugas() {
	let filter = ''
	let da = $('#polda_id').val()
	let res = $('#polres_id').val()
	if (da != '' && da != undefined) {
		filter += `&polda_id=${da}`
	}
	if (res != '' && res != undefined) {
		filter += `&polres_id=${res}`
	}
	return new Promise(async function (resolve, reject) {
		const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
		var marker = {};
		getDataSmNew(
			`${global_url_api}sm_master_data/petugas?activity=1${filter}`,
			{},
			"5b3dac76aaee24d14185cbc3d010fd20"
		).then((data) => {
			data.data.forEach((e) => {
				const symbol = document.createElement('img');
				symbol.src = e.is_live_streaming == '1' ? './my/logo/petugas_live.svg' : './my/satupeta/petugas.png';
				symbol.style.width = '20px';
				symbol.style.height = '20px';

				marker = new AdvancedMarkerElement({
					position: {
						lat : parseFloat(e.lat),
						lng : parseFloat(e.lng)
					},
					map: map,
					content: symbol
				});

				marker_petugas.push(marker);

				// SET INFORMATION PROPERTY MARKER
				marker.petugasInfo = {
						nama_petugas: e.nama_petugas,
						sotk: e.sotk,
						is_live_streaming: e.is_live_streaming,
						nik: e.nik
				};

				// SET EVENT LISTENER MARKER
				marker.addListener('click', () => {
					const content = `
					<div class="mt-3">
					<span id="live_pet"></span>
					<table class="w-100">
					<tr>
					</tr>
					<tr>
					<td><b>Nama Petugas</b></td>
					<td>:</td>
					<td>${e.nama_petugas}</td>
					</tr>
					<tr>
					<td><b>Kedinasan</b></td>
					<td>:</td>
					<td>${e.sotk}</td>
					</tr>
					</table>
					</div>
					${e.is_live_streaming == '1' ? `
					<div class="mt-2">
							<button onclick="myLiveStream('${e.nik}')"><img class="ml-2" src="./my/satupeta/live.png"></img></button>
					</div>` : ``}
					<div class="row" style="margin-right:0 !important;">
					</div>
					</div>
					`;

					infowindow_pet.setContent(content);
					infowindow_pet.open(map, marker);
				});

				pet_arr.push(marker);
				pet_detail.push(e);
				pet_api.petugas[e.rowid] = marker;
				pet_api.nama[e.rowid] = e.nama_petugas;
			});
			
			resolve(pet_api);
		});
	});
}

function updateMarkerPet(marker, latitude, longitude, is_live_streaming, nama_petugas, sotk, nik) {
  var newLatLng = new google.maps.LatLng(latitude, longitude);
  if (typeof newLatLng !== 'undefined') {
		// CHECK MARKER INSTANCE ADVANCED MAERKER ELEMENT
		if (marker instanceof google.maps.marker.AdvancedMarkerElement) {
			marker.position = newLatLng;
			// UPDATE ICON
			const symbol = document.createElement('img');
			symbol.src = is_live_streaming == '1' ? './my/logo/petugas_live.svg' : './my/satupeta/petugas.png';
			symbol.style.width = '20px';
			symbol.style.height = '20px';
			marker.content = symbol;

			// SET INFORMATION FOR WINDOW INFO
			marker.petugasInfo = {
					nama_petugas: nama_petugas,
					sotk: sotk,
					is_live_streaming: is_live_streaming,
					nik: nik
			};

			// UPDATE EVENT LISTENER MARKER
			google.maps.event.clearListeners(marker, 'click');
			marker.addListener('click', () => {
					if (!infowindow_pet) {
							initPetugasMap();
					}
					const content = `
					<div class="mt-3">
					<span id="live_pet"></span>
					<table class="w-100">
					<tr>
					</tr>
					<tr>
					<td><b>Nama Petugas</b></td>
					<td>:</td>
					<td>${nama_petugas}</td>
					</tr>
					<tr>
					<td><b>Kedinasan</b></td>
					<td>:</td>
					<td>${sotk}</td>
					</tr>
					</table>
					</div>
					${is_live_streaming == '1' ? `
					<div class="mt-2">
							<button onclick="myLiveStream('${nik}')"><img class="ml-2" src="./my/satupeta/live.png"></img></button>
					</div>` : ``}
					<div class="row" style="margin-right:0 !important;">
					</div>
					</div>
					`;
					if (infowindow_pet) {
						infowindow_pet.setContent(content);
						infowindow_pet.open(map, marker);
					}
			});
		} else {
			// UPDATE MARKER DEFAULT
			marker.setPosition(newLatLng);
			var newIcon = {
					url: is_live_streaming == '1' ? './my/logo/petugas_live.svg' : './my/satupeta/petugas.png',
					scaledSize: new google.maps.Size(20, 20)
			};
			marker.setIcon(newIcon);

			// UPDATE CONTENT FOR WINDOW INFO
			const content = `
			<div class="mt-3">
			<span id="live_pet"></span>
			<table class="w-100">
			<tr>
			</tr>
			<tr>
			<td><b>Nama Petugas</b></td>
			<td>:</td>
			<td>${nama_petugas}</td>
			</tr>
			<tr>
			<td><b>Kedinasan</b></td>
			<td>:</td>
			<td>${sotk}</td>
			</tr>
			</table>
			</div>
			${is_live_streaming == '1' ? `
			<div class="mt-2">
					<button onclick="myLiveStream('${nik}')"><img class="ml-2" src="./my/satupeta/live.png"></img></button>
			</div>` : ``}
			<div class="row" style="margin-right:0 !important;">
			</div>
			</div>
			`;
			marker.content = content;
    }
  }
}

function setInfoWindowPetugas(marker = "", pet_detail = '') {
	if (!infowindow_pet) {
			initPetugasMap(); // Only one InfoWindow
	}

	if (infowindow_pet) {
			infowindow_pet.close(); // Close previously opened infowindow
	}

	return new Promise(function (resolve, reject) {
		for (let i = 0; i < pet_detail.length; i++) {
			google.maps.event.addListener(marker[i], 'click', function(){
				if (!infowindow_pet) {
						initPetugasMap();
				}

				map.setZoom(22);
				map.setCenter({ lat : parseFloat(pet_detail[i].lat), lng : parseFloat(pet_detail[i].lng) });

				if (infowindow_pet) {
					infowindow_pet.setContent(this.content);
					infowindow_pet.open(map, marker[i]);
				}
			});
		}
	});
}

// var markerss
var directionsDisplayL, directionsServiceL, requestL;
var lineHistoryPetugas = []
function history_petugas(rowid) {
	let filter = ''
	let t_s = $('#f_date_from').val()
	let t_e = $('#f_date_to').val()
	if (t_s != '' && t_s != undefined) {
		filter += `&tgl_start=${t_s}`
	}
	if (t_e != '' && t_e != undefined) {
		filter += `&tgl_end=${t_e}`
	}
	clear_route_history_petugas()
	getDataSmNew(global_url_api+`sm_master_data/petugas/log_petugas/${rowid}?${filter}`).then(r => {

		const flightPlanCoordinates = [];

		for (let i = 0; i < r.data.length; i++) {
			flightPlanCoordinates.push({lat : parseFloat(r.data[i].lat), lng : parseFloat(r.data[i].lng)})
		}

		  const flightPath = new google.maps.Polyline({
			path: flightPlanCoordinates,
			geodesic: true,
			strokeColor: "#FF0000",
			strokeOpacity: 1.0,
			strokeWeight: 2,
		  });
		
		  lineHistoryPetugas.push(flightPath)
		  flightPath.setMap(map);
	})
}

function clear_route_history_petugas() {
	if (lineHistoryPetugas.length > 0) {
		for (let i = 0; i < lineHistoryPetugas.length; i++) {
			lineHistoryPetugas[i].setMap(null)
		}
	}
}

function myLiveStream(nik = "") {
	let x = $('#live_pet')
	if ($("#live_pet").hasClass("checks")) {
        $('#live_pet').addClass('d-none');
        $("#live_pet").removeClass("checks");
		x.html('')
      } else {
		x.append(`
		<div style="width: 27rem; height:15rem;">
		<video
			id="my-video-pet"
			class="video-js w-100 border border-warning rounded"
			controls
			preload="auto"
			width="640"
			height="230"
			data-setup="{}"
		>
			<source id='url_stream_pet' src="https://stream.estrada.co.id:2234/live/${nik}/index.m3u8" type="application/x-mpegURL" />
			<p class="vjs-no-js">
			To view this video please enable JavaScript, and consider upgrading to a
			web browser that
			<a href="https://videojs.com/html5-video-support/" target="_blank"
				>supports HTML5 video</a
			>
			</p>
		</video>
		</div>
		`);
        $('#live_pet').removeClass('d-none');
		$("#live_pet").addClass("checks");
		video_pet = document.getElementById('my-video-pet');
		url_st_pet = document.getElementById('url_stream_pet').src;
		if (player_pet != undefined) {
			retrying_pet = false
			clearInterval(retry_pet)
			player_pet.reset()
			player_pet.dispose()
			hls_pet.destroy()
		}
		play_pet()
      }
  }

  function play_pet() {
	if(Hls.isSupported()) {
		player_pet = videojs('my-video-pet');
  
		player_pet.ready(function() {
		var promise_pet = player_pet.autoplay('muted');
  
		if (promise_pet !== undefined) {
			promise_pet.then(function() {
			// Autoplay started!
			console.log('start');
			}).catch(function(error) {
			// Autoplay was prevented.
			console.log('error');
			});
		}
		});
		  const config = {
			  autoStartLoad: true,
			  maxBufferSize: 1 * 1000 * 1000,
			  manifestLoadingMaxRetry: 300000,
			  manifestLoadingMaxRetryTimeout: 1000,
			  levelLoadingMaxRetry: 300000,
			  levelLoadingMaxRetryTimeout: 1000,
			  fragLoadingMaxRetry: 300000,
			  fragLoadingMaxRetryTimeout: 1000
		  }
		  
		  hls_pet = new Hls(config);
		  
		  retrying_pet = false;
		  retry_pet = setInterval(() => retryLiveStreamPet(hls_pet, video_pet), 1000);
		  
		  video_pet.onplaying = () => {
			  isLoadingPet = false;
			  hideErrorMsgPet = true;
			  clearInterval(retry_pet);
			  retrying_pet = false;
		  }
		  
		  hls_pet.loadSource(url_st_pet);
		  hls_pet.attachMedia(video_pet);
		  hls_pet.on(Hls.Events.MANIFEST_PARSED,function() {
			  video_pet.play();
		  });
	  
		  hls_pet.on(Hls.Events.ERROR, function (event, data) {
			  if (!hideErrorMsgPet) {
				  isLoadingPet = true;
				  hideErrorMsgPet = true;
			  }
			  if (data.fatal) {
				switch(data.type) {
				case Hls.ErrorTypes.NETWORK_ERROR:
				  console.log("fatal network error encountered, try to recover");
				  if (!retrying_pet) {
					  retry_pet;
				  }
				  break;
				case Hls.ErrorTypes.MEDIA_ERROR:
				  console.log("fatal media error encountered, try to recover");
				  hls_pet.recoverMediaError();
				  break;
				}
			  }
		  });
	  }
  }
	
  function retryLiveStreamPet(hls_pet, video_pet) {
	  retrying_pet = true;
	  hls_pet.loadSource(document.getElementById('url_stream_pet').src);
	  hls_pet.attachMedia(video_pet);
	  hls_pet.startLoad();
  }
