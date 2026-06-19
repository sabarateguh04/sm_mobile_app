let selectedCCTVs = [];
function dash_traffic_crowd_counting(filter = {}) {
	prom_clearMarkerCctvDum().then(function (d) {
		cctv_arr_dum.forEach((item) => {
			if (!list_arr_cctv[item.category]) {
				list_arr_cctv[item.category] = [];
			}

			list_arr_cctv[item.category].push(item);
		});
	});

	// add_geo(geojsonFiles);
	let param1 = {
		jenis_chart: 5,
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		polda: paramsPolda ? paramsPolda : global_polda,
	};

	let param2 = {
		jenis_chart: 30,
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		polda: paramsPolda ? paramsPolda : global_polda,
	};

	let c_polres_id;
	paramsPolres ? (param1["polres"] = paramsPolres) : c_polres_id;
	paramsPolres ? (param2["polres"] = paramsPolres) : c_polres_id;
	if ($("#f-polres").val() != undefined) {
		if ($("#f-polres").val() != "") {
			param1.polres = $("#f-polres").val().split(",")[0];
			param2.polres = $("#f-polres").val().split(",")[0];
			c_polres_id = $("#f-polres").val().split(",")[1];
		}
	}

	let filter_fifty = filter_obj(param1);
	let filter_thirty = filter_obj(param2);

	$("#konten-kanan").html(`
		<div class="d-flex justify-content-center align-items-center vh-100" id="load-kanan">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
		<div class="d-none" id="display-kanan">
			<div class="mt-3">
				<div class="d-flex justify-content-center mx-2 btn" onclick="toggleKanan()">
					<h4 class="text-white">TOTAL</h4>
				</div>
				<div class="row">
					<div class="col mt-1 p-1">
						<div class="card text-white text-center card-bt-biru">
							<div class="card-body">
								<h3 id="TotalGenderFiveMinute"></h3>
								<h3 class="d-none" id="TotalGenderThirtyMinute"></h3>
							</div>
						</div>
					</div>
				</div>
			</div>
			<ul class="nav nav-tabs nav-fill mt-4" id="tabIN" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="five-minute-tab-in" data-bs-toggle="tab" data-bs-target="#five-minute-in" type="button" role="tab" aria-controls="five-minute-in" aria-selected="true">PER 5 MENIT</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="thirty-minute-tab-in" data-bs-toggle="tab" data-bs-target="#thirty-minute-in" type="button" role="tab" aria-controls="thirty-minute-in" aria-selected="false">PER 30 MENIT</button>
				</li>
			</ul>
			<div class="tab-content" id="tabInContent">
				<div class="tab-pane fade show active p-3" id="five-minute-in" role="tabpanel" aria-labelledby="five-minute-tab-in">
					<div class="d-flex flex-column vh-100">
						<div class="mt-4">
							<div id="chart-obj-total-five-minute" style="height:200px"></div>
							<div class="badge bg-primary text-wrap d-flex justify-content-center" id="highestChartFiveMinute"></div>
						</div>
						<div class="d-flex flex-wrap mt-4">
							<div class="p-2">
								<span class="d-flex align-items-center ">
									<img src="${global_url_ci}/my/logo/pria.png" width="40" alt=""> 
									<h4 class="text-white ms-3">PRIA
										<span class="d-block" id="totalPriaFiveMinute"></span>
									</h4>
								</span>
							</div>
							<div class="p-2">
								<span class="d-flex align-items-center "><img src="${global_url_ci}/my/logo/wanita.png" width="40" alt=""> 
									<h4 class="text-white ms-3">WANITA
										<span class="d-block" id="totalWanitaFiveMinute"></span>
									</h4>
								</span>
							</div>
						</div>
						<div class="mt-4">
							<h4 class="text-white">GIAT MASYARAKAT</h4>
							<table class="table table-hover-custom custom-table">
								<tbody id="t_giat_masyarakat_1"></tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="tab-pane fade p-2" id="thirty-minute-in" role="tabpanel" aria-labelledby="thirty-minute-tab-in">
					<div class="d-flex flex-column vh-100">
						<div class="mt-4">
							<div id="chart-obj-total-thirty-minute" style="height:200px"></div>
							<div class="badge bg-primary text-wrap d-flex justify-content-center" id="highestChartThirtyMinute"></div>
						</div>
						<div class="d-flex flex-wrap mt-4">
							<div class="p-2">
								<span class="d-flex align-items-center ">
									<img src="${global_url_ci}/my/logo/pria.png" width="40" alt=""> 
									<h4 class="text-white ms-3">PRIA
										<span class="d-block" id="totalPriaThirtyMinute"></span>
									</h4>
								</span>
							</div>
							<div class="p-2">
								<span class="d-flex align-items-center "><img src="${global_url_ci}/my/logo/wanita.png" width="40" alt=""> 
									<h4 class="text-white ms-3">WANITA
										<span class="d-block" id="totalWanitaThirtyMinute"></span>
									</h4>
								</span>
							</div>
						</div>
						<div class="mt-4">
							<h4 class="text-white">GIAT MASYARAKAT</h4>
							<table class="table table-hover-custom custom-table">
								<tbody id="t_giat_masyarakat_2"></tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	`);

	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_traffic/object_vital_min?${filter_fifty}`
	).then((data) => {
		$("#load-kanan").addClass("d-none");
		$("#display-kanan").removeClass("d-none");
		$("#TotalGenderFiveMinute").text(data.data.total);
		$("#totalPriaFiveMinute").text(data.data.total_pria);
		$("#totalWanitaFiveMinute").text(data.data.total_wanita);
		$("#highestChartFiveMinute").text(data.data.grafik.dataTeratas);
		line_chart_pemkot(
			data.data.grafik,
			"#chart-obj-total-five-minute",
			"render",
			"grafik_people",
			"#1C75BC"
		);
	});

	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_traffic/object_vital_min?${filter_thirty}`
	).then((data) => {
		$("#TotalGenderThirtyMinute").text(data.data.total);
		$("#totalPriaThirtyMinute").text(data.data.total_pria);
		$("#totalWanitaThirtyMinute").text(data.data.total_wanita);
		$("#highestChartThirtyMinute").text(data.data.grafik.dataTeratas);
		line_chart_pemkot(
			data.data.grafik,
			"#chart-obj-total-thirty-minute",
			"render",
			"grafik_people",
			"#1C75BC"
		);
	});

	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_mobile_officer/laphar_opsdal?tgl_start=${date_time_filter(filter).start_date}&tgl_end=${date_time_filter(filter).end_date}&laphar=DATA PAM GIAT MASYARAKAT`
	).then((data) => {
		data = data.data;
		if (data.length > 0) {
			data.forEach((v, no) => {
				$("#t_giat_masyarakat_1").append(`
					<tr>
					<td>${no + 1}</td>
					<td>${v.jenis_kegiatan}</td>
					<td>${v.jumlah_kegiatan}</td>
					</tr>
				`);

				$("#t_giat_masyarakat_2").append(`
					<tr>
					<td>${no + 1}</td>
					<td>${v.jenis_kegiatan}</td>
					<td>${v.jumlah_kegiatan}</td>
					</tr>
				`);
			});
		} else {
			$("#t_giat_masyarakat_1").append(`
				<tr>
					<td>TIDAK ADA PAM GIAT MASYARAKAT</td>
				</tr>
			`);
			$("#t_giat_masyarakat_2").append(`
				<tr>
					<td>TIDAK ADA PAM GIAT MASYARAKAT</td>
				</tr>
			`);
		}
	});

	$("#five-minute-tab-in").on("click", function () {
		$("#TotalGenderThirtyMinute").addClass("d-none");
		$("#TotalGenderFiveMinute").removeClass("d-none");
	});

	$("#thirty-minute-tab-in").on("click", function () {
		$("#TotalGenderFiveMinute").addClass("d-none");
		$("#TotalGenderThirtyMinute").removeClass("d-none");
	});

	set_all_cctv(
		paramsPoldaId ? paramsPoldaId : global_polda_id,
		paramsPolresId ? paramsPolresId : c_polres_id,
		"3"
	);

	t_out_traffic_crowd_counting = setTimeout(() => {
		$("#konten-kanan").html(``);
		dash_traffic_crowd_counting({
			start_date: date_time_filter(filter).start_date,
			end_date: date_time_filter(filter).end_date,
		});
		hapus_geo();
	}, 300000);
}

function dash_cctv_objek_vital() {
	let c_polres_id;
	let param = {
		polda_id: global_polda_id,
		cctv_category_id: 3,
	};

	if ($("#f-polres").val() != undefined) {
		if ($("#f-polres").val() != "") {
			c_polres_id = $("#f-polres").val().split(",")[1];
		}
	}

	if (paramsPolresId || c_polres_id) {
		param["polres_id"] = paramsPolresId ? paramsPolresId : c_polres_id;
	}
	let filter_cctv = filter_obj(param);
	
	$("#konten-kiri").html(`
		<div class="d-flex justify-content-center align-items-center vh-100" id="load-kiri">
		<div class="spinner-border text-primary" role="status">
			<span class="visually-hidden">Loading...</span>
		</div>
		</div>
		<div class="d-none" id="display-kiri">
			<div class="mt-4">
				<img src="${global_url_ci}/my/logo/icon_crowd.png">
				<h4 class="text-white ms-3">CROWD COUNTING</h4>
			</div>
			<div class="mt-4 scroll-pane" style="height: 500px !important;overflow: auto;">
				<table class="table table-hover-custom custom-table">
					<tbody id="t_list_cctv_objek_vital">
					</tbody>
				</table>
			</div>
		</div>
	`);
	
	getDataSmNew(`${global_url_api}patroli_cctv/cctv?${filter_cctv}`)
	.then((response) => {
		$("#load-kiri").addClass("d-none");
		$("#display-kiri").removeClass("d-none");
		$("#t_list_cctv_objek_vital").html("");

		if (response.status) {
			let html_table_cctv = ``;
			response.data.forEach((e, no) => {
				html_table_cctv += `<tr role="button">
									<td class="text-center d-none">
										<input type="checkbox" class="select-row" data-cctv='${JSON.stringify({
											nama_cctv: e.nama_cctv,
										})}'>
									</td>
									<td class="text-center">
										<button type="button" class="btn btn-sm btn-outline-light numbers-cctv" disabled>${
											no + 1
										}</button>
									</td>
									<td class="cctv-name" style="cursor: pointer;">
										${e.nama_cctv}
									</td>
								</tr>`;
			});

			$("#t_list_cctv_objek_vital").html(html_table_cctv);
		} else {
			let html_table_cctv = `<tr class='text-white'><td colspan="3" class="text-center">Tidak ada cctv objek vital</td></tr>`;
			$("#t_list_cctv_objek_vital").html(html_table_cctv);
		}
	})
	.catch((error) => {
		console.log("Error", error);
	});
}

function dash_traffic_crowd_counting_cctv_multiple(
	nama_cctv = {},
	filter = {}
) {
	cleartimeout();
	const filter_date = g_start_filter || g_end_filter ? {start_date: g_start_filter.format("YYYY-MM-DD"), end_date: g_end_filter.format("YYYY-MM-DD")} : filter;
	let cctv = nama_cctv.length > 0 ? nama_cctv.map((item) => item.nama_cctv).join("','") : nama_cctv;
	let param1 = {
		jenis_chart: 5,
		start_date: date_time_filter(filter_date).start_date,
		end_date: date_time_filter(filter_date).end_date,
		polda: paramsPolda ? paramsPolda : global_polda,
	};

	let param2 = {
		jenis_chart: 30,
		start_date: date_time_filter(filter_date).start_date,
		end_date: date_time_filter(filter_date).end_date,
		polda: paramsPolda ? paramsPolda : global_polda,
	};

	let c_polres_id;
	cctv.length > 0 ? (param1["nama_cctv"] = cctv) : "";
	cctv.length > 0 ? (param2["nama_cctv"] = cctv) : "";
	paramsPolres ? (param1["polres"] = paramsPolres) : c_polres_id;
	paramsPolres ? (param2["polres"] = paramsPolres) : c_polres_id;
	
	if ($("#f-polres").val() != undefined) {
		if ($("#f-polres").val() != "") {
			param1.polres = $("#f-polres").val().split(",")[0];
			param2.polres = $("#f-polres").val().split(",")[0];
			c_polres_id = $("#f-polres").val().split(",")[1];
		}
	}

	let filter_fifty = filter_obj(param1);
	let filter_thirty = filter_obj(param2);

	$("#konten-kanan").html(``);
	$("#konten-kanan").html(`
		<div class="d-flex justify-content-center align-items-center vh-100" id="load-kanan">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
		<div class="d-none" id="display-kanan">
			<div class="mt-3">
				<div class="d-flex justify-content-center mx-2 btn" onclick="toggleKanan()">
					<h4 class="text-white">TOTAL</h4>
				</div>
				<div class="row">
					<div class="col mt-1 p-1">
						<div class="card text-white text-center card-bt-biru">
							<div class="card-body">
								<h3 id="TotalGenderFiveMinute"></h3>
								<h3 class="d-none" id="TotalGenderThirtyMinute"></h3>
							</div>
						</div>
					</div>
				</div>
			</div>
			<ul class="nav nav-tabs nav-fill mt-4" id="tabIN" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="five-minute-tab-in" data-bs-toggle="tab" data-bs-target="#five-minute-in" type="button" role="tab" aria-controls="five-minute-in" aria-selected="true">PER 5 MENIT</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="thirty-minute-tab-in" data-bs-toggle="tab" data-bs-target="#thirty-minute-in" type="button" role="tab" aria-controls="thirty-minute-in" aria-selected="false">PER 30 MENIT</button>
				</li>
			</ul>
			<div class="tab-content" id="tabInContent">
				<div class="tab-pane fade show active p-3" id="five-minute-in" role="tabpanel" aria-labelledby="five-minute-tab-in">
					<div class="d-flex flex-column vh-100">
						<div class="mt-4">
							<div id="chart-obj-total-five-minute" style="height:200px"></div>
							<div class="badge bg-primary text-wrap d-flex justify-content-center" id="highestChartFiveMinute"></div>
						</div>
						<div class="d-flex flex-wrap mt-4">
							<div class="p-2">
								<span class="d-flex align-items-center ">
									<img src="${global_url_ci}/my/logo/pria.png" width="40" alt=""> 
									<h4 class="text-white ms-3">PRIA
										<span class="d-block" id="totalPriaFiveMinute"></span>
									</h4>
								</span>
							</div>
							<div class="p-2">
								<span class="d-flex align-items-center "><img src="${global_url_ci}/my/logo/wanita.png" width="40" alt=""> 
									<h4 class="text-white ms-3">WANITA
										<span class="d-block" id="totalWanitaFiveMinute"></span>
									</h4>
								</span>
							</div>
						</div>
						<div class="mt-4">
							<h4 class="text-white">GIAT MASYARAKAT</h4>
							<table class="table table-hover-custom custom-table">
								<tbody id="t_giat_masyarakat_1"></tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="tab-pane fade p-2" id="thirty-minute-in" role="tabpanel" aria-labelledby="thirty-minute-tab-in">
					<div class="d-flex flex-column vh-100">
						<div class="mt-4">
							<div id="chart-obj-total-thirty-minute" style="height:200px"></div>
							<div class="badge bg-primary text-wrap d-flex justify-content-center" id="highestChartThirtyMinute"></div>
						</div>
						<div class="d-flex flex-wrap mt-4">
							<div class="p-2">
								<span class="d-flex align-items-center ">
									<img src="${global_url_ci}/my/logo/pria.png" width="40" alt=""> 
									<h4 class="text-white ms-3">PRIA
										<span class="d-block" id="totalPriaThirtyMinute"></span>
									</h4>
								</span>
							</div>
							<div class="p-2">
								<span class="d-flex align-items-center "><img src="${global_url_ci}/my/logo/wanita.png" width="40" alt=""> 
									<h4 class="text-white ms-3">WANITA
										<span class="d-block" id="totalWanitaThirtyMinute"></span>
									</h4>
								</span>
							</div>
						</div>
						<div class="mt-4">
							<h4 class="text-white">GIAT MASYARAKAT</h4>
							<table class="table table-hover-custom custom-table">
								<tbody id="t_giat_masyarakat_2"></tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	`);

	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_traffic/object_vital_min?${filter_fifty}`
	).then((data) => {
		$("#load-kanan").addClass("d-none");
		$("#display-kanan").removeClass("d-none");
		$("#TotalGenderFiveMinute").text(data.data.total);
		$("#totalPriaFiveMinute").text(data.data.total_pria);
		$("#totalWanitaFiveMinute").text(data.data.total_wanita);
		$("#highestChartFiveMinute").text(data.data.grafik.dataTeratas);
		line_chart_pemkot(
			data.data.grafik,
			"#chart-obj-total-five-minute",
			"render",
			"grafik_people",
			"#1C75BC"
		);
	});

	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_traffic/object_vital_min?${filter_thirty}`
	).then((data) => {
		$("#TotalGenderThirtyMinute").text(data.data.total);
		$("#totalPriaThirtyMinute").text(data.data.total_pria);
		$("#totalWanitaThirtyMinute").text(data.data.total_wanita);
		$("#highestChartThirtyMinute").text(data.data.grafik.dataTeratas);
		line_chart_pemkot(
			data.data.grafik,
			"#chart-obj-total-thirty-minute",
			"render",
			"grafik_people",
			"#1C75BC"
		);
	});

	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_mobile_officer/laphar_opsdal?tgl_start=${date_time_filter(filter).start_date}&tgl_end=${date_time_filter(filter).end_date}&laphar=DATA PAM GIAT MASYARAKAT`
	).then((data) => {
		data = data.data;
		if (data.length > 0) {
			data.forEach((v, no) => {
				$("#t_giat_masyarakat_1").append(`
					<tr>
					<td>${no + 1}</td>
					<td>${v.jenis_kegiatan}</td>
					<td>${v.jumlah_kegiatan}</td>
					</tr>
				`);

				$("#t_giat_masyarakat_2").append(`
					<tr>
					<td>${no + 1}</td>
					<td>${v.jenis_kegiatan}</td>
					<td>${v.jumlah_kegiatan}</td>
					</tr>
				`);
			});
		} else {
			$("#t_giat_masyarakat_1").append(`
				<tr>
					<td>TIDAK ADA PAM GIAT MASYARAKAT</td>
				</tr>
			`);
			$("#t_giat_masyarakat_2").append(`
				<tr>
					<td>TIDAK ADA PAM GIAT MASYARAKAT</td>
				</tr>
			`);
		}
	});

	$("#five-minute-tab-in").on("click", function () {
		$("#TotalGenderThirtyMinute").addClass("d-none");
		$("#TotalGenderFiveMinute").removeClass("d-none");
	});

	$("#thirty-minute-tab-in").on("click", function () {
		$("#TotalGenderFiveMinute").addClass("d-none");
		$("#TotalGenderThirtyMinute").removeClass("d-none");
	});

	t_out_traffic_crowd_counting_cctv_multiple = setTimeout(() => {
		if (selectedCCTVs.length > 0 || nama_cctv) {
			dash_traffic_crowd_counting_cctv_multiple(selectedCCTVs, {
				start_date: date_time_filter(filter_date).start_date,
				end_date: date_time_filter(filter_date).end_date,
			});
		} else {
			dash_traffic_crowd_counting_cctv_multiple([], {
				start_date: date_time_filter(filter_date).start_date,
				end_date: date_time_filter(filter_date).end_date,
			});
		}
	}, 300000);
}

