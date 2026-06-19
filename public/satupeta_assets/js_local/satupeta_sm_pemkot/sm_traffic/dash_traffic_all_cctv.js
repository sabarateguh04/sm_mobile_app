function dash_traffic_all_cctv(filter = {}) {
	trafficLayer = new google.maps.TrafficLayer();
	trafficLayer.setMap(map);

	$("#konten-kiri").append(`
    <div class="mx-3 mt-4">
      <span class="d-flex align-items-center justify-content-between btn" onclick="toggleKiri()">
        <h4 class="text-white text-left">KENDARAAN MASUK </h4>
        <img src="${global_url_ci}/my/logo/in.png" width="50" alt="">
      </span>
      <div class="row">
        <div class="col mt-1 p-1">
          <div class="card text-white text-center card-bt-biru">
            <div class="card-body">
              <h6 class="mb-2 fw-lighter">TOTAL KENDARAAN MASUK</h6>
              <h3 id="total_kendaraan_masuk_five"></h3>
              <h3 id="total_kendaraan_masuk_thirty"></h3>
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
      <div class="tab-pane fade show active p-2" id="five-minute-in" role="tabpanel" aria-labelledby="five-minute-tab-in">
          <div class="d-flex justify-content-center align-items-center vh-100 status_in">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
      </div>
      <div class="tab-pane fade p-2" id="thirty-minute-in" role="tabpanel" aria-labelledby="thirty-minute-tab-in">
          <div class="d-flex justify-content-center align-items-center vh-100 status_in">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
      </div>
    </div>
  `);
	$("#konten-kanan").append(`
    <div class="mx-3 mt-4">
      <span class="d-flex align-items-center justify-content-between btn" onclick="toggleKanan()">
        <h4 class="text-white text-left">KENDARAAN KELUAR </h4>
        <img src="${global_url_ci}/my/logo/out.png" width="50" alt="">
      </span>
      <div class="row">
        <div class="col mt-1 p-1">
          <div class="card text-white text-center card-bt-merah" >
            <div class="card-body">
              <h6 class="mb-2 fw-lighter">TOTAL KENDARAAN KELUAR</h6>
              <h3 id="total_kendaraan_keluar_five"></h3>
              <h3 id="total_kendaraan_keluar_thirty"></h3>
            </div>
          </div>
        </div>
      </div>
    </div>
    <ul class="nav nav-tabs nav-fill mt-4" id="tabOut" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="five-minute-tab-out" data-bs-toggle="tab" data-bs-target="#five-minute-out" type="button" role="tab" aria-controls="five-minute-out" aria-selected="true">PER 5 MENIT</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="thirty-minute-tab-out" data-bs-toggle="tab" data-bs-target="#thirty-minute-out" type="button" role="tab" aria-controls="thirty-minute-out" aria-selected="false">PER 30 MENIT</button>
      </li>
    </ul>
    <div class="tab-content" id="tabOutContent">
      <div class="tab-pane fade show active p-2" id="five-minute-out" role="tabpanel" aria-labelledby="five-minute-tab-out">
        <div class="d-flex justify-content-center align-items-center vh-100 status_out">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div class="tab-pane fade p-2" id="thirty-minute-out" role="tabpanel" aria-labelledby="thirty-minute-tab-out">
        <div class="d-flex justify-content-center align-items-center vh-100 status_out">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    </div>
  `);

	prom_clearMarkerCctvDum().then(function (d) {
		cctv_arr_dum.forEach((item) => {
			if (!list_arr_cctv[item.category]) {
				list_arr_cctv[item.category] = [];
			}

			list_arr_cctv[item.category].push(item);
		});
	});

	let param1 = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		polda: paramsPolda,
	};

	let param2 = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		polda: paramsPolda,
	};

	paramsPolres ? (param1["polres"] = paramsPolres) : "";
	paramsPolres ? (param2["polres"] = paramsPolres) : "";
	let filter_all_cctv = filter_obj(param2);
	let filter_ = filter_obj(param1);
	// add_geo(geojsonFiles);

	getDataSmNew(
		`${global_url_api}smart_dashboard/v2/smart_traffic/all_cctv?${filter_all_cctv}`
	).then((data) => {
		// if (paramsPolres) {
			// $("#bottom-content").addClass("d-none"); // Konten kiri
			// $("#konten-kiri").append(`
			//         <div class="mt-4">
			//           <div class="row">
			//             <div class="col">
			//               <div class="card text-white text-center" style="background: linear-gradient(90deg, rgba(15, 14, 30, 0.45) 0%, #0606D2 53%, rgba(15, 14, 30, 0.45) 100%) !important;">
			//                 <div class="card-body">
			//                   <h5>DALAM KOTA</h5>
			//                 </div>
			//               </div>
			//             </div>
			//           </div>
			//         </div>
			//         <div class="py-3" style="background:#000C1E;">
			//           <div class="w-100">
			//             <h6 class="text-white text-center font-bold mx-2">
			//                 KECAMATAN DENGAN VOLUME LALU LINTAS TERTINGGI
			//             </h6>
			//             <table class="table table-hover-custom custom-table mx-2">
			//               <thead style="background-color: #0d6efd !important">
			//                 <tr>
			//                   <th class="text-center">No</th>
			//                   <th>Kecamatan</th>
			//                   <th class="text-center">Volume</th>
			//                 </tr>
			//               </thead>
			//               <tbody id="t_top_cctv_dalam_kota"></tbody>
			//             </table>
			//           </div>
			//           <div class="w-100">
			//             <h6 class="text-white text-center font-bold">
			//                 GRAFIK KENDARAAN ${
			// 									data.data.dalam_kota.chart.hasOwnProperty("title")
			// 										? data.data.dalam_kota.chart.title
			// 										: "-"
			// 								}
			//             </h6>
			//             <div id="chart-dalam-kota" style="height:200px"></div>
			//           </div>
			//           <div class="w-100">
			//             <h6 class="text-white text-center font-bold">
			//                 LALU LINTAS BERDASARKAN JENIS KENDARAAN
			//             </h6>
			//             <div id="list_kendaraan" class="text-center"></div>
			//           </div>
			//         </div>
			// `);
		// } else {
			// Konten tengah bawah
			$("#bottom-content").append(`
        <div class="d-flex py-3" style="background:#000C1E;">
          <div class="w-100">
            <h6 class="text-white text-center font-bold mx-2">
                KECAMATAN DENGAN VOLUME LALU LINTAS TERTINGGI
            </h6>
            <table class="table table-hover-custom custom-table mx-2">
              <thead style="background-color: #0d6efd !important">
                <tr>
                  <th class="text-center">No</th>
                  <th>Kecamatan</th>
                  <th class="text-center">Volume</th>
                </tr>
              </thead>
              <tbody id="t_top_cctv_dalam_kota"></tbody>
            </table>
          </div>
          <div class="w-100">
            <h6 class="text-white text-center font-bold">
                GRAFIK KENDARAAN ${
									data.data.dalam_kota.chart.hasOwnProperty("title")
										? data.data.dalam_kota.chart.title
										: "-"
								}
            </h6>
            <div id="chart-dalam-kota" style="height:200px"></div>
          </div>
          <div class="w-100">
            <h6 class="text-white text-center font-bold">
                LALU LINTAS BERDASARKAN JENIS KENDARAAN
            </h6>
            <div id="list_kendaraan" class="text-center"></div>
          </div>
        </div>
      `);

			// Dalam Kota
			data.data.dalam_kota.details.forEach((e, no) => {
				var img;
				if (e.label == "BUS") {
					img = `${global_url_ci}my/logo/bus.svg`;
				} else if (e.label == "MOBIL PENUMPANG") {
					img = `${global_url_ci}my/logo/mobil.svg`;
				} else if (e.label == "MOBIL BARANG") {
					img = `${global_url_ci}my/logo/truk.svg`;
				} else if (e.label == "SEPEDA MOTOR") {
					img = `${global_url_ci}my/logo/motor.svg`;
				} else {
					img = `${global_url_ci}my/logo/motor.svg`;
				}

				if (e.label != "TOTAL") {
					$("#list_kendaraan").append(`
              <div class="row align-items-center mb-3 ms-3">
                  <div class="col-3"><img src="${img}" width="40"></div>
                  <div class="col-4"><h6 class="text-white fw-lighter">${
										e.label
									}</h6></div>
                  <div class="col-5"><h6 class="text-white ms-4">${formatNumberWithThousandSeparator(
										e.total
									)}</h6></div>
              </div>
          `);
				}
			});

			data.data.dalam_kota.highest.forEach((e, no) => {
				$("#t_top_cctv_dalam_kota").append(`
          <tr data-key='${no + 1}'>
          <td class="text-center"><button type="button" class="btn btn-sm btn-outline-light" disabled>${
						no + 1
					}</button></td>
          <td>${e.label}</td>
          <td class="text-center">${formatNumberWithThousandSeparator(
						e.total
					)}</td>
          </tr>
      `);
			});

			line_chart_pemkot(
				data.data.dalam_kota.chart,
				"#chart-dalam-kota",
				"render",
				"grafik_kendaraan",
				"#1C75BC"
			);
		// }
	});

	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=5&status_cctv=IN&${filter_}`
	).then((data) => {
		$("#total_kendaraan_masuk_five").text(``);
		$("#total_kendaraan_masuk_five").text(
			formatNumberWithThousandSeparator(data.data.sum.total)
		);

		$("#five-minute-in").html(``);
		$("#five-minute-in").html(
			`<div class="mt-4" id="chart-cctv-in-fivemin" style="height:200px;"></div>          
      <div class="mt-4">
        <h6 class="text-white text-center font-bold mb-4">
            JUMLAH JENIS KENDARAAN MASUK
        </h6>
        <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
          <div class="d-flex align-items-center">
            <img src="${global_url_ci}my/logo/motor.svg" width="40" alt="" />
            <h6 class="text-white fw-lighter ms-2">MOTOR</h6>
          </div>
          <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
						data.data.sum.motor
					)}</h5>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
          <div class="d-flex align-items-center">
            <img src="${global_url_ci}my/logo/mobil.svg" width="40" alt="" />
            <h6 class="text-white fw-lighter ms-2">MINIBUS</h6>
          </div>
          <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
						data.data.sum.minibus
					)}</h5>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
          <div class="d-flex align-items-center">
            <img src="${global_url_ci}my/logo/bus.svg" width="40" alt="" />
            <h6 class="text-white fw-lighter ms-2">BUS</h6>
          </div>
          <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
						data.data.sum.bus
					)}</h5>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
          <div class="d-flex align-items-center">
            <img src="${global_url_ci}my/logo/truk.svg" width="40" alt="" />
            <h6 class="text-white fw-lighter ms-2">TRUK</h6>
          </div>
          <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
						data.data.sum.truck
					)}</h5>
        </div>
      </div>`
		);

		line_chart_arus(data.data.chart, "#chart-cctv-in-fivemin", "#1C75BC");
		$(".load_in").addClass("d-none");
	});

	$("#five-minute-tab-in").on("click", function () {
		$("#total_kendaraan_masuk_five").removeClass("d-none");
		$("#total_kendaraan_masuk_thirty").addClass("d-none");
	});

	$("#thirty-minute-tab-in").on("click", function () {
		$(".load_in").removeClass("d-none");
		$("#total_kendaraan_masuk_five").addClass("d-none");
		$("#total_kendaraan_masuk_thirty").removeClass("d-none");
		getDataSmNew(
			`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=30&status_cctv=IN&${filter_}`
		).then((data) => {
			$("#total_kendaraan_masuk_thirty").text(``);
			$("#total_kendaraan_masuk_thirty").text(
				formatNumberWithThousandSeparator(data.data.sum.total)
			);

			$("#thirty-minute-in").html(``);
			$("#thirty-minute-in").html(
				`<div class="mt-4" id="chart-cctv-in-thirtymin" style="height:200px;"></div>          
        <div class="mt-4">
          <h6 class="text-white text-center font-bold mb-4">
              JUMLAH JENIS KENDARAAN MASUK
          </h6>
          <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/motor.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">MOTOR</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
							data.data.sum.motor
						)}</h5>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/mobil.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">MINIBUS</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
							data.data.sum.minibus
						)}</h5>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/bus.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">BUS</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
							data.data.sum.bus
						)}</h5>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/truk.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">TRUK</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
							data.data.sum.truck
						)}</h5>
          </div>
        </div>`
			);

			line_chart_arus(data.data.chart, "#chart-cctv-in-thirtymin", "#1C75BC");
			$(".load_in").addClass("d-none");
		});
	});

	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=5&status_cctv=OUT&${filter_}`
	).then((data) => {
		$("#total_kendaraan_keluar_five").text(``);
		$("#total_kendaraan_keluar_five").text(
			formatNumberWithThousandSeparator(data.data.sum.total)
		);

		$("#five-minute-out").html(``);
		$("#five-minute-out").html(
			`<div class="mt-4" id="chart-cctv-out-fivemin" style="height:200px;"></div>          
        <div class="mt-4">
          <h6 class="text-white text-center font-bold mb-4">
              JUMLAH JENIS KENDARAAN KELUAR
          </h6>
          <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/motor.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">MOTOR</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
							data.data.sum.motor
						)}</h5>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/mobil.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">MINIBUS</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
							data.data.sum.minibus
						)}</h5>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/bus.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">BUS</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
							data.data.sum.bus
						)}</h5>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/truk.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">TRUK</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
							data.data.sum.truck
						)}</h5>
          </div>
        </div>`
		);

		line_chart_arus(data.data.chart, "#chart-cctv-out-fivemin", "#1C75BC");
		$(".load_out").addClass("d-none");
	});

	$("#five-minute-tab-out").on("click", function () {
		$("#total_kendaraan_keluar_five").removeClass("d-none");
		$("#total_kendaraan_keluar_thirty").addClass("d-none");
	});

	$("#thirty-minute-tab-out").on("click", function () {
		$(".load_out").removeClass("d-none");
		$("#total_kendaraan_keluar_five").addClass("d-none");
		$("#total_kendaraan_keluar_thirty").removeClass("d-none");
		getDataSmNew(
			`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=30&status_cctv=OUT&${filter_}`
		).then((data) => {

			$("#total_kendaraan_keluar_thirty").text(``);
			$("#total_kendaraan_keluar_thirty").text(
				formatNumberWithThousandSeparator(data.data.sum.total)
			);

			$("#thirty-minute-out").html(``);
			$("#thirty-minute-out").html(
				`<div class="mt-4" id="chart-cctv-out-thirtymin" style="height:200px;"></div>          
          <div class="mt-4">
            <h6 class="text-white text-center font-bold mb-4">
                JUMLAH JENIS KENDARAAN KELUAR
            </h6>
            <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
              <div class="d-flex align-items-center">
                <img src="${global_url_ci}my/logo/motor.svg" width="40" alt="" />
                <h6 class="text-white fw-lighter ms-2">MOTOR</h6>
              </div>
              <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
								data.data.sum.motor
							)}</h5>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
              <div class="d-flex align-items-center">
                <img src="${global_url_ci}my/logo/mobil.svg" width="40" alt="" />
                <h6 class="text-white fw-lighter ms-2">MINIBUS</h6>
              </div>
              <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
								data.data.sum.minibus
							)}</h5>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
              <div class="d-flex align-items-center">
                <img src="${global_url_ci}my/logo/bus.svg" width="40" alt="" />
                <h6 class="text-white fw-lighter ms-2">BUS</h6>
              </div>
              <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
								data.data.sum.bus
							)}</h5>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3 mx-3">
              <div class="d-flex align-items-center">
                <img src="${global_url_ci}my/logo/truk.svg" width="40" alt="" />
                <h6 class="text-white fw-lighter ms-2">TRUK</h6>
              </div>
              <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
								data.data.sum.truck
							)}</h5>
            </div>
          </div>`
			);

			line_chart_arus(data.data.chart, "#chart-cctv-out-thirtymin", "#1C75BC");
			$(".load_out").removeClass("d-none");
		});
	});

	set_all_cctv(
		paramsPoldaId != "" ? paramsPoldaId : "",
		paramsPolresId != "" ? paramsPolresId : "",
		"",
		"",
		"only_live"
	);

	t_out_traffic_all_cctv = setTimeout(() => {
		$("#bottom-content").html(``);
		$("#konten-kiri").html(``);
		$("#konten-kanan").html(``);
		dash_traffic_all_cctv({
			start_date: date_time_filter(filter).start_date,
			end_date: date_time_filter(filter).end_date,
		});
		// hapus_geo();
	}, 300000);
}

function formatNumberWithThousandSeparator(number) {
	return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
