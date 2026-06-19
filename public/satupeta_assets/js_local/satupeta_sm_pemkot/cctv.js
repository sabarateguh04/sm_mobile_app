var cctv_link_dum = [];
var cctv_arr_dum = [];
var cctv_arr_dum_temp = {};
var list_arr_cctv = {};
var list_arr_cctv_sub = {};

var g_rtsp = [];
var s_rtsp;
var popover;

let video;
let url_st;

let t_out_gddtl = null;
let t_out_gddas = null;
let t_out_gddre = null;
let t_out_gddtcka = null;
let t_out_gddtcki = null;
let t_out_gddpp = null;
let t_out_gddpw = null;
let t_out_cctv = null;
let t_out_sts_cctv = null;

function get_list_cctv() {
	if ($("#cbc").hasClass("check-in-peta")) {
		prom_clearMarkerCctvDum();
		$("#list_cctv").html("");
		$("#legend-cctv").addClass("d-none");
		$("#legend-cctv-2").addClass("d-none");
		$("#cbc").removeClass("check-in-peta");
	} else {
		$("#cbc").addClass("check-in-peta");
		$("#legend-cctv").removeClass("d-none");
		$("#legend-cctv-2").removeClass("d-none");
		$("#btn-cbc").prop("disabled", true);
		set_all_cctv(
			usr_polda_id != "" ? usr_polda_id : "",
			usr_polres_id != "" ? usr_polres_id : ""
		);

		setTimeout(() => {
			jalankan_live_marker_cctv();
			$("#btn-cbc").prop("disabled", false);
		}, 2000);
	}
}

// setTimeout(() => {
//   set_all_cctv( usr_polda_id != '' ? usr_polda_id : '', usr_polres_id != '' ? usr_polres_id : '')
// }, 1000);

// setTimeout(() => {
//   jalankan_live_status_cctv()
//   jalankan_live_marker_cctv()
// }, 3000);

function jalankan_live_marker_cctv() {
	live_marker_cctv().then(function (data) {
		var newIcon = {
			url: "", // Lokasi file ikon baru
			scaledSize: new google.maps.Size(30, 30), // Ukuran ikon baru
		};
		$("#list_toast").html("");
		data.forEach((v, i) => {
			if (v.situasi_lalin == "LANCAR") {
				if (v.category.replace(/ /g, "_").toLowerCase() == "objek_vital") {
					newIcon.url = `${global_url_ci}my/logo/green_cctv_objek_vital.gif`;
				}

				if (v.category.replace(/ /g, "_").toLowerCase() == "batas_provinsi") {
					newIcon.url = `${global_url_ci}my/logo/green_cctv_batas_provinsi.gif`;
				}

				if (v.category.replace(/ /g, "_").toLowerCase() == "batas_kota") {
					newIcon.url = `${global_url_ci}my/logo/green_cctv_batas_kota.gif`;
				}

				if (
					v.category.replace(/ /g, "_").toLowerCase() == "titik_rawan_macet"
				) {
					newIcon.url = `${global_url_ci}my/logo/green_cctv_rawan_macet.gif`;
				}

				if (v.category.replace(/ /g, "_").toLowerCase() == "dalam_kota") {
					newIcon.url = `${global_url_ci}/my/logo/green_cctv_dalam_kota.gif`;
				}
				// newIcon.url ='./my/logo/alert-green.gif`  // Lokasi file ikon baru
			}
			if (v.situasi_lalin == "PADAT") {
				if (v.category.replace(/ /g, "_").toLowerCase() == "objek_vital") {
					newIcon.url = `${global_url_ci}my/logo/yellow_cctv_objek_vital.gif`;
				}

				if (v.category.replace(/ /g, "_").toLowerCase() == "batas_provinsi") {
					newIcon.url = `${global_url_ci}my/logo/yellow_cctv_batas_provinsi.gif`;
				}

				if (v.category.replace(/ /g, "_").toLowerCase() == "batas_kota") {
					newIcon.url = `${global_url_ci}my/logo/yellow_cctv_batas_kota.gif`;
				}

				if (
					v.category.replace(/ /g, "_").toLowerCase() == "titik_rawan_macet"
				) {
					newIcon.url = `${global_url_ci}my/logo/yellow_cctv_rawan_macet.gif`;
				}

				if (v.category.replace(/ /g, "_").toLowerCase() == "dalam_kota") {
					newIcon.url = `${global_url_ci}/my/logo/yellow_cctv_dalam_kota.gif`;
				}
				// newIcon.url ='./my/logo/alert-yellow.gif'  // Lokasi file ikon baru
			}
			if (v.situasi_lalin == "MACET") {
				$("#list_toast").append(`
        <div id='liveToast${i}' class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              ${v.channel_name} ${v.situasi_lalin}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div> `);
				const toastLiveExample = document.getElementById("liveToast" + i);
				const toast = new bootstrap.Toast(toastLiveExample);

				toast.show();
				if (v.category.replace(/ /g, "_").toLowerCase() == "objek_vital") {
					newIcon.url = `${global_url_ci}my/logo/red_cctv_objek_vital.gif`;
				}

				if (v.category.replace(/ /g, "_").toLowerCase() == "batas_provinsi") {
					newIcon.url = `${global_url_ci}my/logo/red_cctv_batas_provinsi.gif`;
				}

				if (v.category.replace(/ /g, "_").toLowerCase() == "batas_kota") {
					newIcon.url = `${global_url_ci}my/logo/red_cctv_batas_kota.gif`;
				}

				if (
					v.category.replace(/ /g, "_").toLowerCase() == "titik_rawan_macet"
				) {
					newIcon.url = `${global_url_ci}my/logo/red_cctv_rawan_macet.gif`;
				}

				if (v.category.replace(/ /g, "_").toLowerCase() == "dalam_kota") {
					newIcon.url = `${global_url_ci}my/logo/red_cctv_dalam_kota.gif`;
				}
				// newIcon.url ='./my/logo/alert-red.gif'  // Lokasi file ikon baru
			}

			cctv_arr_dum[v.cctv_id].setIcon(newIcon);
		});
	});
	t_out_cctv = setTimeout(() => {
		jalankan_live_marker_cctv();
	}, 60000);
}

function live_marker_cctv(pd_id = "", ps_id = "") {
	let polda_id = $("#polda_id").val();
	let polres_id = $("#polres_id").val();
	let filter = "";

	if (polda_id != "") {
		filter += `&polda_id=${polda_id}`;
	} else if (pd_id != "") {
		filter += `&polda_id=${pd_id}`;
	}

	if (polres_id != "") {
		filter += `&polres_id=${polres_id}`;
	} else if (ps_id != "") {
		filter += `&polres_id=${ps_id}`;
	}
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	let stamp_start = today.setTime(today.getTime() - 5 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_live_counting?${filter}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				resolve(data.data);
			} else {
				resolve([]);
			}
			// closeToast()
		});
	});
}

function jalankan_live_status_cctv() {
	live_status_cctv().then(function (data) {
		var newIcon = {
			url: "", // Lokasi file ikon baru
			scaledSize: new google.maps.Size(30, 30), // Ukuran ikon baru
		};
		$("#list_toast").html("");
		data.forEach((v, i) => {
			if (v.status == "mati") {
				newIcon.url = `${global_url_ci}my/logo/cctv_mati.png`;
			} else {
				if (v.category.replace(/ /g, "_").toLowerCase() == "objek_vital") {
					newIcon.url = `${global_url_ci}my/logo/cctv_objek_vital.png`;
				} else if (
					v.category.replace(/ /g, "_").toLowerCase() == "batas_provinsi"
				) {
					newIcon.url = `${global_url_ci}my/logo/cctv_batas_provinsi.png`;
				} else if (
					v.category.replace(/ /g, "_").toLowerCase() == "batas_kota"
				) {
					newIcon.url = `${global_url_ci}my/logo/cctv_batas_kota.png`;
				} else if (
					v.category.replace(/ /g, "_").toLowerCase() == "titik_rawan_macet"
				) {
					newIcon.url = `${global_url_ci}my/logo/cctv_rawan_macet.png`;
				} else if (
					v.category.replace(/ /g, "_").toLowerCase() == "dalam_kota"
				) {
					newIcon.url = `${global_url_ci}my/logo/cctv_dalam_kota.png`;
				} else {
					newIcon.url = `${global_url_ci}my/logo/cctv.png`;
				}
			}
			cctv_arr_dum[v.rowid].setIcon(newIcon);
		});
	});
	t_out_sts_cctv = setTimeout(() => {
		jalankan_live_status_cctv();
	}, 60000);
}

function live_status_cctv(pd_id = "", ps_id = "") {
	let polda_id = $("#polda_id").val();
	let polres_id = $("#polres_id").val();
	let filter = "";

	if (polda_id != "") {
		filter += `&polda_id=${polda_id}`;
	} else if (pd_id != "") {
		filter += `&polda_id=${pd_id}`;
	}

	if (polres_id != "") {
		filter += `&polres_id=${polres_id}`;
	} else if (ps_id != "") {
		filter += `&polres_id=${ps_id}`;
	}
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}patroli_cctv/cctv?${filter}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				resolve(data.data);
			} else {
				resolve([]);
			}
			// closeToast()
		});
	});
}

