let tr_active = null
function dash_traffic_dalam_kota(filter={}) {
	trafficLayer = new google.maps.TrafficLayer();
	trafficLayer.setMap(map);

	// Konten Kiri
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

	// Konten kanan
	$("#konten-kanan").append(`
    <div class="mx-3 mt-4">
      <span class="d-flex align-items-center justify-content-between btn" onclick="toggleKanan()">
        <h4 class="text-white text-left">KENDARAAN KELUAR </h4>
        <img src="${global_url_ci}/my/logo/out.png" width="50" alt="">
      </span>
      <div class="row">
        <div class="col mt-1 p-1">
          <div class="card text-white text-center card-bt-merah">
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

  let c_polres_id;
	let param1 = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		status_cctv: "IN",
		polda: paramsPolda,
		category: "DALAM KOTA",
	};

	let param2 = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		status_cctv: "OUT",
		polda: paramsPolda,
		category: "DALAM KOTA",
	};
  
	paramsPolres ? (param1["polres"] = paramsPolres) : "";
	paramsPolres ? (param2["polres"] = paramsPolres) : "";
	if ($("#f-polres").val() != undefined) {
		if ($("#f-polres").val() != "") {
			param1.polres = $("#f-polres").val().split(",")[0];
			param2.polres = $("#f-polres").val().split(",")[0];
			c_polres_id = $("#f-polres").val().split(",")[1];
		}
	}

	let filter_in = filter_obj(param1);
	let filter_out = filter_obj(param2);

	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=5&${filter_in}`
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
			`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=30&${filter_in}`
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
		`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=5&${filter_out}`
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
			`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=30&${filter_out}`
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

	// konten kiri
	// getDataSmNew(`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_dalam_kota?${filter_}`)
	// .then(data => {
	//   $("#konten-kiri").html("");
	//   hapus_geo()
	//   $('#konten-kanan').html('');
	//   $('#konten-kiri').append(`
	//     <div class="mt-4">
	//       <h4 class="text-white ms-3">KOTA DENGAN KEPADATAN LALU LINTAS TERTINGGI</h4>
	//       <div class="mt-4">
	//         <table class="table table-hover-custom custom-table">
	//           <thead>
	//             <tr>
	//               <th class="text-center">No</th>
	//               <th>Kota</th>
	//               <th class="text-center">Jumlah Terdeteksi</th>
	//             </tr>
	//           </thead>
	//           <tbody id="t_top_lalin_kota">
	//           </tbody>
	//         </table>
	//       </div>
	//     </div>
	//   `)

	//   $("#konten-kanan").append(`
	//     <div class="mt-4">
	//         <div id="chart-lalin-tertinggi" style="height:200px;"></div>
	//         <div class="badge bg-primary text-wrap">
	//           <h6>${data.data.grafik_hourly.dataTeratas}</h6>
	//         </div>
	//     </div>
	//     <div class="mt-4">
	//       <h5 class="text-white text-center font-bold mb-4">
	//           LALU LINTAS BERDASARKAN JENIS KENDARAAN
	//       </h5>
	//       <div class="d-flex justify-content-between align-items-center mb-3">
	//         <div class="d-flex align-items-center">
	//           <img src="${global_url_ci}my/logo/motor.svg" width="40" alt="" />
	//           <h6 class="text-white fw-lighter ms-2">MOTOR</h6>
	//         </div>
	//         <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
	// 					data.data.total_motor
	// 				)}</h5>
	//       </div>
	//       <div class="d-flex justify-content-between align-items-center mb-3">
	//         <div class="d-flex align-items-center">
	//           <img src="${global_url_ci}my/logo/mobil.svg" width="40" alt="" />
	//           <h6 class="text-white fw-lighter ms-2">MINIBUS</h6>
	//         </div>
	//         <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
	// 					data.data.total_minibus
	// 				)}</h5>
	//       </div>
	//       <div class="d-flex justify-content-between align-items-center mb-3">
	//         <div class="d-flex align-items-center">
	//           <img src="${global_url_ci}my/logo/bus.svg" width="40" alt="" />
	//           <h6 class="text-white fw-lighter ms-2">BUS</h6>
	//         </div>
	//         <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
	// 					data.data.total_bus
	// 				)}</h5>
	//       </div>
	//       <div class="d-flex justify-content-between align-items-center mb-3">
	//         <div class="d-flex align-items-center">
	//           <img src="${global_url_ci}my/logo/truk.svg" width="40" alt="" />
	//           <h6 class="text-white fw-lighter ms-2">TRUK</h6>
	//         </div>
	//         <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
	// 					data.data.total_truck
	// 				)}</h5>
	//       </div>
	//     </div>
	//   `);

	//   // let legend_map = []
	//   //   for (let i = 0; i < data.data.legend_map.length; i++) {
	//   //     legend_map.push({"url" : `${global_url_ci}my/geo/jateng/surakarta/`+data.data.legend_map[i].kecamatan.replace(" ", "_")+".geojson","color":data.data.legend_map[i].warna})
	//   //   }
	//   // add_geo(legend_map)

	//   add_geo(geojsonFiles)

	//   for (let a = 0; a < data.data.list_cctv_event.length; a++) {
	//     heatmapDataLalin.push({location: new google.maps.LatLng(parseFloat(data.data.list_cctv_event[a].lat), parseFloat(data.data.list_cctv_event[a].lng)), weight:parseInt(data.data.list_cctv_event[a].jumlah)})
	//   }
	//   // Buat objek heatmap
	//   heatmap_lalin = new google.maps.visualization.HeatmapLayer({
	//     data: heatmapDataLalin.map(item => ({
	//       location: item.location,
	//       weight: item.weight,
	//     })),
	//     map: map,
	//     radius: 50
	//   });

	//   line_chart_pemkot(data.data.grafik_hourly,'#chart-lalin-tertinggi','render','grafik_kendaraan','#1C75BC')

	//   $('#t_top_lalin_kota').html('');
	//   data.data.tabel_lalin_tertinggi.forEach((e,no) => {
	//     $('#t_top_lalin_kota').append(`
	//       <tr data-key='${no+1}' class="" ${paramsPolres ? "" : `onclick="dash_traffic_dalam_kota_detail('${e.kota}',{})"`} style="cursor:pointer;">
	//         <td class="text-center"><button type="button" class="btn btn-sm btn-outline-light" disabled>${no+1}</button></td>
	//         <td>${paramsPolres ? e.kecamatan : e.kota}</td>
	//         <td class="text-center">${formatNumberWithThousandSeparator(e.jumlah)}</td>
	//       </tr>
	//     `)
	//   });
	// })

	set_all_cctv(
		paramsPoldaId != "" ? paramsPoldaId : global_polda_id,
		paramsPolresId != "" ? paramsPolresId : c_polres_id,
		"5",
		"",
		""
	);

	t_out_traffic_dalam_kota = setTimeout(() => {
		// clearTimeout(t_out_traffic_dalam_kota)
		$("#konten-kiri").html(``);
		$("#konten-kanan").html(``);
		dash_traffic_dalam_kota({
			start_date: date_time_filter(filter).start_date,
			end_date: date_time_filter(filter).end_date,
		});
		// hapus_geo();
	}, 300000);
}

function dash_traffic_dalam_kota_detail(val, filter ={}) {
  tr_active = val
  if (filter.start_date == undefined) {
    $('#konten-filter').html(`
      <div id="reportrange" data-key="traffic_${val.toLowerCase().replace(" ","_")}" class="bg-primary text-white filter-custom">
          <i class="fa fa-calendar"></i>&nbsp;
          <span></span> <i class="fa fa-caret-down"></i>
      </div>
    `)
    
    filter_range(val)
  }
  // fetch(`${global_url_ci}/my/geo/diy/${val.toLowerCase().replace(" ","_")}.geojson`)
  //     .then(response =>  response.json())
  //     .then(data => {
        // map.setZoom(15)
        // map.setCenter(new google.maps.LatLng(data.geometry.coordinates_center[0], data.geometry.coordinates_center[1]));
        getDataSmNew(`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_dalam_kota/detail?start_date=${date_time_filter(filter).start_date}&end_date=${date_time_filter(filter).end_date}&polda=${global_polda}&category=DALAM KOTA&kota=${val}`)
        .then(data => {

          $('#konten-kiri').html('');
          // hapus_geo();
          $('#konten-kiri').append(`
          <div class="mt-4">
            <h4 class="text-white ms-3">KOTA DENGAN KEPADATAN LALU LINTAS TERTINGGI</h4>
            <div class="mt-4">
              <table class="table table-hover custom-table">
                <thead>
                  <tr>
                    <th class="text-center">No</th>
                    <th>Kota</th>
                    <th class="text-center">Jumlah Terdeteksi</th>
                  </tr>
                </thead>
                <tbody id="t_top_lalin_kota_detail">
                </tbody>
              </table>
            </div>
          </div>
          <div class="mt-4">
        <div id="chart-lalin-tertinggi" style="height:200px;"></div>
            <div class="badge bg-primary text-wrap">
              <h6>${data.data.grafik_hourly.dataTeratas}</h6>
            </div>
        </div>
        <div class="mt-4">
          <h5 class="text-white text-center font-bold mb-4">
              LALU LINTAS BERDASARKAN JENIS KENDARAAN
          </h5>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/motor.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">MOTOR</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
              data.data.total_motor
            )}</h5>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/mobil.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">MINIBUS</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
              data.data.total_minibus
            )}</h5>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/bus.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">BUS</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
              data.data.total_bus
            )}</h5>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
              <img src="${global_url_ci}my/logo/truk.svg" width="40" alt="" />
              <h6 class="text-white fw-lighter ms-2">TRUK</h6>
            </div>
            <h5 class="text-white ms-4">${formatNumberWithThousandSeparator(
              data.data.total_truck
            )}</h5>
          </div>
        </div>
          `)
      
          // add_geo(geojsonFiles)
      
          for (let a = 0; a < data.data.list_cctv_event.length; a++) {
            heatmapDataLalin.push({location: new google.maps.LatLng(parseFloat(data.data.list_cctv_event[a].lat), parseFloat(data.data.list_cctv_event[a].lng)), weight:parseInt(data.data.list_cctv_event[a].jumlah)})
          }
          // Buat objek heatmap (menggunakan Circle overlay, HeatmapLayer sudah deprecated di v3.65+)
          heatmap_lalin = createHeatmapCircles(map, heatmapDataLalin, 300);
      
          line_chart_pemkot(data.data.grafik_hourly,'#chart-lalin-tertinggi','render','grafik_kendaraan','#1C75BC')

          $('#t_top_lalin_kota_detail').html('');
            data.data.tabel_lalin_tertinggi.forEach((e,no) => {

              $('#t_top_lalin_kota_detail').append(`
                <tr data-key='${e.kota}' class="${ tr_active == e.kota ? `table-tr-active`: ``}" style="cursor:pointer;">
                  <td class="text-center"><button type="button" class="btn btn-sm btn-outline-light" disabled>${no+1}</button></td>
                  <td>${paramsPolres ? e.kecamatan : e.kota}</td>
                  <td class="text-center">${formatNumberWithThousandSeparator(e.jumlah)}</td>
                </tr>
              `)
            });

            $('#t_top_lalin_kota_detail tr').on('click', function() {
                // Mengecek apakah elemen <tr> memiliki kelas 'table-tr-active'
                const isHighlighted = $(this).hasClass('table-tr-active');
            
                // Menjalankan fungsi berbeda tergantung pada keberadaan kelas 'table-tr-active'
                if (isHighlighted) {
                  fungsiKetikaHighlighted(this);
                } else {
                  fungsiKetikaTidakHighlighted(this);
                }
              });
            
              function fungsiKetikaHighlighted(elemen) {
                // Tambahkan logika atau perintah lain yang diinginkan
                // hapus_geo()
                cleartimeout()
                back_to()
                $('#konten-filter').html(`
                  <div id="reportrange" key="dalam_kota" class="bg-primary text-white filter-custom">
                      <i class="fa fa-calendar"></i>&nbsp;
                      <span></span> <i class="fa fa-caret-down"></i>
                  </div>
                `)
                filter_range()
                dash_traffic_dalam_kota({})
              }
            
              // Fungsi ketika elemen <tr> tidak memiliki kelas 'highlighted'
              function fungsiKetikaTidakHighlighted(elemen) {
                const key = $(elemen).data('key');
                // hapus_geo()
                cleartimeout()
                $('#konten-filter').html(`
                  <div id="reportrange" key="${key.replace(" ","_")}" class="bg-primary text-white filter-custom">
                      <i class="fa fa-calendar"></i>&nbsp;
                      <span></span> <i class="fa fa-caret-down"></i>
                  </div>
                `)
                filter_range(key.replace(" ","_"))
                dash_traffic_dalam_kota_detail(key,{})
              }
        })
        t_out_traffic_dalam_kota_detail =setTimeout(() => {
          // clearTimeout(t_out_traffic_dalam_kota)
          dash_traffic_dalam_kota_detail(val,{start_date: date_time_filter(filter).start_date, end_date:date_time_filter(filter).end_date})
          $('#konten-kiri').html('');
          $('#title-konten-tengah').html(`${global_app_name}`);
          $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Dalam Kota')
          // hapus_geo()
        }, 300000);
      // })
      // .catch(error => console.error('Error fetching GeoJSON:', error));
}