// Event listener for clicking the CCTV name
$(document).on("click", ".cctv-name", function () {
	const checkbox = $(this).siblings("td").find(".select-row");
	checkbox.prop("checked", !checkbox.prop("checked")).trigger("change");
});

// Event listener for checkbox change
$(document).on("change", ".select-row", function () {
	const cctvData = $(this).data("cctv");

	if ($(this).prop("checked")) {
		selectedCCTVs.push(cctvData);
	} else {
		selectedCCTVs = selectedCCTVs.filter(
			(item) => item.nama_cctv !== cctvData.nama_cctv
		);
	}

	// Update row style
	$(".select-row").each(function () {
		const cctvRow = $(this).closest("tr");
		const cctvNameCell = cctvRow.find(".numbers-cctv");

		if ($(this).prop("checked")) {
			cctvNameCell.removeClass("btn-outline-light").addClass("btn-info");
		} else {
			cctvNameCell.removeClass("btn-info").addClass("btn-outline-light");
		}
	});

	const date = new Date();
	dash_traffic_crowd_counting_cctv_multiple(selectedCCTVs, {
		start_date: formatDate(date),
		end_date: formatDate(date),
	});
});

function formatDate(date) {
	return date.toISOString().split("T")[0]; // Extracts the date part (YYYY-MM-DD)
}