function set_all_cctv(
	pd_id = "",
	ps_id = "",
	category = "",
	cat_dash = "",
	cat_content_cctv = ""
) {
	// cctv_link_dum = [];
	prom_get_cctv_dummy(pd_id, ps_id, category).then(function (data) {
		$("#jml_cctv_legend").html("");
		$("#jml_cctv_legend_batas_kota").html("");
		$("#jml_cctv_legend_batas_provinsi").html("");
		$("#jml_cctv_legend_objek_vital").html("");
		$("#jml_cctv_legend_dalam_kota").html("");

		$("#jml_cctv_legend").append(`( ${data.length} )`);
		$("#jml_cctv_legend_batas_kota").append(
			`( ${
				data.filter((item) => item.category === "BATAS KOTA" && item).length
			} )`
		);
		$("#jml_cctv_legend_objek_vital").append(
			`( ${
				data.filter((item) => item.category === "OBJEK VITAL" && item).length
			} )`
		);
		$("#jml_cctv_legend_dalam_kota").append(
			`( ${
				data.filter((item) => item.category === "DALAM KOTA" && item).length
			} )`
		);
		if (paramsPolres == null) {
			$("#jml_cctv_legend_batas_provinsi").append(
				`( ${
					data.filter((item) => item.category === "BATAS PROVINSI" && item)
						.length
				} )`
			);
		}
		var icn = [];
		for (let i = 0; i < data.length; i++) {
			if (data[i].status == "mati") {
				icn.push(`${global_url_ci}/my/logo/cctv_mati.png`);
			} else {
				if (data[i].category != null) {
					if (
						data[i].category.replace(/ /g, "_").toLowerCase() == "objek_vital"
					) {
						icn.push(`${global_url_ci}/my/logo/cctv_objek_vital.png`);
					} else if (
						data[i].category.replace(/ /g, "_").toLowerCase() ==
						"batas_provinsi"
					) {
						icn.push(`${global_url_ci}/my/logo/cctv_batas_provinsi.png`);
					} else if (
						data[i].category.replace(/ /g, "_").toLowerCase() == "batas_kota"
					) {
						icn.push(`${global_url_ci}/my/logo/cctv_batas_kota.png`);
					} else if (
						data[i].category.replace(/ /g, "_").toLowerCase() ==
						"titik_rawan_macet"
					) {
						icn.push(`${global_url_ci}/my/logo/cctv_rawan_macet.png`);
					} else if (
						data[i].category.replace(/ /g, "_").toLowerCase() == "dalam_kota"
					) {
						icn.push(`${global_url_ci}/my/logo/cctv_dalam_kota.png`);
					} else {
						icn.push(`${global_url_ci}/my/logo/cctv.png`);
					}
				} else {
					icn.push(`${global_url_ci}/my/logo/cctv.png`);
				}
			}
		}

		list_arr_cctv = {};
		prom_clearMarkerCctvDum().then(function (d) {
			data.forEach((item) => {
				if (!list_arr_cctv[item.category]) {
					list_arr_cctv[item.category] = [];
				}

				list_arr_cctv[item.category].push(item);
			});
			list_cctv_pemkot(list_arr_cctv);
			list_card_cctv(list_arr_cctv);
			create_cctv_map_dummy(data, "", icn, cat_dash, cat_content_cctv);
		});
	});
}

function prom_get_cctv_dummy(pd_id = "", ps_id = "", category = "") {
	let filter = "";
	// HANDLE PARAMS POLDA
	if (paramsPoldaId != null) {
		filter += `polda_id=${paramsPoldaId}`;
	} else if (pd_id != "") {
		filter += `polda_id=${pd_id}`;
	}

	// HANDLE PARAMS POLRES
	if (paramsPolresId) {
		filter += `${filter ? "&" : ""}polres_id=${paramsPolresId}`;
	} else if (ps_id) {
		filter += `${filter ? "&" : ""}polres_id=${ps_id}`;
	} else if ($("#f-polres").val() && $("#f-polres").val() !== "") {
		let polresId = $("#f-polres").val().split(",")[1];
		filter += `${filter ? "&" : ""}polres_id=${polresId}`;
	}

	// HANDLE PARAMS CATEGORY
	if (category != "") {
		filter += `${filter ? "&" : ""}cctv_category_id=${category}`;
	}

	showToast();
	return new Promise(function (resolve, reject) {
		// CLEAR EXIST MARKER
		if (cctv_arr_dum) {
			for (let marker of Object.values(cctv_arr_dum)) {
				if (marker.setMap) {
					marker.setMap(null);
				}
			}
		}
		cctv_arr_dum = [];
		cctv_link_dum = [];

		$.ajax({
			type: "GET",
			url: `${global_url_api}patroli_cctv/cctv?${filter}`,
			dataType: "json",
			headers: {
				token: "5b3dac76aaee24d14185cbc3d010fd20",
				secondbearer: `${global_sbearer}`,
			},
			success: function (r) {
				cctv_link_dum = [];
				r.data.forEach((o) => {
					cctv_link_dum[o.rowid] = o;
				});

				closeToast();
				resolve(r.data);
			},
			error: function (e) {
				prom_clearMarkerCctvDum();
				if (e.status == 0) {
					swal("Error !", "Koneksi server error", "error");
				} else if (e.status == 400) {
					resolve([]);
				}
				closeToast();
			},
		});
	});
}

function prom_clearMarkerCctvDum() {
	return new Promise(function (resolve, reject) {
		if (cctv_arr_dum) {
			list_arr_cctv = {};
			clearTimeout(t_out_gddtl);
			clearTimeout(t_out_gddas);
			clearTimeout(t_out_gddre);
			clearTimeout(t_out_gddtcka);
			clearTimeout(t_out_gddtcki);
			clearTimeout(t_out_gddpp);
			clearTimeout(t_out_gddpw);
			clearTimeout(t_out_cctv);
			if (popover != undefined) {
				popover.hide();
			}
			cctv_arr_dum.forEach((v, i) => {
				cctv_arr_dum[i].setMap(null);
			});
			resolve(cctv_arr_dum);
		}
	});
}

function create_cctv_map_dummy(arrx, item, icon, cat_dash, cat_content_cctv) {
	prom_create_cctv_map_dummy(arrx, item, icon).then(function (data) {
		setInfoWindowsCCTVDUM(item, cat_dash, cat_content_cctv);
	});
}
function prom_create_cctv_map_dummy(arrx, item, icn) {
	return new Promise(async function (resolve, reject) {
		const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

		// CLEAR EXIST MARKER
		if (cctv_arr_dum) {
			for (let marker of Object.values(cctv_arr_dum)) {
				if (marker.setMap) {
					marker.setMap(null);
				}
			}
		}

		cctv_arr_dum = [];
		let arr = arrx || cctv_link_dum;

		for (let i = 0; i < arr.length; i++) {
			const img = document.createElement("img");
			img.src = icn[i];
			img.style.width = "30px";
			img.style.height = "30px";

			const marker = new AdvancedMarkerElement({
				position: { lat: parseFloat(arr[i].lat), lng: parseFloat(arr[i].lng) },
				map,
				content: img,
				gmpClickable: true,
			});

			cctv_arr_dum[arr[i].rowid] = marker;
		}

		resolve(cctv_arr_dum);
	});
}

