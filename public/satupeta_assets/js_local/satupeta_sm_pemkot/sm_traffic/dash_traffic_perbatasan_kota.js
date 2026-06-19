function dash_traffic_perbatasan_kota(filter = {}) {
  // add_geo(geojsonFiles)

  trafficLayer = new google.maps.TrafficLayer();
  trafficLayer.setMap(map);
  prom_clearMarkerCctvDum().then(function (d) {
    cctv_arr_dum.forEach(item => {
      if (!list_arr_cctv[item.category]) {
        list_arr_cctv[item.category] = [];
      }
    
      list_arr_cctv[item.category].push(item);
    });
  });

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

  let c_polres_id
  let param1 = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		polda_id: paramsPolda,
		status_cctv: "IN",
		category: "BATAS KOTA",
		// kategori_cctv: 1,
		// in_out: 'in'
		// polda:'',
		// status_cctv:'IN',
		// category:'BATAS KOTA'
	};

  let param2 = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		polda_id: paramsPolda,
		status_cctv: "OUT",
		category: "BATAS KOTA",
		// kategori_cctv: 1,
		// in_out: "out",
		// polda:'',
		// status_cctv:'OUT',
		// category:'BATAS KOTA'
	};

	paramsPolres ? (param1["polres"] = paramsPolres) : "";
	paramsPolres ? (param2["polres"] = paramsPolres) : "";
  if ($('#f-polres').val() != undefined) {
    if ($('#f-polres').val() != '') {
      param1.polres = $('#f-polres').val().split(',')[0]
      param2.polres = $('#f-polres').val().split(',')[0]
      c_polres_id = $('#f-polres').val().split(',')[1]
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

	set_all_cctv(
		paramsPoldaId != "" ? paramsPoldaId : global_polda_id,
		paramsPolresId != "" ? paramsPolresId : c_polres_id,
		"2",
		"traffic_batas_kota",
		"only_live"
	);

  t_out_traffic_batas_kota =setTimeout(() => {
    // clearTimeout(t_out_traffic_batas_kota)
    $('#konten-kiri').html(``);
    $('#konten-kanan').html(``);
    dash_traffic_perbatasan_kota({start_date: date_time_filter(filter).start_date, end_date:date_time_filter(filter).end_date})
    // hapus_geo()
  }, 300000);
}

function dash_traffic_perbatasan_kota_cctv(nama_cctv='', filter= {}) {
	cleartimeout();

	// Konten Kiri
	$("#konten-kiri").html(``);
	$("#konten-kiri").append(`
    <div class="mx-3 mt-4">
      <span class="d-flex align-items-center justify-content-between btn" onclick="toggleKiri()">
        <h4 class="text-white text-left">KENDARAAN MASUK ${'- ' + nama_cctv.toUpperCase()} </h4>
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
	$("#konten-kanan").html(``);
	$("#konten-kanan").append(`
    <div class="mx-3 mt-4">
      <span class="d-flex align-items-center justify-content-between btn" onclick="toggleKanan()">
        <h4 class="text-white text-left">KENDARAAN KELUAR ${'- ' + nama_cctv.toUpperCase()} </h4>
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

	let c_polres_id;
  let param1 = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		polda_id: global_polda,
		status_cctv: "IN",
		category: "BATAS KOTA",
	};

	let param2 = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		polda_id: global_polda,
		status_cctv: "OUT",
		category: "BATAS KOTA",
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
		`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=5&${filter_in}&nama_cctv=${nama_cctv}`
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
			`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=30&${filter_in}&nama_cctv=${nama_cctv}`
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
		`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=5&${filter_out}&nama_cctv=${nama_cctv}`
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
			`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas_min?jenis_chart=30&${filter_out}&nama_cctv=${nama_cctv}`
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

	// // konten kiri
	// getDataSmNew(`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas?${filter_in}&nama_cctv=${nama_cctv}`)
	// .then(data => {
	//   $('#konten-kiri').append(`
	//   <div class="mt-4">
	//     <span class="d-flex align-items-center justify-content-center"><h4 class="text-white ms-3 text-left">KENDARAAN MASUK </h4><img src="${global_url_ci}my/logo/in.png" width="50" alt=""></span>
	//     <div class="row">
	//       <div class="col mt-1 p-1">
	//         <div class="card text-white text-center card-bt-biru">
	//           <div class="card-body">
	//             <h6 class="mb-2 fw-lighter">TOTAL</h6>
	//             <h3 class="">${data.data.total}</h3>
	//           </div>
	//         </div>
	//       </div>
	//     </div>
	//   </div>
	//   <div class="mt-4">
	//     <h5 class="text-white text-center font-bold">
	//         GRAFIK KENDARAAN MASUK ${data.data.grafik_hourly.title}
	//     </h5>
	//     <div id="chart-in-traffic" style="height:200px;"></div>
	//     <div class="badge bg-primary text-wrap">
	//       <h6>${data.data.grafik_hourly.dataTeratas}</h6>
	//     </div>
	//   </div>
	//   <div class="mt-4">
	//     <h5 class="text-white text-center font-bold mb-4">
	//         JUMLAH JENIS KENDARAAN MASUK
	//     </h5>
	//     <div class="row align-items-start mb-3 ms-3">
	//       <div class="col-3"><img src="${global_url_ci}my/logo/motor.svg" alt=""></div>
	//       <div class="col-3"><h5 class="text-white fw-lighter">Motor</h5></div>
	//       <div class="col-6"><h5 class="text-white ms-4">${data.data.total_motor}</h5></div>
	//     </div>
	//     <div class="row align-items-start mb-3 ms-3">
	//       <div class="col-3"><img src="${global_url_ci}my/logo/mobil.svg" alt=""></div>
	//       <div class="col-3"><h5 class="text-white fw-lighter">Minibus</h5></div>
	//       <div class="col-6"><h5 class="text-white ms-4">${data.data.total_minibus}</h5></div>
	//     </div>
	//     <div class="row align-items-start mb-3 ms-3">
	//       <div class="col-3"><img src="${global_url_ci}my/logo/bus.svg" alt=""></div>
	//       <div class="col-3"><h5 class="text-white fw-lighter">Bus</h5></div>
	//       <div class="col-6"><h5 class="text-white ms-4">${data.data.total_bus}</h5></div>
	//     </div>
	//     <div class="row align-items-start mb-3 ms-3">
	//       <div class="col-3"><img src="${global_url_ci}my/logo/truk.svg" alt=""></div>
	//       <div class="col-3"><h5 class="text-white fw-lighter">Truk</h5></div>
	//       <div class="col-6"><h5 class="text-white ms-4">${data.data.total_truck}</h5></div>
	//     </div>
	//   </div>
	// `)
	//   line_chart_pemkot(data.data.grafik_hourly,'#chart-in-traffic','render','grafik_kendaraan','#1C75BC')
	// })

	// // konten kanan
	// getDataSmNew(`${global_url_api}smart_dashboard/smart_traffic/lalu_lintas?${filter_out}&nama_cctv=${nama_cctv}`)
	// .then(data => {
	//   $('#konten-kanan').append(`
	//   <div class="mt-4">
	//     <span class="d-flex align-items-center justify-content-center"><img src="${global_url_ci}my/logo/out.png" width="50" alt=""> <h4 class="text-white ms-3 text-end">KENDARAAN KELUAR</h4></span>
	//     <div class="row">
	//       <div class="col mt-1 p-1">
	//         <div class="card text-white text-center card-bt-merah">
	//           <div class="card-body">
	//             <h6 class="mb-2 fw-lighter">TOTAL</h6>
	//             <h3 class="">${data.data.total}</h3>
	//           </div>
	//         </div>
	//       </div>
	//     </div>
	//   </div>
	//   <div class="mt-4">
	//     <h5 class="text-white text-center font-bold">
	//         GRAFIK KENDARAAN KELUAR ${data.data.grafik_hourly.title}
	//     </h5>
	//     <div id="chart-out-traffic" style="height:200px"></div>
	//     <div class="badge bg-danger text-wrap">
	//       <h6>${data.data.grafik_hourly.dataTeratas}</h6>
	//     </div>
	//   </div>
	//   <div class="mt-4">
	//     <h5 class="text-white text-center font-bold mb-4">
	//         JUMLAH JENIS KENDARAAN KELUAR
	//     </h5>
	//     <div class="row align-items-start mb-3 ms-3">
	//       <div class="col-3"><img src="${global_url_ci}my/logo/motor.svg" alt=""></div>
	//       <div class="col-3"><h5 class="text-white fw-lighter">Motor</h5></div>
	//       <div class="col-6"><h5 class="text-white ms-4">${data.data.total_motor}</h5></div>
	//     </div>
	//     <div class="row align-items-start mb-3 ms-3">
	//       <div class="col-3"><img src="${global_url_ci}my/logo/mobil.svg" alt=""></div>
	//       <div class="col-3"><h5 class="text-white fw-lighter">Minibus</h5></div>
	//       <div class="col-6"><h5 class="text-white ms-4">${data.data.total_minibus}</h5></div>
	//     </div>
	//     <div class="row align-items-start mb-3 ms-3">
	//       <div class="col-3"><img src="${global_url_ci}my/logo/bus.svg" alt=""></div>
	//       <div class="col-3"><h5 class="text-white fw-lighter">Bus</h5></div>
	//       <div class="col-6"><h5 class="text-white ms-4">${data.data.total_bus}</h5></div>
	//     </div>
	//     <div class="row align-items-start mb-3 ms-3">
	//       <div class="col-3"><img src="${global_url_ci}my/logo/truk.svg" alt=""></div>
	//       <div class="col-3"><h5 class="text-white fw-lighter">Truk</h5></div>
	//       <div class="col-6"><h5 class="text-white ms-4">${data.data.total_truck}</h5></div>
	//     </div>
	//   </div>
	// `)
	//   line_chart_pemkot(data.data.grafik_hourly,'#chart-out-traffic','render','grafik_kendaraan','#BC1C1C')
	// })

	t_out_traffic_batas_kota_cctv = setTimeout(() => {
		if (nama_cctv != "") {
			dash_traffic_perbatasan_kota_cctv(nama_cctv, {
				start_date: date_time_filter(filter).start_date,
				end_date: date_time_filter(filter).end_date,
			});
		} else {
			dash_traffic_perbatasan_kota_cctv("", {
				start_date: date_time_filter(filter).start_date,
				end_date: date_time_filter(filter).end_date,
			});
		}
	}, 300000);
}