var infowindowCctv;
let streamCTV
async function setInfoWindowsCCTVDUM(
	item2 = "",
	cat_dash = "",
	cat_content_cctv = ""
) {
	infowindowCctv = new google.maps.InfoWindow(); // Only one InfoWindow
	cctv_arr_dum.forEach((v, i) => {
		cctv_arr_dum[i].addListener("mouseover", function () {
			if (popover != undefined) {
				popover.hide();
			}
			if (cctv_link_dum[i].category == "OBJEK VITAL") {
				showInfoWindow(
					cctv_arr_dum[i],
					`
                <div style='font-size: 10.5px'>
                  <div class='fs-6 fw-bold lh-2'>${cctv_link_dum[i].nama_cctv} - ${cctv_link_dum[i].polda}<br>
                    <div class='fw-light lh-2' id='cctv_hover_people'>KEPADATAN MASYARAKAT </div>
                    <div class='fw-light' id='time_hover_people'></div>
                  </div>
                </div>`
				);
				setTimeout(() => {
					get_data_dahua_people_hover(cctv_link_dum[i].channel_id);
				}, 200);
			} else {
				showInfoWindow(
					cctv_arr_dum[i],
					`
                <div style='font-size: 10.5px'>
                  <div class='fs-6 fw-bold lh-2'>${cctv_link_dum[i].nama_cctv} - ${cctv_link_dum[i].polda}<br>
                    <div class='fw-light lh-2' id='cctv_hover'>STATUS LALIN </div>
                    <div class='fw-light' id='time_hover'></div>
                  </div>
                </div>`
				);
				setTimeout(() => {
					get_data_dahua_traffic_lalin_hover(cctv_link_dum[i].channel_id);
				}, 200);
			}
		});

		cctv_arr_dum[i].addListener("mouseout", function () {
			if (!cctv_arr_dum[i].clicked) {
				infowindowCctv.close();
			}
		});

		cctv_arr_dum[i].addEventListener("gmp-click", async function () {
			clearTimeout(t_out_gddtl);
			clearTimeout(t_out_gddas);
			clearTimeout(t_out_gddre);
			clearTimeout(t_out_gddtcka);
			clearTimeout(t_out_gddtcki);
			clearTimeout(t_out_gddpp);
			clearTimeout(t_out_gddpw);
			switch (cat_dash) {
				case "traffic_batas_prov":
					dash_traffic_perbatasan_provinsi_cctv(cctv_link_dum[i].nama_cctv);
					break;
				case "traffic_batas_kota":
					dash_traffic_perbatasan_kota_cctv(cctv_link_dum[i].nama_cctv);
					break;
				case "road_safety_batas_provinsi":
					dash_road_safety_perbatasan_provinsi_cctv(cctv_link_dum[i].nama_cctv);
					break;
				case "road_safety_batas_kota":
					dash_road_safety_perbatasan_kota_cctv(cctv_link_dum[i].nama_cctv);
					break;
				default:
					break;
			}
			map.setZoom(22);
			map.setCenter({
				lat: parseFloat(cctv_link_dum[i].lat),
				lng: parseFloat(cctv_link_dum[i].lng),
			});
			cctv_arr_dum[i].clicked = true;
			if (cctv_link_dum[i].status == "mati") {
				showInfoWindow(
					cctv_arr_dum[i],
					`
                    <style>
                    .gm-style .gm-style-iw{
                      background: linear-gradient(116deg, #250473 2.33%, rgba(27, 0, 92, 0.53) 108.81%) !important;
                    }
                  
                    .gm-style .gm-style-iw-tc::after {
                      background: linear-gradient(116deg, #250473 2.33%, rgba(27, 0, 92, 0.53) 108.81%) !important;
                    }
    
                    .popover-arrow::after{
                      border-right-color:#503987!important;
                    }
                    
                    .popover {
                      --bs-popover-max-width: 500px!important;
                    }
                    </style>
                    <div>
                      <div class="row text-white">
                        <div class="col-9">
                          ${cctv_link_dum[i].nama_cctv}
                        </div>
                        <div class="col-3">
                          ${current_date()}
                        </div>
                      </div>
                      
                      <div class="mt-3 text-center text-danger" style="width: 27rem; height:2rem;">
                          CCTV DALAM KEADAAN OFFLINE
                      </div>
                      
                    </div>
                    `
				);
			} else {
				if (cctv_link_dum[i].category == "OBJEK VITAL") {
					showInfoWindow(
						cctv_arr_dum[i],
						`
                    <style>
                    .gm-style .gm-style-iw{
                      background: linear-gradient(116deg, #250473 2.33%, rgba(27, 0, 92, 0.53) 108.81%) !important;
                    }
                  
                    .gm-style .gm-style-iw-tc::after {
                      background: linear-gradient(116deg, #250473 2.33%, rgba(27, 0, 92, 0.53) 108.81%) !important;
                    }
    
                    .popover-arrow::after{
                      border-right-color:#503987!important;
                    }
                    
                    .popover {
                      --bs-popover-max-width: 500px!important;
                    }
                    </style>
                    <div>
                      <div class="row text-white">
                        <div class="col-9">
                          ${cctv_link_dum[i].nama_cctv}
                        </div>
                        <div class="col-3">
                          ${current_date()}
                        </div>
                      </div>
                      
                      <div class="container mt-2" >
                        <div class="mt-2 row justify-content-md-center"  id="stream_cctv">
                        </div>
                      </div>

                      <div class="card mt-2 border border-warning">
                        <div class="card-header border-warning text-center bg-warning text-black fw-bold font-infw-card-1">
                          <div class="row align-items-center">
                            <div class="col-10" style="padding-left:5rem!important;">
                              VOLUME KEPADATAN MASYARAKAT 30 MENIT
                            </div>
                          </div>
                        </div>
    
                        <div class="card-body row p-0" >

                          <div class="col-6 border-end border-2 border-warning" style="padding-right:0px!important">
                            <div class="text-center border border-top-0 border-start-0 border-end-0 border-2 pb-2 pt-2 fw-bold" style="border-style:dotted!important;border-color:black!important;">
                            <span id="stime_people_pria_2"></span> - <span id="etime_people_pria_2"></span>
                            </div>
                            <h5 class="text-danger text-center mt-2" id="total_people_pria_2">
                             PRIA : 
                            </h5>
                          </div>

                          <div class="col-6 border-end border-2 border-warning" style="padding-left:0px!important">
                            <div class="text-center border border-top-0 border-start-0 border-end-0 border-2 pb-2 pt-2 fw-bold" style="border-style:dotted!important;border-color:black!important;">
                            <span id="stime_people_wanita_2"></span> - <span id="etime_people_wanita_2"></span>
                            </div>
                            <h5 class="text-danger text-center mt-2" id="total_people_wanita_2">
                              WANITA : 
                            </h5>
                          </div>

                        </div>
                      </div>
    
    
                      <div class="card mt-2 border border-warning">
                        <div class="card-header border-warning text-center bg-warning text-black fw-bold font-infw-card-1">
                          <div class="row align-items-center">
                            <div class="col-10" style="padding-left:5rem!important;">
                              VOLUME KEPADATAN MASYARAKAT
                            </div>
                            <div class="col-2 ms-auto">
                              <button id="chart-button" type="button" class="btn btn-warning" data-bs-container="body" data-bs-toggle="popover" data-bs-offset="10,40" data-bs-placement="right" data-bs-custom-class="custom-popover" data-bs-html="true"><img src="${global_url_ci}my/logo/chart.png" width="18" height="18"></img></button>
                            </div>
                          </div>
                        </div>
    
                        <div class="card-body row p-0" >

                          <div class="col-6 border-end border-2 border-warning" style="padding-right:0px!important">
                            <div class="text-center border border-top-0 border-start-0 border-end-0 border-2 pb-2 pt-2 fw-bold" style="border-style:dotted!important;border-color:black!important;">
                            <span id="stime_people_pria"></span> - <span id="etime_people_pria"></span>
                            </div>
                            <h5 class="text-danger text-center mt-2" id="total_people_pria">
                             PRIA : 
                            </h5>
                          </div>

                          <div class="col-6 border-end border-2 border-warning" style="padding-left:0px!important">
                            <div class="text-center border border-top-0 border-start-0 border-end-0 border-2 pb-2 pt-2 fw-bold" style="border-style:dotted!important;border-color:black!important;">
                            <span id="stime_people_wanita"></span> - <span id="etime_people_wanita"></span>
                            </div>
                            <h5 class="text-danger text-center mt-2" id="total_people_wanita">
                              WANITA : 
                            </h5>
                          </div>

                        </div>
                      </div>

                    </div>
                    `
					);
				} else {
					switch (cat_content_cctv) {
						case "only_live":
							showInfoWindow(
								cctv_arr_dum[i],
								`
                          <style>
                          .gm-style .gm-style-iw{
                            background: linear-gradient(116deg, #250473 2.33%, rgba(27, 0, 92, 0.53) 108.81%) !important;
                          }
                        
                          .gm-style .gm-style-iw-tc::after {
                            background: linear-gradient(116deg, #250473 2.33%, rgba(27, 0, 92, 0.53) 108.81%) !important;
                          }
          
                          .popover-arrow::after{
                            border-right-color:#503987!important;
                          }
                          
                          .popover {
                            --bs-popover-max-width: 500px!important;
                          }
                          </style>
                          <div>
                            <div class="row text-white">
                              <div class="col-9">
                                ${cctv_link_dum[i].nama_cctv}
                              </div>
                              <div class="col-3">
                                ${current_date()}
                              </div>
                            </div>
                            
                            <div class="container mt-2" >
                              <div class="mt-2 row justify-content-md-center"  id="stream_cctv">
                              </div>
                            </div>
                            
                          </div>
                          `
							);
							break;
						default:
							showInfoWindow(
								cctv_arr_dum[i],
								`
                          <style>
                          .gm-style .gm-style-iw{
                            background: linear-gradient(116deg, #250473 2.33%, rgba(27, 0, 92, 0.53) 108.81%) !important;
                          }
                        
                          .gm-style .gm-style-iw-tc::after {
                            background: linear-gradient(116deg, #250473 2.33%, rgba(27, 0, 92, 0.53) 108.81%) !important;
                          }
          
                          .popover-arrow::after{
                            border-right-color:#503987!important;
                          }
                          
                          .popover {
                            --bs-popover-max-width: 500px!important;
                          }
                          </style>
                          <div>
                            <div class="row text-white">
                              <div class="col-9">
                                ${cctv_link_dum[i].nama_cctv}
                              </div>
                              <div class="col-3">
                                ${current_date()}
                              </div>
                            </div>
                            
                            <div class="container mt-2" >
                              <div class="mt-2 row justify-content-md-center"  id="stream_cctv">
                              </div>
                            </div>
          
                            <div class="row mt-2">
                              <div class="col-6">
                                <div class="card border border-warning">
                                  <div class="card-header bg-warning text-black fw-bold">
                                    <div class="row">
                                      <div class="col-7 font-infw-card-1">
                                        STATUS LALIN
                                      </div>
                                      <div class="col-5 font-infw-card-2" id="time_lalin_cctv">
                                      </div>
                                    </div>
                                  </div>
                                  <div class="card-body row align-items-center" style="padding:0.5rem!important;">
                                    <div class="col-8 align-text-bottom">
                                      <p class="card-text fw-bold" id="lalin_cctv"></p>
                                    </div>
                                    <div class="col-4">
                                      <img src="${global_url_ci}my/logo/lalin.png"></img>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="col-6">
                                <div class="card border border-warning">
                                  <div class="card-header bg-warning text-black fw-bold">
                                    <div class="row">
                                      <div class="col-7 font-infw-card-1">
                                        AVG SPEED
                                      </div>
                                      <div class="col-5 font-infw-card-2" id="time_avg_cctv">
                                      </div>
                                    </div>
                                  </div>
                                  <div class="card-body row align-items-center" style="padding:0.5rem!important;">
                                    <div class="col-8 align-text-bottom">
                                      <p class="card-text fw-bold text-danger"><span id="avg_cctv"></span> KM/H</p>
                                    </div>
                                    <div class="col-4">
                                      <img src="${global_url_ci}my/logo/speed.png"></img>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              
                            </div>
          
                            <div class="card mt-2 border border-warning">
                              <div class="card-header border-warning text-center bg-warning text-black fw-bold font-infw-card-1">
                                <div class="row align-items-center">
                                  <div class="col-10" style="padding-left:5rem!important;">
                                    VOLUME KENDARAAN
                                  </div>
                                  <div class="col-2 ms-auto">
                                    <button id="chart-button" type="button" class="btn btn-warning" data-bs-container="body" data-bs-toggle="popover" data-bs-offset="10,40" data-bs-placement="right" data-bs-custom-class="custom-popover" data-bs-html="true"><img src="${global_url_ci}my/logo/chart.png" width="18" height="18"></img></button>
                                  </div>
                                </div>
                              </div>
          
                              <div class="card-body row p-0" >
                                <div class="col-6 text-center border-end border-2 border-warning" style="padding-right:0px!important">
                                  <div class="border border-top-0 border-start-0 border-end-0 border-2 pb-2 pt-2 fw-bold" style="border-style:dotted!important;border-color:black!important;">
                                  <span id="stime_kendaraan_kiri"></span> - <span id="etime_kendaraan_kiri"></span>
                                  </div>
                                  <h5 class="text-danger mt-2" id="total_kendaraan_kiri">
                                  </h5>
                                  <div class="row">
                                    <div class="col-6">
                                      <img src="${global_url_ci}my/logo/mobil.png"></img>
                                      <p class="text-danger fw-bold" id="total_mobil_kiri"></p>
                                    </div>
                                    <div class="col-6">
                                      <img src="${global_url_ci}my/logo/motor.png"></img>
                                      <p class="text-danger fw-bold" id="total_motor_kiri"></p>
                                    </div>
                                    <div class="col-6">
                                      <img src="${global_url_ci}my/logo/bus.png"></img>
                                      <p class="text-danger fw-bold" id="total_bus_kiri"></p>
                                    </div>
                                    <div class="col-6">
                                      <img src="${global_url_ci}my/logo/truck.png"></img>
                                      <p class="text-danger fw-bold" id="total_truck_kiri"></p>
                                    </div>
                                  </div>
                                </div>
                                <div class="col-6 text-center border-start border-2 border-warning" style="padding-left:0px!important">
                                  <div class="border border-top-0 border-start-0 border-end-0 border-2 pb-2 pt-2 fw-bold" style="border-style:dotted!important;border-color:black!important;">
                                    <span id="stime_kendaraan_kanan"></span> - <span id="etime_kendaraan_kanan"></span>
                                  </div>
                                  <h5 class="text-danger mt-2" id="total_kendaraan_kanan">
                                  </h5>
                                  <div class="row">
                                    <div class="col-6">
                                      <img src="${global_url_ci}my/logo/mobil.png"></img>
                                      <p class="text-danger fw-bold" id="total_mobil_kanan"></p>
                                    </div>
                                    <div class="col-6">
                                      <img src="${global_url_ci}my/logo/motor.png"></img>
                                      <p class="text-danger fw-bold" id="total_motor_kanan"></p>
                                    </div>
                                    <div class="col-6">
                                      <img src="${global_url_ci}my/logo/bus.png"></img>
                                      <p class="text-danger fw-bold" id="total_bus_kanan"></p>
                                    </div>
                                    <div class="col-6">
                                      <img src="${global_url_ci}my/logo/truck.png"></img>
                                      <p class="text-danger fw-bold" id="total_truck_kanan"></p>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
        
                            <div class="card mt-2 border border-warning">
                              <div class="card-header border-warning text-center bg-warning text-black fw-bold font-infw-card-1">
                                ROAD EVENT
                              </div>
                              <div class="card-body row p-0" >
                                <div class="text-center border-start border-2 border-warning" style="padding-left:0px!important">
                                  <div class="border border-top-0 border-start-0 border-end-0 border-2 pb-2 pt-2 fw-bold" style="border-style:dotted!important;border-color:black!important;">
                                    <span class="fw-bold" id="stime_road_event"> </span> - <span class="fw-bold" id="etime_road_event"></span>
                                  </div>
                                </div>
                                <div class="mt-2 ps-3 fw-bold">
                                  <table class="w-100 table">
                                    <tbody id='t-road-event'>
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                            </div>
                            
                          </div>
                          `
							);
							break;
					}
				}
				setTimeout(async () => {
					$("#stream_cctv").html("");
					// $('#stream_cctv').append(`
					// <video
					//   id="my-video"
					//   class="video-js w-100 border border-warning rounded"
					//   controls
					//   preload="auto"
					//   width="640"
					//   height="230"
					//   data-setup="{}"
					// >
					//   <source id='url_stream' src="${cctv_link_dum[i].hls_cctv}" type="application/x-mpegURL" />
					//   <p class="vjs-no-js">
					//     To view this video please enable JavaScript, and consider upgrading to a
					//     web browser that
					//     <a href="https://videojs.com/html5-video-support/" target="_blank"
					//       >supports HTML5 video</a
					//     >
					//   </p>
					// </video>
					// `)
					// video = document.getElementById('my-video');
					// url_st = document.getElementById('url_stream').src;
					// play()
					$("#stream_cctv").append(`
                      <input type="hidden" name="mse-url" id="mse-url" value="${cctv_link_dum[i].rowid}">
                      <video id="mse-video" autoplay muted playsinline controls style="max-width: 640px; max-height: 230px;"></video>`);

					const videoEl = document.querySelector("#mse-video");
					const mseUrl = document.querySelector("#mse-url").value;

					// fix stalled video in safari
					videoEl.addEventListener("pause", () => {
						if (
							videoEl.currentTime >
							videoEl.buffered.end(videoEl.buffered.length - 1)
						) {
							videoEl.currentTime =
								videoEl.buffered.end(videoEl.buffered.length - 1) - 0.1;
							videoEl.play();
						}
					});
					
					streamCTV = await loadCamera(mseUrl,videoEl, streamCTV)
				}, 1000);
				// })
				if (cat_content_cctv != "only_live") {
					setTimeout(() => {
						if (cctv_link_dum[i].channel_id_event != null) {
							var popoverTriggerList = [].slice.call(
								document.querySelectorAll('[data-bs-toggle="popover"]')
							);
							var popoverList = popoverTriggerList.map(function (
								popoverTriggerEl
							) {
								popover = new bootstrap.Popover(popoverTriggerEl, {
									content: `<div id="card-content">
                            <div class="card border border-warning">
                              <div class="card-header border-warning bg-warning text-black fw-bold font-infw-card-1" id='f-card-pop'>
                              </div>
                              <div class="card-body">
                                <div id="chart_volkend"></div>
                              </div>
                            </div>
                            <div class="bg-danger text-white rounded mt-2 p-2" id='data_tertinggi_grafik'>
                            </div>
                          </div>`,
									// trigger: 'focus' // Tutup Popover saat kehilangan fokus
								});
								map.addListener("drag", function () {
									popover.hide();
								});
								return popover;
							});

							$("#chart-button").click(function () {
								setTimeout(() => {
									$("#f-card-pop").html("");
									$("#f-card-pop").append(`
                          <select class="form-select border-warning bg-warning" style="width:35%!important;" aria-label="Default select example" id="f-card" onchange="filter_grafik_traffic('${cctv_link_dum[i].channel_id}')">
                            <option value="limamenit" selected>5 MIN</option>
                            <option value="hourly">Hourly</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                          </select>
  
                        
                          `);
									$("#data_tertinggi_grafik").html("");
									prom_get_data_grafik_traffic(
										"",
										"",
										cctv_link_dum[i].channel_id,
										"limamenit"
									).then(function (data) {
										$("#data_tertinggi_grafik").append(`
                              <p>${data.dataTeratas}</p>
                            `);
										show_chart_volkend(
											data,
											"#chart_volkend",
											"render",
											"grafik_kendaraan"
										);
									});
								}, 1000);
							});
							get_data_dahua_traffic_lalin(cctv_link_dum[i].channel_id);
							// get_data_dahua_avg_speed(cctv_link_dum[i].channel_id_event)
							get_data_dahua_road_event(cctv_link_dum[i].channel_id_event);
						} else if (cctv_link_dum[i].category == "OBJEK VITAL") {
							var popoverTriggerList = [].slice.call(
								document.querySelectorAll('[data-bs-toggle="popover"]')
							);
							var popoverList = popoverTriggerList.map(function (
								popoverTriggerEl
							) {
								popover = new bootstrap.Popover(popoverTriggerEl, {
									content: `<div id="card-content">
                            <div class="card border border-warning">
                              <div class="card-header border-warning bg-warning text-black fw-bold font-infw-card-1" id='f-card-pop'>
                              </div>
                              <div class="card-body">
                                <div id="chart_volkend"></div>
                              </div>
                            </div>
                            <div class="bg-danger text-white rounded mt-2 p-2" id='data_tertinggi_grafik'>
                            </div>
                          </div>`,
									// trigger: 'focus' // Tutup Popover saat kehilangan fokus
								});
								map.addListener("drag", function () {
									popover.hide();
								});
								return popover;
							});

							$("#chart-button").click(function () {
								setTimeout(() => {
									$("#f-card-pop").html("");
									$("#f-card-pop").append(`
                          <select class="form-select border-warning bg-warning" style="width:35%!important;" aria-label="Default select example" id="f-card" onchange="filter_grafik_traffic_people('${cctv_link_dum[i].channel_id}')">
                            <option value="limamenit" selected>5 MIN</option>
                            <option value="hourly">Hourly</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                          </select>
  
                        
                          `);
									$("#data_tertinggi_grafik").html("");
									prom_get_data_grafik_traffic_people(
										"",
										"",
										cctv_link_dum[i].channel_id,
										"limamenit"
									).then(function (data) {
										$("#data_tertinggi_grafik").append(`
                              <p>${data.dataTeratas}</p>
                            `);
										show_chart_volkend(
											data,
											"#chart_volkend",
											"render",
											"grafik_people"
										);
									});
								}, 1000);
							});
							get_data_dahua_people_pria(cctv_link_dum[i].channel_id);
							get_data_dahua_people_wanita(cctv_link_dum[i].channel_id);
							get_data_dahua_people_pria_2(cctv_link_dum[i].channel_id);
							get_data_dahua_people_wanita_2(cctv_link_dum[i].channel_id);
							get_data_dahua_kepadatan_masyarakat(cctv_link_dum[i].channel_id);
						} else {
							var popoverTriggerList = [].slice.call(
								document.querySelectorAll('[data-bs-toggle="popover"]')
							);
							var popoverList = popoverTriggerList.map(function (
								popoverTriggerEl
							) {
								popover = new bootstrap.Popover(popoverTriggerEl, {
									content: `<div id="card-content">
                            <div class="card border border-warning">
                              <div class="card-header border-warning bg-warning text-black fw-bold font-infw-card-1" id='f-card-pop'>
                              </div>
                              <div class="card-body">
                                <div id="chart_volkend"></div>
                              </div>
                            </div>
                            <div class="bg-danger text-white rounded mt-2 p-2" id='data_tertinggi_grafik'>
                            </div>
                          </div>`,
									// trigger: 'focus' // Tutup Popover saat kehilangan fokus
								});
								map.addListener("drag", function () {
									popover.hide();
								});
								return popover;
							});

							$("#chart-button").click(function () {
								setTimeout(() => {
									$("#f-card-pop").html("");
									$("#f-card-pop").append(`
                          <select class="form-select border-warning bg-warning" style="width:35%!important;" aria-label="Default select example" id="f-card" onchange="filter_grafik_traffic('${cctv_link_dum[i].channel_id}')">
                            <option value="limamenit" selected>5 MIN</option>
                            <option value="hourly">Hourly</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                          </select>
  
                        
                          `);
									$("#data_tertinggi_grafik").html("");
									prom_get_data_grafik_traffic(
										"",
										"",
										cctv_link_dum[i].channel_id,
										"limamenit"
									).then(function (data) {
										$("#data_tertinggi_grafik").append(`
                              <p>${data.dataTeratas}</p>
                            `);
										show_chart_volkend(
											data,
											"#chart_volkend",
											"render",
											"grafik_kendaraan"
										);
									});
								}, 1000);
							});
							$("#avg_cctv").html(`-`);
							// $('#avg_cctv').html(`0`)
							$("#time_avg_cctv").html(`-`);
							$("#t-road-event").append(`
                          <tr>
                            <td>TIDAK ADA KEJADIAN</td>
                          </tr>
                        `);
							$("#stime_road_event").html("");
							$("#etime_road_event").html("");
						}
						get_data_dahua_traffic_lalin(cctv_link_dum[i].channel_id);
						get_data_dahua_traffic_counting_kanan(cctv_link_dum[i].channel_id);
						get_data_dahua_traffic_counting_kiri(cctv_link_dum[i].channel_id);
					}, 300);
				}
			}
		});
	});
	infowindowCctv.addListener("closeclick", function () {
		switch (cat_dash) {
			case "traffic_batas_prov":
				dash_traffic_perbatasan_provinsi("");
				break;
			case "traffic_batas_kota":
				dash_traffic_perbatasan_kota_cctv("");
				break;
			case "road_safety_batas_provinsi":
				dash_road_safety_perbatasan_provinsi("");
				break;
			case "road_safety_batas_kota":
				dash_road_safety_perbatasan_kota("");
				break;

			default:
				break;
		}
		if (popover != undefined) {
			popover.hide();
		}
	});
}

function showInfoWindow(marker, content) {
	infowindowCctv.setContent(content);
	infowindowCctv.open(map, marker);
}

function get_data_dahua_traffic_lalin_hover(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	let stamp_start = today.setTime(today.getTime() - 5 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	$("#cctv_hover").html("");
	$("#time_cctv_hover").html("");
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_counting?channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				$("#cctv_hover").html(
					`SITUASI LALIN ${data.data[0].situasi_lalin} / -, ${data.data[0].rekomendasi_lalin}`
				);
				$("#time_hover").html(
					`${current_date()} , ${convert_timestamp_to_time(
						s_start
					)} - ${convert_timestamp_to_time(s_end)}`
				);
			} else {
				$("#cctv_hover").html(`SITUASI LALIN - / -, `);
				$("#time_hover").html(
					`${current_date()} , ${convert_timestamp_to_time(
						s_start
					)} - ${convert_timestamp_to_time(s_end)}`
				);
			}
			// closeToast()
		});
	});
}

function get_data_dahua_people_hover(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	let stamp_start = today.setTime(today.getTime() - 5 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	$("#cctv_hover_people").html("");
	$("#time_cctv_hover_people").html("");
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_people?channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				$("#cctv_hover_people").html(
					`KEPADATAN MASYARAKAT ${data.data[0].kepadatan} / -`
				);
				$("#time_hover_people").html(
					`${current_date()} , ${convert_timestamp_to_time(
						s_start
					)} - ${convert_timestamp_to_time(s_end)}`
				);
			} else {
				$("#cctv_hover_people").html(`KEPADATAN MASYARAKAT - / -, `);
				$("#time_hover_people").html(
					`${current_date()} , ${convert_timestamp_to_time(
						s_start
					)} - ${convert_timestamp_to_time(s_end)}`
				);
			}
			// closeToast()
		});
	});
}

function get_data_dahua_traffic_lalin(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	let stamp_start = today.setTime(today.getTime() - 5 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	$("#lalin_cctv").html("");
	$("#time_lalin_cctv").html("");
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_counting?channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				switch (data.data[0].situasi_lalin.toLowerCase()) {
					case "lancar":
						$("#lalin_cctv").html(
							`<span class="text-success">${data.data[0].situasi_lalin}</span>`
						);
						break;
					case "padat":
						$("#lalin_cctv").html(
							`<span class="text-warning">${data.data[0].situasi_lalin}</span>`
						);
						break;
					case "macet":
						$("#lalin_cctv").html(
							`<span class="text-danger">${data.data[0].situasi_lalin}</span>`
						);
						break;

					default:
						break;
				}
				$("#time_lalin_cctv").html(
					`${convert_timestamp_to_time(s_start)} - ${convert_timestamp_to_time(
						s_end
					)}`
				);
			} else {
				$("#lalin_cctv").html(`-`);
				$("#time_lalin_cctv").html(
					`${convert_timestamp_to_time(s_start)} - ${convert_timestamp_to_time(
						s_end
					)}`
				);
			}
			t_out_gddtl = setTimeout(() => {
				get_data_dahua_traffic_lalin(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function get_data_dahua_avg_speed(cid) {
	var today = new Date();
	var today2 = new Date();
	let stamp_start = today.setTime(today.getTime() - 60 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	$("#avg_cctv").html("");
	$("#time_avg_cctv").html("");
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/avg_speed_cid?channel_id=${cid}&time_start=${stamp_start}&time_end=${stamp_end}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				$("#avg_cctv").html(`-`);
				// $('#avg_cctv').html(data.data[0].avg)
				$("#time_avg_cctv").html(
					`${convert_timestamp_to_time(s_start)} - ${convert_timestamp_to_time(
						s_end
					)}`
				);
			} else {
				$("#avg_cctv").html(`-`);
				$("#time_avg_cctv").html(
					`${convert_timestamp_to_time(s_start)} - ${convert_timestamp_to_time(
						s_end
					)}`
				);
			}
			t_out_gddas = setTimeout(() => {
				get_data_dahua_avg_speed(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function get_data_dahua_traffic_counting_kanan(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	let stamp_start = today.setTime(today.getTime() - 30 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_counting?channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				get_data_dahua_traffic_category_kanan(
					cid,
					convert_timestamp_to_time(s_start),
					convert_timestamp_to_time(s_end),
					c_date
				);
				$("#total_kendaraan_kanan").html(data.data[0].jml);
				$("#stime_kendaraan_kanan").html(convert_timestamp_to_time(s_start));
				$("#etime_kendaraan_kanan").html(convert_timestamp_to_time(s_end));
			} else {
				$("#total_kendaraan_kanan").html("0");
				$("#stime_kendaraan_kanan").html(convert_timestamp_to_time(s_start));
				$("#etime_kendaraan_kanan").html(convert_timestamp_to_time(s_end));
			}
			t_out_gddtcka = setTimeout(() => {
				get_data_dahua_traffic_counting_kanan(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function get_data_dahua_road_event(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	let stamp_start = today.setTime(today.getTime() - 30 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	$("#t-road-event").html("");
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/road_event?channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				data.data.forEach((e) => {
					$("#t-road-event").append(`
            <tr>
              <td>${e.event_name}</td>
              <td>${e.jml}</td>
            </tr>
          `);
				});
				$("#stime_road_event").html(convert_timestamp_to_time(s_start));
				$("#etime_road_event").html(convert_timestamp_to_time(s_end));
			} else {
				$("#t-road-event").append(`
            <tr>
              <td>TIDAK ADA KEJADIAN</td>
            </tr>
          `);
				$("#stime_road_event").html(convert_timestamp_to_time(s_start));
				$("#etime_road_event").html(convert_timestamp_to_time(s_end));
			}
			t_out_gddre = setTimeout(() => {
				get_data_dahua_road_event(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function get_data_dahua_traffic_counting_kiri(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	today.setHours(0, 0, 0, 0);
	let stamp_start = today.setTime(today.getTime());
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_counting?channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				get_data_dahua_traffic_category_kiri(
					cid,
					convert_timestamp_to_time(s_start),
					convert_timestamp_to_time(s_end),
					c_date
				);
				$("#total_kendaraan_kiri").html(data.data[0].jml);
				$("#stime_kendaraan_kiri").html(convert_timestamp_to_time(s_start));
				$("#etime_kendaraan_kiri").html(convert_timestamp_to_time(s_end));
			} else {
				$("#total_kendaraan_kiri").html("0");
				$("#stime_kendaraan_kiri").html(convert_timestamp_to_time(s_start));
				$("#etime_kendaraan_kiri").html(convert_timestamp_to_time(s_end));
			}
			t_out_gddtcki = setTimeout(() => {
				get_data_dahua_traffic_counting_kiri(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function get_data_dahua_traffic_category_kanan(cid, s_start, s_end, c_date) {
	return new Promise(function (resolve, reject) {
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_category?channel_id=${cid}&tanggal=${c_date}&time_start=${s_start}&time_end=${s_end}`,
			{},
			token_sm
		).then((data) => {
			$("#total_bus_kanan").html("0");
			$("#total_mobil_kanan").html("0");
			$("#total_truck_kanan").html("0");
			$("#total_motor_kanan").html("0");
			data.data.forEach((e) => {
				if (e.jenis_kendaraan.toLowerCase() == "bus") {
					$("#total_bus_kanan").html(e.jml);
				}
				if (e.jenis_kendaraan.toLowerCase() == "minibus") {
					$("#total_mobil_kanan").html(e.jml);
				}
				if (e.jenis_kendaraan.toLowerCase() == "truck") {
					$("#total_truck_kanan").html(e.jml);
				}
				if (e.jenis_kendaraan.toLowerCase() == "motor") {
					$("#total_motor_kanan").html(e.jml);
				}
			});
		});
	});
}

function get_data_dahua_traffic_category_kiri(cid, s_start, s_end, c_date) {
	return new Promise(function (resolve, reject) {
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_category?channel_id=${cid}&tanggal=${c_date}&time_start=${s_start}&time_end=${s_end}`,
			{},
			token_sm
		).then((data) => {
			$("#total_bus_kiri").html("0");
			$("#total_mobil_kiri").html("0");
			$("#total_truck_kiri").html("0");
			$("#total_motor_kiri").html("0");
			data.data.forEach((e) => {
				if (e.jenis_kendaraan.toLowerCase() == "bus") {
					$("#total_bus_kiri").html(e.jml);
				}
				if (e.jenis_kendaraan.toLowerCase() == "minibus") {
					$("#total_mobil_kiri").html(e.jml);
				}
				if (e.jenis_kendaraan.toLowerCase() == "truck") {
					$("#total_truck_kiri").html(e.jml);
				}
				if (e.jenis_kendaraan.toLowerCase() == "motor") {
					$("#total_motor_kiri").html(e.jml);
				}
			});
		});
	});
}

function get_data_dahua_people_pria(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	today.setHours(0, 0, 0, 0);
	let stamp_start = today.setTime(today.getTime());
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_people?gender=1&channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				$("#total_people_pria").html("PRIA : " + data.jml);
				$("#stime_people_pria").html(convert_timestamp_to_time(s_start));
				$("#etime_people_pria").html(convert_timestamp_to_time(s_end));
				// $('#people_anak_pria').html('0')
				// $('#people_remaja_pria').html('0')
				// $('#people_dewasa_pria').html('0')
				// $('#people_pralansia_pria').html('0')
				// $('#people_lansia_pria').html('0')
				// data.data.forEach(e => {
				//   if (e.category.toLowerCase() == 'anak') {
				//     $('#people_anak_pria').html(e.jml)
				//   }
				//   if (e.category.toLowerCase() == 'remaja') {
				//     $('#people_remaja_pria').html(e.jml)
				//   }
				//   if (e.category.toLowerCase() == 'dewasa') {
				//     $('#people_dewasa_pria').html(e.jml)
				//   }
				//   if (e.category.toLowerCase() == 'pra lanjut usia') {
				//     $('#people_pralansia_pria').html(e.jml)
				//   }
				//   if (e.category.toLowerCase() == 'lanjut usia') {
				//     $('#people_lansia_pria').html(e.jml)
				//   }
				// });
			} else {
				$("#total_people_pria").html("PRIA : 0");
				$("#stime_people_pria").html(convert_timestamp_to_time(s_start));
				$("#etime_people_pria").html(convert_timestamp_to_time(s_end));
				// $('#people_anak_pria').html('0')
				// $('#people_remaja_pria').html('0')
				// $('#people_dewasa_pria').html('0')
				// $('#people_pralansia_pria').html('0')
				// $('#people_lansia_pria').html('0')
			}
			t_out_gddpp = setTimeout(() => {
				get_data_dahua_people_pria(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function get_data_dahua_people_wanita(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	today.setHours(0, 0, 0, 0);
	let stamp_start = today.setTime(today.getTime());
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_people?gender=2&channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				$("#total_people_wanita").html("WANITA : " + data.jml);
				$("#stime_people_wanita").html(convert_timestamp_to_time(s_start));
				$("#etime_people_wanita").html(convert_timestamp_to_time(s_end));
				// $('#people_anak_wanita').html('0')
				// $('#people_remaja_wanita').html('0')
				// $('#people_dewasa_wanita').html('0')
				// $('#people_pralansia_wanita').html('0')
				// $('#people_lansia_wanita').html('0')
				// data.data.forEach(e => {
				//   console.log(e);
				//   if (e.category.toLowerCase() == 'anak') {
				//     $('#people_anak_wanita').html(e.jml)
				//   }
				//   if (e.category.toLowerCase() == 'remaja') {
				//     $('#people_remaja_wanita').html(e.jml)
				//   }
				//   if (e.category.toLowerCase() == 'dewasa') {
				//     $('#people_dewasa_wanita').html(e.jml)
				//   }
				//   if (e.category.toLowerCase() == 'pra lanjut usia') {
				//     $('#people_pralansia_wanita').html(e.jml)
				//   }
				//   if (e.category.toLowerCase() == 'lanjut usia') {
				//     $('#people_lansia_wanita').html(e.jml)
				//   }
				// });
			} else {
				$("#total_people_wanita").html("WANITA : 0");
				$("#stime_people_wanita").html(convert_timestamp_to_time(s_start));
				$("#etime_people_wanita").html(convert_timestamp_to_time(s_end));
				// $('#people_anak_wanita').html('0')
				// $('#people_remaja_wanita').html('0')
				// $('#people_dewasa_wanita').html('0')
				// $('#people_pralansia_wanita').html('0')
				// $('#people_lansia_wanita').html('0')
			}
			t_out_gddpw = setTimeout(() => {
				get_data_dahua_people_wanita(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function get_data_dahua_people_pria_2(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	let stamp_start = today.setTime(today.getTime() - 30 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_people?gender=1&channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				$("#total_people_pria_2").html("PRIA : " + data.jml);
				$("#stime_people_pria_2").html(convert_timestamp_to_time(s_start));
				$("#etime_people_pria_2").html(convert_timestamp_to_time(s_end));
			} else {
				$("#total_people_pria_2").html("PRIA : 0");
				$("#stime_people_pria_2").html(convert_timestamp_to_time(s_start));
				$("#etime_people_pria_2").html(convert_timestamp_to_time(s_end));
			}
			t_out_gddpp = setTimeout(() => {
				get_data_dahua_people_pria_2(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function get_data_dahua_people_wanita_2(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	let stamp_start = today.setTime(today.getTime() - 30 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_people?gender=2&channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				$("#total_people_wanita_2").html("WANITA : " + data.jml);
				$("#stime_people_wanita_2").html(convert_timestamp_to_time(s_start));
				$("#etime_people_wanita_2").html(convert_timestamp_to_time(s_end));
			} else {
				$("#total_people_wanita_2").html("WANITA : 0");
				$("#stime_people_wanita_2").html(convert_timestamp_to_time(s_start));
				$("#etime_people_wanita_2").html(convert_timestamp_to_time(s_end));
			}
			t_out_gddpw = setTimeout(() => {
				get_data_dahua_people_wanita_2(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function get_data_dahua_kepadatan_masyarakat(cid) {
	var today = new Date();
	var today2 = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;
	let stamp_start = today.setTime(today.getTime() - 5 * 60 * 1000);
	let s_start = String(stamp_start).slice(0, -3);
	let stamp_end = today2.setTime(today2.getTime());
	let s_end = String(stamp_end).slice(0, -3);
	$("#kepadatan_masyarakat").html("");
	$("#time_kepadatan_masyarakat").html("");
	return new Promise(function (resolve, reject) {
		// showToast()
		getDataSmNew(
			`${global_url_api}sm_integration/traffic_counting?channel_id=${cid}&tanggal=${c_date}&time_start=${convert_timestamp_to_time(
				s_start
			)}&time_end=${convert_timestamp_to_time(s_end)}`,
			{},
			token_sm
		).then((data) => {
			if (data.status != false) {
				switch (data.data[0].kepadatan.toLowerCase()) {
					case "lenggang":
						$("#kepadatan_masyarakat").html(
							`<span class="text-success">${data.data[0].kepadatan}</span>`
						);
						break;
					case "padat":
						$("#kepadatan_masyarakat").html(
							`<span class="text-warning">${data.data[0].kepadatan}</span>`
						);
						break;
					case "penuh":
						$("#kepadatan_masyarakat").html(
							`<span class="text-danger">${data.data[0].kepadatan}</span>`
						);
						break;

					default:
						break;
				}
				$("#time_kepadatan_masyarakat").html(
					`${convert_timestamp_to_time(s_start)} - ${convert_timestamp_to_time(
						s_end
					)}`
				);
			} else {
				$("#kepadatan_masyarakat").html(`-`);
				$("#time_kepadatan_masyarakat").html(
					`${convert_timestamp_to_time(s_start)} - ${convert_timestamp_to_time(
						s_end
					)}`
				);
			}
			t_out_gddtl = setTimeout(() => {
				get_data_dahua_traffic_lalin(cid);
			}, 60000);
			// closeToast()
		});
	});
}

function list_card_cctv(data) {
	$.fn.extend({
		treed: function (o) {
			var openedClass = "fa-minus-circle";
			var closedClass = "fa-plus-circle";

			if (typeof o != "undefined") {
				if (typeof o.openedClass != "undefined") {
					openedClass = o.openedClass;
				}
				if (typeof o.closedClass != "undefined") {
					closedClass = o.closedClass;
				}
			}

			//initialize each of the top levels
			var tree = $(this);
			tree.addClass("tree");
			tree
				.find("li")
				.has("ul")
				.each(function () {
					var branch = $(this); //li with children ul
					branch.prepend("<i class='indicator fa " + closedClass + "'></i>");
					branch.addClass("branch");
					branch.on("click", function (e) {
						if (this == e.target) {
							var icon = $(this).children("i:first");
							icon.toggleClass(openedClass + " " + closedClass);
							$(this).children().children().toggle();
						}
					});
					branch.children().children().toggle();
				});
			//fire event from the dynamically added icon
			tree.find(".branch .indicator").each(function () {
				$(this).on("click", function () {
					$(this).closest("li").click();
				});
			});
			//fire event to open branch if the li contains an anchor instead of text
			tree.find(".branch>a").each(function () {
				$(this).on("click", function (e) {
					$(this).closest("li").click();
					e.preventDefault();
				});
			});
			//fire event to open branch if the li contains a button instead of text
			tree.find(".branch>button").each(function () {
				$(this).on("click", function (e) {
					$(this).closest("li").click();
					e.preventDefault();
				});
			});
		},
	});

	list_arr_cctv = {};
	list_arr_cctv_sub = {};
	$("#list_cctv").html("");
	$("#list_cctv").append(`
  <div class="mb-2">
    <input type="text" class="form-control" placeholder="Cari cctv..." id="search-cctv" onkeyup="search_cctv('search-cctv')">
  </div>
    <div class="accordion" id="accordionListCctv">
    
    </div>
  `);
	const keys = Object.keys(data);
	keys.forEach((e, i) => {
		$("#accordionListCctv").append(`
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingListCctv${i}">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseListCctv${i}" aria-expanded="true" aria-controls="collapseListCctv${i}">
              <input type="checkbox" class="form-check-input me-1" id="check_${keys[
								i
							]
								.replace(/ /g, "_")
								.toLocaleLowerCase()}" onchange="check_list_cctv('${keys[i]
			.replace(/ /g, "_")
			.toLocaleLowerCase()}')" value="cctv_${keys[i].replace(
			/ /g,
			"_"
		)}" checked> CCTV ${keys[i]} : <span class="fw-bold">${
			data[e].length
		}</span>
            </button>
          </h2>
          <div id="collapseListCctv${i}" class="accordion-collapse collapse show" aria-labelledby="headingListCctv${i}" data-bs-parent="#accordionExample">
            <div class="accordion-body">
            <ul id="ftree_${keys[i].replace(/ /g, "_")}">
            </ul>
            </div>
          </div>
        </div>
        `);
		const grouped_in_out = data[e].reduce((acc, obj) => {
			const key = obj.in_out; // Nilai yang digunakan untuk pengelompokan
			if (!acc[key]) {
				acc[key] = []; // Buat array kosong jika kunci belum ada
			}
			acc[key].push(obj); // Masukkan objek ke dalam array yang sesuai dengan kunci
			return acc;
		}, {});
		const keys_grouped_in_out = Object.keys(grouped_in_out);
		keys_grouped_in_out.forEach((io) => {
			if (io != "null") {
				$(`#ftree_${keys[i].replace(/ /g, "_")}`).append(
					`<li><a href="#">${io}</a><ul class="item_tree_${keys[i].replace(
						/ /g,
						"_"
					)}" id="item_tree_${keys[i].replace(/ /g, "_")}_${io.replace(
						/ /g,
						"_"
					)}"></ul></li>`
				);
				const grouped = grouped_in_out[io].reduce((acc, obj) => {
					const key = obj.polres; // Nilai yang digunakan untuk pengelompokan
					if (!acc[key]) {
						acc[key] = []; // Buat array kosong jika kunci belum ada
					}
					acc[key].push(obj); // Masukkan objek ke dalam array yang sesuai dengan kunci
					return acc;
				}, {});
				const keys_grouped = Object.keys(grouped);
				keys_grouped.forEach((f) => {
					$(
						`#item_tree_${keys[i].replace(/ /g, "_")}_${io.replace(/ /g, "_")}`
					).append(
						`<li><a href="#">${f}</a><ul class="sub_item_tree_${keys[i].replace(
							/ /g,
							"_"
						)}" id="sub_item_tree_${keys[i].replace(/ /g, "_")}_${f.replace(
							/ /g,
							"_"
						)}_${io.replace(/ /g, "_")}"></ul></li>`
					);
					grouped[f].forEach((g) => {
						$(
							`#sub_item_tree_${keys[i].replace(/ /g, "_")}_${f.replace(
								/ /g,
								"_"
							)}_${io.replace(/ /g, "_")}`
						).append(
							`<li class="list-group-item"><a class="text-reset" href="javascript:void(0);" onclick="zoom_cctv(${g.rowid})">${g.nama_cctv}</a></li>`
						);
					});
				});
			} else {
				const grouped = data[e].reduce((acc, obj) => {
					const key = obj.polres; // Nilai yang digunakan untuk pengelompokan
					if (!acc[key]) {
						acc[key] = []; // Buat array kosong jika kunci belum ada
					}
					acc[key].push(obj); // Masukkan objek ke dalam array yang sesuai dengan kunci
					return acc;
				}, {});
				const keys_grouped = Object.keys(grouped);
				keys_grouped.forEach((f) => {
					$(`#ftree_${keys[i].replace(/ /g, "_")}`).append(
						`<li><a href="#">${f}</a><ul class="item_tree_${keys[i].replace(
							/ /g,
							"_"
						)} item_tree" id="item_tree_${keys[i].replace(
							/ /g,
							"_"
						)}_${f.replace(/ /g, "_")}"></ul></li>`
					);
					grouped[f].forEach((g) => {
						$(
							`#item_tree_${keys[i].replace(/ /g, "_")}_${f.replace(/ /g, "_")}`
						).append(
							`<li class="list-group-item"><a class="text-reset" href="javascript:void(0);" onclick="zoom_cctv(${g.rowid})">${g.nama_cctv}</a></li>`
						);
					});
				});
			}
		});

		//Initialization of treeviews
		$(`#ftree_${keys[i].replace(/ /g, "_")}`).treed({
			openedClass: "fa-minus-circle",
			closedClass: "fa-plus-circle",
		});
	});
}

function list_cctv_pemkot(data) {
	const dt = Object.keys(data)[0];
	$("#list_cctv_traffic").html("");
	$("#list_cctv_traffic").append(`
        <option value="0">LIST CCTV</option>
  `);
	if (data[dt].length > 0) {
		data[dt].forEach((v) => {
			$("#list_cctv_traffic").append(`
        <option value="${v.rowid}">${v.nama_cctv}</option>
      `);
		});
		setTimeout(() => {
			$("#list_cctv_traffic").select2();
		}, 200);
	} else {
		$("#list_cctv_traffic").append(`
        <option value="0">LIST CCTV</option>
      `);
	}
}

function zoom_cctv(data) {
	if (cctv_arr_dum[data]) {
		map.setZoom(22);
		map.setCenter(cctv_arr_dum[data].position);
	} else {
		map.setZoom(10);
		console.warn("CCTV marker not found for ID:", data);
	}
}

function prom_get_data_grafik_traffic(
	pd_id = "",
	ps_id = "",
	cid = "",
	category = ""
) {
	let polda_id = $("#polda_id").val();
	let polres_id = $("#polres_id").val();
	let filter = "";

	if (polda_id != "") {
		filter += `&polda_id=${polda_id}`;
	} else if (pd_id != "") {
		filter += `&polda_id=${pd_id}`;
	}

	if (polres_id != "") {
		filter += `&polres_id=${polres_id}`;
	} else if (ps_id != "") {
		filter += `&polres_id=${ps_id}`;
	}

	if (cid != "") {
		filter += `&channel_id=${cid}`;
	}

	if (category != "") {
		filter += `&category=${category}`;
	}

	var today = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;

	filter += `&tanggal=${c_date}`;

	// let a = getRToken().then(async r=>{
	//       // Default options are marked with *
	//       if (r.status != true) {
	//           window.location.href = `${global_url_ci}Auth/logout`
	//       }else {
	return new Promise(function (resolve, reject) {
		$.ajax({
			type: "GET",
			url: `${global_url_api}sm_integration/grafik_traffic?${filter}`,
			dataType: "json",
			headers: {
				token: "5b3dac76aaee24d14185cbc3d010fd20",
				// "second_bearer" : r.data.second_bearer
				secondbearer: `${global_sbearer}`,
			},
			success: function (r) {
				resolve(r.data);
			},
			error: function (e) {
				if (e.status == 0) {
					swal("Error !", "Koneksi server error", "error");
				} else if (e.status == 400) {
					resolve([]);
				}
				closeToast();
			},
		});
	});
	//     }
	// })
	// return a
}

function prom_get_data_grafik_traffic_people(
	pd_id = "",
	ps_id = "",
	cid = "",
	category = ""
) {
	let polda_id = $("#polda_id").val();
	let polres_id = $("#polres_id").val();
	let filter = "";

	if (polda_id != "") {
		filter += `&polda_id=${polda_id}`;
	} else if (pd_id != "") {
		filter += `&polda_id=${pd_id}`;
	}

	if (polres_id != "") {
		filter += `&polres_id=${polres_id}`;
	} else if (ps_id != "") {
		filter += `&polres_id=${ps_id}`;
	}

	if (cid != "") {
		filter += `&channel_id=${cid}`;
	}

	if (category != "") {
		filter += `&category=${category}`;
	}

	var today = new Date();
	var dd = String(today.getDate()).padStart(2, "0");
	var mm = String(today.getMonth() + 1).padStart(2, "0"); //January is 0!
	var yyyy = today.getFullYear();
	let c_date = yyyy + "-" + mm + "-" + dd;

	filter += `&tanggal=${c_date}`;

	// let a = getRToken().then(async r=>{
	//       // Default options are marked with *
	//       if (r.status != true) {
	//           window.location.href = `${global_url_ci}Auth/logout`
	//       }else {
	return new Promise(function (resolve, reject) {
		$.ajax({
			type: "GET",
			url: `${global_url_api}sm_integration/grafik_traffic_people?${filter}`,
			dataType: "json",
			headers: {
				token: "5b3dac76aaee24d14185cbc3d010fd20",
				// "second_bearer" : r.data.second_bearer
				secondbearer: `${global_sbearer}`,
			},
			success: function (r) {
				resolve(r.data);
			},
			error: function (e) {
				if (e.status == 0) {
					swal("Error !", "Koneksi server error", "error");
				} else if (e.status == 400) {
					resolve([]);
				}
				closeToast();
			},
		});
	});
	//     }
	// })
	// return a
}

function show_chart_volkend(
	data,
	id = "#chart_volkend",
	action = "render",
	category = ""
) {
	// Extracting jenis data for tooltip
	let tooltipContent = "";
	if (category == "grafik_kendaraan") {
		const jenisData = data.jenis.map((item) => ({
			cat: item.cat,
			total: parseInt(item.total),
			minibus: parseInt(item.minibus),
			bus: parseInt(item.bus),
			truck: parseInt(item.truck),
			motor: parseInt(item.motor),
		}));

		// Tooltip content
		tooltipContent = jenisData.map(
			(item) => `
      <div class="card">
        <div class="card-header">
          ${item.cat}
        </div>
        <div class="card-body">
          <p>MOBIL PENUMPANG : ${item.minibus}</p>
          <p>BUS : ${item.bus}</p>
          <p>MOBIL BARANG : ${item.truck}</p>
          <p>SEPEDA MOTOR : ${item.motor}</p>
          <p><b>TOTAL : ${item.total}</b></p>
        </div>
      </div>
  
    `
		);
	} else if (category == "grafik_people") {
		const jenisData = data.jenis.map((item) => ({
			cat: item.cat,
			total: parseInt(item.total),
			wanita: parseInt(item.wanita),
			pria: parseInt(item.pria),
		}));

		// Tooltip content
		tooltipContent = jenisData.map(
			(item) => `
      <div class="card">
        <div class="card-header">
          ${item.cat}
        </div>
        <div class="card-body">
          <p>Wanita : ${item.wanita}</p>
          <p>Pria : ${item.pria}</p>
          <p><b>TOTAL : ${item.total}</b></p>
        </div>
      </div>
  
    `
		);
	}

	const options = {
		series: [
			{
				name: "TRAFFIC",
				data: data.series,
			},
		],
		chart: {
			height: 200,
			type: "area",
			zoom: {
				enabled: true,
			},
		},
		dataLabels: {
			enabled: false,
		},
		stroke: {
			curve: "smooth",
			colors: ["#000000"],
		},
		dataLabels: {
			enabled: true,
			textAnchor: "top",
			offsetX: -4,
			offsetY: -10,
			style: {
				fontSize: "9px",
				fontWeight: 400,
				colors: ["#000000"],
			},
			background: {
				enabled: false,
			},
		},
		markers: {
			size: 5,
			colors: "#F80404",
			strokeColors: "#F80404",
		},
		grid: {
			row: {
				colors: ["#f3f3f3", "transparent"], // takes an array which will be repeated on columns
				opacity: 0.5,
			},
		},
		xaxis: {
			categories: data.categories,
		},
		tooltip: {
			custom: function ({ series, seriesIndex, dataPointIndex, w }) {
				return "<div>" + tooltipContent[dataPointIndex] + "</div>";
			},
		},
	};

	if (action == "render") {
		chart = new ApexCharts(document.querySelector(id), options);
		chart.render();
	} else if (action == "update") {
		chart.updateSeries(
			[
				{
					data: data.series,
				},
			],
			true
		);
		chart.updateOptions({
			xaxis: {
				categories: data.categories,
			},
			tooltip: {
				custom: function ({ series, seriesIndex, dataPointIndex, w }) {
					return "<div>" + tooltipContent[dataPointIndex] + "</div>";
				},
			},
		});
	}

	return chart;
}

function filter_grafik_traffic(cid) {
	showToast();
	$("#f-card").prop("disabled", true);
	prom_get_data_grafik_traffic("", "", cid, $("#f-card").val()).then(function (
		data
	) {
		$("#data_tertinggi_grafik").html("");
		$("#data_tertinggi_grafik").append(`
      <p>${data.dataTeratas}</p>
    `);
		show_chart_volkend(data, "#chart_volkend", "update", "grafik_kendaraan");
		$("#f-card").prop("disabled", false);
		closeToast();
	});
}

function filter_grafik_traffic_people(cid) {
	showToast();
	$("#f-card").prop("disabled", true);
	prom_get_data_grafik_traffic_people("", "", cid, $("#f-card").val()).then(
		function (data) {
			$("#data_tertinggi_grafik").html("");
			$("#data_tertinggi_grafik").append(`
      <p>${data.dataTeratas}</p>
    `);
			show_chart_volkend(data, "#chart_volkend", "update", "grafik_people");
			$("#f-card").prop("disabled", false);
			closeToast();
		}
	);
}

function search_cctv(id) {
	let f_cctv = $(`#${id}`).val().toLowerCase();
	let i_cctv = $(`.item_tree>.list-group-item`);
	for (var i = 0; i < i_cctv.length; i++) {
		let span = $(".list-group-item>a")[i];
		if (span.innerHTML.toLowerCase().indexOf(f_cctv) > -1) {
			i_cctv[i].style.display = "";
		} else {
			i_cctv[i].style = "display:none!important;";
		}
	}
}

function check_list_cctv(val) {
	if ($(`#check_${val}`).prop("checked")) {
		for (let i = 0; i < cctv_arr_dum_temp[val].length; i++) {
			cctv_arr_dum_temp[val][i].setMap(map);
		}
	} else {
		const filteredData = cctv_arr_dum.filter((item) => item.category === val);
		const newArray = [...filteredData];
		cctv_arr_dum_temp[val] = newArray;
		for (let i = 0; i < cctv_arr_dum_temp[val].length; i++) {
			cctv_arr_dum_temp[val][i].setMap(null);
		}
	}
}
