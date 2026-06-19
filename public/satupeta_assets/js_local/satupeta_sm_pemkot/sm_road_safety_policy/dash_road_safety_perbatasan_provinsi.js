function dash_road_safety_perbatasan_provinsi(filter = {}) {
	trafficLayer = new google.maps.TrafficLayer();
	trafficLayer.setMap(map);

	$("#konten-kiri").html(``);
	$("#konten-kanan").html(``);
	$("#bottom-content").html(``);

    $("#konten-kiri").html(`
		<div class="text-white pt-5">
			<div class="d-flex align-items-center justify-content-start gap-3 px-3">
				<img src="${global_url_ci}/my/logo/in.png" width="50" alt="">
				<div class="d-flex flex-column text-left">
					<h6 class="fw-normal">Total Kendaraan Masuk</h6>
					<div class="d-flex gap-2 position-relative">
						<h4 id="prediksi_masuk" class="text-white text-left"></h4>
						<img src="${global_url_ci}/my/satupeta/emergency.png" width="30" class="blinking d-none" id="alert_summaryIn" />
						<span class="d-flex align-items-center p-2 text-white position-absolute d-none" id="alert_hover_summaryIn">
							<img src="${global_url_ci}/my/satupeta/emergency.png" class="me-2" width="30">
							<small class="fw-semibold">Jumlah kendaraan masuk mencapai lebih dari <span id="vechileIn"></span> kendaraan. <b class="d-block">Arus lalu lintas akan diperkirakan macet.</b></small>
						</span>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column text-center mt-2">
				<ul class="nav nav-tabs nav-fill" id="tabIN" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="prediksi-in-tab" data-bs-toggle="tab" data-bs-target="#prediksi_in" type="button" role="tab" aria-controls="prediksi-in-tab" aria-selected="true">PREDIKSI</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="vcr-in-tab" data-bs-toggle="tab" data-bs-target="#tab_vcr_in" type="button" role="tab" aria-controls="vcr-in-tab" aria-selected="false">VC RASIO</button>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane fade show active px-2" id="prediksi_in" role="tabpanel">
						<div class="d-flex justify-content-center align-items-center mt-3">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					<div class="tab-pane fade px-2 overflow-hidden" id="tab_vcr_in" role="tabpanel">
						<div class="d-flex justify-content-center align-items-center mt-3 load_vcr_in">
							<div class="spinner-border text-primary" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column text-center px-2" id="kinerja_ruas_jalan_in"></div>
			<div class="d-flex flex-column px-2 mt-4" id="crowd_counting_content"></div>
			<div class="d-flex flex-column px-2 mt-4" id="pencemaran_udara_content"></div>
		</div>
	`);

    $("#konten-kanan").html(`
		<div class="text-white pt-5">
			<div class="d-flex align-items-center justify-content-end gap-3 px-3">
				<div class="d-flex flex-column text-right">
					<h6 class="fw-normal">Total Kendaraan Keluar</h6>
					<h4 id="prediksi_keluar" class="text-white text-end"></h4>
				</div>
				<img src="${global_url_ci}/my/logo/out.png" width="50" alt="">
			</div>
			<div class="d-flex flex-column text-center">
				<ul class="nav nav-tabs nav-fill mt-2" id="tabOUT" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="prediksi-out-tab" data-bs-toggle="tab" data-bs-target="#prediksi_out" type="button" role="tab" aria-controls="prediksi-out-tab" aria-selected="true">PREDIKSI</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="vcr-out-tab" data-bs-toggle="tab" data-bs-target="#tab_vcr_out" type="button" role="tab" aria-controls="vcr-out-tab" aria-selected="false">VC RASIO</button>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane fade show active px-2" id="prediksi_out" role="tabpanel">
						<div class="d-flex justify-content-center align-items-center mt-3">
							<div class="spinner-border text-danger" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					<div class="tab-pane fade px-2 overflow-hidden" id="tab_vcr_out" role="tabpanel">
						<div class="d-flex justify-content-center align-items-center mt-3 load_vcr_out">
							<div class="spinner-border text-danger" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column text-center px-2" id="kinerja_ruas_jalan_out"></div>
			<div class="d-flex flex-column px-2 mt-4" id="pelanggaran_content"></div>
			<div class="d-flex flex-column mt-4" id="top_mention_sentiment_content"></div>
		</div>
	`); 

    $("#bottom-content").html(`
		<div class="d-flex justify-content-between align-items-start text-white gap-2 px-3 pt-4">
			<div class="d-flex flex-column w-100">
				<h6 class="fw-normal text-center">INFO KEJADIAN</h6>
				<ul class="nav nav-tabs nav-fill mt-0" id="tabIN" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="object-vital-tab" data-bs-toggle="tab" data-bs-target="#info_kejadian" type="button" role="tab" aria-controls="info_kejadian-tab" aria-selected="false">HARI INI</button>
					</li>
				</ul>
				<div class="tab-content scroll-pane" style="height: 450px !important;overflow: auto;">
					<div class="tab-pane fade show active" id="info_kejadian" role="tabpanel" aria-labelledby="info_kejadian">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column w-100">
				<h6 class="fw-normal text-center">ANALISA WAKTU TEMPUH</h6>
				<ul class="nav nav-tabs nav-fill mt-0" id="tabIN" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="object-vital-tab" data-bs-toggle="tab" data-bs-target="#object_vital" type="button" role="tab" aria-controls="object_vital-tab" aria-selected="false">OBJEK VITAL</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="fasum-tab" data-bs-toggle="tab" data-bs-target="#fasum" type="button" role="tab" aria-controls="fasum-tab" aria-selected="false">FASUM</button>
					</li>
				</ul>
				<div class="tab-content scroll-pane" style="height: 450px !important;overflow: auto;">
					<div class="tab-pane fade show active" id="object_vital" role="tabpanel" aria-labelledby="object_vital">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					<div class="tab-pane fade show" id="fasum" role="tabpanel" aria-labelledby="fasum">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column w-100">
				<h6 class="fw-normal text-center">REKOMENDASI PENCEGAHAN</h6>
				<ul class="nav nav-tabs nav-fill mt-0" id="tabIN" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="rawan-laka-tab" data-bs-toggle="tab" data-bs-target="#rawan_laka" type="button" role="tab" aria-controls="rawan-laka-tab" aria-selected="false">RAWAN LAKA</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="rawan-langgar-tab" data-bs-toggle="tab" data-bs-target="#rawan_langgar" type="button" role="tab" aria-controls="rawan-langgar-tab" aria-selected="false">RAWAN LANGGAR</button>
					</li>
				</ul>
				<div class="tab-content scroll-pane" style="height: 450px !important;overflow: auto;">
					<div class="tab-pane fade show active" id="rawan_laka" role="tabpanel" aria-labelledby="rawan_laka">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					<div class="tab-pane fade show" id="rawan_langgar" role="tabpanel" aria-labelledby="rawan_langgar">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`);   
	
	// Chart Prediksi IN & OUT
	let filters_prediksi_in_out = {
		category: 'BATAS PROVINSI',
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
	};
	
	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_prediksi_in_out = {
			...filters_prediksi_in_out,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_prediksi_in_out = {
			...filters_prediksi_in_out,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_prediksi_in_out = {
			...filters_prediksi_in_out,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_prediksi_in_out = {
			...filters_prediksi_in_out,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_prediksi_in = filter_obj(filters_prediksi_in_out);
	let filter_prediksi_out = filter_obj(filters_prediksi_in_out);
	checkPeningkatanAlert(date_time_filter(filter).start_date, date_time_filter(filter).end_date)

	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/traffic/traffic_in?` + filter_prediksi_in
	).then((data) => {
		$("#prediksi_in").html(""); 
		if (data.data) {
			$("#prediksi_in").html(`
				<h6 class="fw-normal my-3">GRAFIK KENDARAAN MASUK</h6>
				<div id="chart-vechile-in" style="width:100%;"></div>
				<span class="alert-span-in d-flex justify-content-center rounded p-1 mb-2"><small class="fw-semibold" id="prediksi_teks_in"></small></span>
				<span class="bg-primary d-flex justify-content-center rounded p-1"><small class="fw-semibold" id="traffic_tinggi_in"></small></span>
			`);
			if (typeof data !== "undefined" && data !== null) {
				const congestionAlert = data.data.congestion_alert;
				const teksPrediksi = "Prediksi peningkatan lalin dalam 2 jam kedepan";
				const iconHtml = `<img src="${global_url_ci}/my/satupeta/emergency.png" class="me-2 blinking" />`;
				if (congestionAlert) {
					$("#prediksi_teks_in").html(teksPrediksi);
					$("#prediksi_teks_in").closest("span").prepend(iconHtml);
					$("#prediksi_teks_in").closest("span").show(); // Pastikan span tampil
					$("#alert_summaryIn").removeClass('d-none');
					$("#vechileIn").html(formatNumberWithThousandSeparator(data.data.total_kendaraan))
				} else {
					$("#prediksi_teks_in").closest("span").remove(); // Hapus span jika false
					$("#alert_summaryIn").addClass('d-none');
				}
			}
			line_chart_forecast(data.data, "#chart-vechile-in", "render", "#1E90FF");
		} else {
			$("#prediksi_in").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
		$("#prediksi_masuk").text(``);
		$("#prediksi_masuk").text(
			formatNumberWithThousandSeparator(data.data.total_kendaraan)
		);
		$("#traffic_tinggi_in").text(data.data.traffic_tertinggi);
		$(".load_in").addClass("d-none");
	});

	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/traffic/traffic_out?` + filter_prediksi_out
	).then((data) => {
		$("#prediksi_out").html(""); 
		if (data.data) {
			$("#prediksi_out").html(`
				<h6 class="fw-normal my-3">GRAFIK KENDARAAN KELUAR</h6>
				<div id="chart-vechile-out" style="width:100%;"></div>
				<span class="alert-span-out d-flex justify-content-center rounded p-1 mb-2"><small class="fw-semibold" id="prediksi_teks_out"></small></span>
				<span class="bg-danger d-flex justify-content-center rounded p-1"><small class="fw-semibold" id="traffic_tinggi_out"></small></span>
			`);
			if (typeof data !== "undefined" && data !== null) {
				const congestionAlert = data.data.congestion_alert;
				const teksPrediksi = "Prediksi peningkatan lalin dalam 2 jam kedepan";
				const iconHtml = `<img src="${global_url_ci}/my/satupeta/emergency.png" class="me-2 blinking" />`;
				if (congestionAlert) {
					$("#prediksi_teks_out").html(teksPrediksi);
					$("#prediksi_teks_out").closest("span").prepend(iconHtml);
					$("#prediksi_teks_out").closest("span").show(); // Pastikan span tampil
				} else {
					$("#prediksi_teks_out").closest("span").remove(); // Hapus span jika false
				}
			}
			line_chart_forecast(data.data, "#chart-vechile-out", "render", "#BC1C1C");
		} else {
			$("#prediksi_out").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
		$("#prediksi_keluar").text(``);
		$("#prediksi_keluar").text(
			formatNumberWithThousandSeparator(data.data.total_kendaraan)
		);
		$("#traffic_tinggi_out").text(data.data.traffic_tertinggi);
		$(".load_in").addClass("d-none");
	});

	// TOP MENTION & SENTIMENT
	let filters_mention_sentiment = {
		category: 'BATAS PROVINSI',
		tgl_start: date_time_filter(filter).start_date,
		tgl_end: date_time_filter(filter).end_date,
		source: 'facebook,instagram,twitter,reddit,youtube,news-blogs,web',
		alert_id: alert_id_global,
	};
	

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_mention_sentiment = {
			...filters_mention_sentiment,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_mention_sentiment = {
			...filters_mention_sentiment,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_mention_sentiment = {
			...filters_mention_sentiment,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_mention_sentiment = {
			...filters_mention_sentiment,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_top_mention = filter_obj(filters_mention_sentiment);
	let filter_sentiment = filter_obj(filters_mention_sentiment);

	setTimeout(() => {
		$("#top_mention_sentiment_content").html(`
			<h6 class="fw-normal text-center mb-2">TOP MENTION & SENTIMENT</h6>
			<ul class="nav nav-tabs nav-fill" id="tabIN" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="mention-tab" data-bs-toggle="tab" data-bs-target="#mention" type="button" role="tab" aria-controls="mention-tab" aria-selected="true">MENTION</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="sentiment-tab" data-bs-toggle="tab" data-bs-target="#sentiment" type="button" role="tab" aria-controls="sentiment-tab" aria-selected="false">SENTIMENT</button>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade show active px-2" id="mention" role="tabpanel">
					<div class="d-flex justify-content-center align-items-center mt-3">
						<div class="spinner-border text-danger" role="status">
							<span class="visually-hidden">Loading...</span>
						</div>
					</div>
				</div>
				<div class="tab-pane fade px-2 overflow-hidden" id="sentiment" role="tabpanel">
					<div class="d-flex justify-content-center align-items-center mt-3">
						<div class="spinner-border text-danger" role="status">
							<span class="visually-hidden">Loading...</span>
						</div>
					</div>
				</div>
			</div>
		`)

		getDataSmNew(
			`${global_url_api}smart_dashboard/smart_media/top_mention?` + filter_top_mention
		).then((data) => {
			$("#mention").html(""); 
			if (data.data.length > 0) {
				$("#mention").html(`
					<div class="scroll-pane" style="height: 500px !important; overflow: auto;">       
						<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
							<tbody>
								${data.data.map(item => `
									<tr>
										<td>
											<a href="${item.link_mention}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
												<div class="d-flex align-items-center">
													<img src="${global_url_ci + '/my/logo/social-media/' + item.source + '.svg'}" 
														class="rounded-circle" width="35"
														onerror="this.onerror=null; this.src='${global_url_ci + '/my/logo/social-media/' + item.source + '.png'}';"/>
													<div class="mx-3">
														<div class="d-flex align-items-center text-white">
															<img src="${item.icon}" alt="sosmed" class="rounded-circle mx-2" width="25" onerror="this.src='https://app.awario.com/assets/images/elements/no-avatar-4.svg'"/>
															<p class="mb-0 font-weight-bold">${item.user ? item.user : 'Anonim'}</p>
														</div>
													<p class="text-muted small mb-0">${item.post}</p>
													</div>
												</div>
											</a>
										</td>
									</tr>
								`).join("")}
							</tbody>
						</table>
					</div>
				`);
			} else {
				$("#mention").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
			}
					
			$(".load_in").addClass("d-none");
		});
	
		getDataSmNew(
			`${global_url_api}smart_dashboard/smart_media/sentiment?` + filter_sentiment
		).then((data) => {
			$("#sentiment").html(""); 
			if (data.data.length > 0) {
				$("#sentiment").html(`
					<div class="mt-3" id="chart-sentiment" style="width:100%;"></div>
				`);
				conut_chart_statement(data.data, "#chart-sentiment", "render");
			} else {
				$("#sentiment").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
			}
					
			$(".load_in").addClass("d-none");
		});
	}, 2000);

	// INFO KEJADIAN HARI INI
	let filters_kejadian = {
		category: 'BATAS PROVINSI',
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_kejadian = {
			...filters_kejadian,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_kejadian = {
			...filters_kejadian,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_kejadian = {
			...filters_kejadian,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_kejadian = {
			...filters_kejadian,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_kejadian = filter_obj(filters_kejadian);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/event/todays_event_info?` + filter_kejadian
	).then((data) => {
		$("#info_kejadian").html(""); 
		if (data.data.length > 0) {
			$("#info_kejadian").html(`
				<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
					<tbody id="info_kejadian">
						${data.data.map(item => {
							// Format tanggal
							const date_kejadian = new Date(item.tanggal);
							const formattedDateKejadian = date_kejadian.toLocaleDateString("en-GB", {
								day: "2-digit",
								month: "short",
								year: "2-digit",
							}).replace(",", ""); // Hapus koma jika ada
			
							// Format waktu
							const [hh, mm] = item.jam.split(":"); // Ambil jam dan menit
							const formattedTimeKejadian = `${hh.padStart(2, "0")}:${mm}`;
			
							return `
								<tr class="d-flex ${selectedInfoKejadian == item.id ? 'active' : ''} btnkejadian-${item.id}" role="button" 
									onclick="marker_info_kejadian('${global_url_api}smart_dashboard/main_dashboard/event/todays_event_info?${filter_kejadian}', ${item.id}, ${item.id}, ${item.id})">
									<td class="text-white d-flex flex-column w-100">
										<div class="d-flex justify-content-between">
											<div>
												${item.deskripsi}
												<small class="d-block text-muted">${item.nama_jalan}<span class="text-warning d-none">Akan Diperkirakan Macet</span></small>
											</div>
											<div class="text-end">
												${formattedTimeKejadian}
												<small class="d-block text-muted">${formattedDateKejadian}</small>
											</div>
										</div>
										<div class="text-warning text-rekomendasi">
											<span>${item.rekomendasi}</span>
										</div>
									</td>
								</tr>
							`;
						}).join("")}
					</tbody>
				</table>
			`);				
		} else {
			$("#info_kejadian").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
		
		$(".load_in").addClass("d-none");
	});

	// OBJECT VITAL
	let filters_object_vital = {
		category: 'BATAS PROVINSI',
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_object_vital = {
			...filters_object_vital,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_object_vital = {
			...filters_object_vital,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}

	let filter_object_vital = filter_obj(filters_object_vital);
	getDataSmNew(
		`${global_url_api_v2}map/v2/distance-matrix?` + filter_object_vital
	).then((data) => {
		$("#object_vital").html("");
		$("#fasum").html("");
		const vitalItems = data.data.filter(item => item.type == 'vital');
		const publicItems = data.data.filter(item => item.type == 'public');
		if (data.data.length > 0 && vitalItems.length > 0) {
			$("#object_vital").html(`
				<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
					<tbody>
						${data.data.filter(item => item.type == 'vital').map((item, key) => `
							<tr class="${selectedBtnAnalisaWaktu == key ? 'active' : ''} btnvital-${key}" role="button" onclick="rute_analisa_waktu(\`${item.start_coordinates}\`, \`${item.end_coordinates}\`, ${key}, 'vital')">
								<td class="text-white d-flex flex-column">
									<div class="d-flex justify-content-between">
										<div class="w-75">
											${item.start_name + ' - ' + item.end_name}
											<span class="alert-icon d-none">⚠</span>
											<small class="d-block text-muted">• ${item.average_speed}<span class="text-warning d-none">Akan Diperkirakan Macet</span></small>
										</div>
										<div class="w-25 text-end">
											${item.distance}
											<small class="d-block text-muted">${item.duration}</small>
										</div>
									</div>
									<div class="w-100">
										<small class="d-block text-muted">• Estimasi BBM</small>
										<div class="d-flex gap-2 mt-1">
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/mobil.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_car}</small>
											</div>
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/motor.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_motor}</small>
											</div>
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/bus.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_bus}</small>
											</div>
										</div>
									</div>
								</td>
							</tr>
						`).join("")}
					</tbody>
				</table>
			`);
		} else {
			$("#object_vital").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}

		if (data.data.length > 0 && publicItems.length > 0) {
			$("#fasum").html(`
				<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
					<tbody>
						${data.data.filter(item => item.type == 'public').map((item, key) => `
							<tr class="${selectedBtnAnalisaWaktu == key ? 'active' : ''} btnpublic-${key}" role="button" onclick="rute_analisa_waktu(\`${item.start_coordinates}\`, \`${item.end_coordinates}\`, ${key}, 'public')">
								<td class="text-white d-flex flex-column">
									<div class="d-flex justify-content-between">
										<div class="w-75">
											${item.start_name + ' - ' + item.end_name}
											<span class="alert-icon d-none">⚠</span>
											<small class="d-block text-muted">• ${item.average_speed}<span class="text-warning d-none">Akan Diperkirakan Macet</span></small>
										</div>
										<div class="w-25 text-end">
											${item.distance}
											<small class="d-block text-muted">${item.duration}</small>
										</div>
									</div>
									<div class="w-100">
										<small class="d-block text-muted">• Estimasi BBM</small>
										<div class="d-flex gap-2 mt-1">
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/mobil.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_car}</small>
											</div>
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/motor.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_motor}</small>
											</div>
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/bus.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_bus}</small>
											</div>
										</div>
									</div>
								</td>
							</tr>
						`).join("")}
					</tbody>
				</table>
			`);
		} else {
			$("#object_vital").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
					
		$(".load_in").addClass("d-none");
	});

	// RUNNING TEXT
	let filters_runningtext = {
		category: 'BATAS PROVINSI',
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_runningtext = {
			...filters_runningtext,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_runningtext = {
			...filters_runningtext,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}

	let filter_runningtext = filter_obj(filters_runningtext);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/accident/prevention?` + filter_runningtext
	).then((data) => {
		$(".ticker-text").html(""); 
		if (data.data.length > 0) {
			$(".ticker-text").html(data.data.map(item => item.rekomendasi).join("  |  "));
		} else {
			$(".ticker-text").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
	});
	
	// PENCEMARAN UDARA
	let filters_pencemaran_udara = {
		lat: `${global_lat_center}`,
		long: `${global_lng_center}`
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_pencemaran_udara = {
			...filters_pencemaran_udara,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_pencemaran_udara = {
			...filters_pencemaran_udara,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_pencemaran_udara = {
			...filters_pencemaran_udara,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_pencemaran_udara = {
			...filters_pencemaran_udara,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_pencemaran_udara = filter_obj(filters_pencemaran_udara);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/polution/quality-air?` + filter_pencemaran_udara
	).then((data) => {
		$("#pencemaran_udara_content").html(`	
			<h6 class="fw-normal text-center mb-2">INDEKS PENCEMARAN UDARA</h6>
			<div class="d-flex flex-column" id="pencemar_udara"></div>
		`); 

		const airQuality = data.data.air_quality.aqi; // Ambil nilai kualitas udara
		const airQualityClasses = {
			1: "good",       // Baik
			2: "fair",       // Cukup
			3: "moderate",   // Sedang
			4: "poor",       // Buruk
			5: "very-poor"   // Sangat Buruk
		};

		const airQualityClass = airQualityClasses[airQuality];
		if (data.status) {
			$("#pencemar_udara").html(`
				<div class="d-flex justify-content-start gap-3 align-items-center">
					<div class="text-dark rounded fw-bold py-3 px-4 ${airQualityClass}">${data.data.air_quality.aqi}</div>
					<div class="d-flex flex-column">
						<span class="fw-bold text-${airQualityClass}">${data.data.air_quality.status}</span>
						<span class="small text-secondary">${data.data.air_quality.description}</span>
					</div>
				</div>
				<hr class="border-secondary">
				<div class="d-flex gap-3">
					<div class="d-flex gap-2 mb-2">
						<img src="${global_url_ci}/my/logo/weather/temp.png" width="20" alt="">
						<b>${data.data.weather.temperature}</b>
					</div>
					<div class="d-flex gap-2 mb-2">
						<img src="${global_url_ci}/my/logo/weather/humidity.png" width="20" alt="">
						<b>${data.data.weather.humidity}</b>
					</div>
					<div class="d-flex gap-2 mb-2">
						<img src="${global_url_ci}/my/logo/weather/wind.png" width="20" alt="">
						<b>${data.data.weather.wind_speed}</b>
					</div>
				</div>
				<p class="small text-secondary" id="datetime"></p>

				<!-- Tooltip Box -->
				<div class="card tooltip-box" style="background: #000C1E">
					<div class="card-body p-1">
						<div class="d-flex align-items-center">
							<div class="good text-dark fw-semibold px-3">1</div>
							<div class="mx-3">Baik</div>
						</div>
						<div class="d-flex align-items-center">
							<div class="fair text-dark fw-semibold px-3">2</div>
							<div class="mx-3">Cukup</div>
						</div>
						<div class="d-flex align-items-center">
							<div class="moderate text-dark fw-semibold px-3">3</div>
							<div class="mx-3">Sedang</div>
						</div>
						<div class="d-flex align-items-center">
							<div class="poor text-dark fw-semibold px-3">4</div>
							<div class="mx-3">Buruk</div>
						</div>
						<div class="d-flex align-items-center">
							<div class="very-poor text-dark fw-semibold px-3">5</div>
							<div class="mx-3">Sangat Buruk</div>
						</div>
					</div>
				</div>
			`);
		} else {
			$("#pencemar_udara").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
	});

	// PELANGGARAN
	let filters_pelanggaran = {
		category: 'BATAS PROVINSI',
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_pelanggaran = {
			...filters_pelanggaran,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_pelanggaran = {
			...filters_pelanggaran,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_pelanggaran = {
			...filters_pelanggaran,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_pelanggaran = {
			...filters_pelanggaran,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_pelanggaran = filter_obj(filters_pelanggaran);
	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_enforcement/jalan?kategori_pelanggaran=PERDA` + filter_pelanggaran
	).then((data) => {
		$("#pelanggaran_content").html(`	
			<h6 class="fw-normal text-center mb-2">PELANGGARAN</h6>
			<div class="d-flex flex-column" id="t_list_pelanggaran"></div>	
		`);
		let scrollStyle = data.data.length > 5 ? 'height: 300px !important; overflow: auto;' : '';
		
		if (data.data.perda.length > 0) {
			$("#t_list_pelanggaran").html(`
				<div class="scroll-pane" style="${scrollStyle}">
					<table class="table table-hover custom-table-road-safety mb-0">
						<thead>
							<tr>
								<th>JALAN</th>
								<th>JUMLAH</th>
							</tr>
						</thead>
						<tbody>
							${data.data.perda.map((item, key) => `
								<tr>
									<td>${item.nama_jalan}</td>
									<td>${item.total}</td>
								</tr>
							`).join("")}
						</tbody>
					</table>
				</div>
			`)
		} else {
			$("#t_list_pelanggaran").html(`
				<table class="table table-hover custom-table-road-safety">
					<thead>
						<tr>
							<th>JALAN</th>
							<th>JUMLAH</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="text-center" colspan="3">
								<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>
							</td>
						</tr>
					</tbody>
				</table>	
			`);
		}
	});
	
	// CROWD COUNTING
	let filters_crowd_counting = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_crowd_counting = {
			...filters_crowd_counting,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_crowd_counting = {
			...filters_crowd_counting,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_crowd_counting = {
			...filters_crowd_counting,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_crowd_counting = {
			...filters_crowd_counting,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_crowd_counting = filter_obj(filters_crowd_counting);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/crowd/rekap_crowd_counting?` + filter_crowd_counting
	).then((data) => {
		$("#crowd_counting_content").html(`
			<h6 class="fw-normal text-center mb-2">CROWD COUNTING</h6>
			<div class="d-flex flex-column" id="t_list_crowd_counting"></div>
		`);

		let scrollStyle = data.data.length > 5 ? 'height: 300px !important; overflow: auto;' : '';
		if (data.data.length > 0) {
			$("#t_list_crowd_counting").html(`
				<div class="scroll-pane" style="${scrollStyle}">
					<table class="table table-hover custom-table-road-safety mb-0">
						<thead>
							<tr>
								<th>CCTV</th>
								<th>PRIA</th>
								<th>WANITA</th>
							</tr>
						</thead>
						<tbody>
							${data.data.map((item, key) => `
								<tr>
									<td>${item.nama_cctv}</td>
									<td>${item.pria}</td>
									<td>${item.wanita}</td>
								</tr>
							`).join("")}
						</tbody>
					</table>
				</div>
			`)
		} else {
			$("#t_list_crowd_counting").html(`
				<table class="table table-hover custom-table-road-safety">
					<thead>
						<tr>
							<th>CCTV</th>
							<th>PRIA</th>
							<th>WANITA</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="text-center" colspan="3">
								<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>
							</td>
						</tr>
					</tbody>
				</table>	
			`);
		}
	});
	
	function updateTime() {
		const now = new Date();
		const options = { 
		  timeZone: global_timezone, 
		  hour12: false, 
		  hour: '2-digit', 
		  minute: '2-digit' 
		};
		
		const time = now.toLocaleTimeString('id-ID', options);
		const date = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
		$("#datetime").html(`${global_weather_name} • ${date} • ${time}`);
	}
	
	updateTime(); // Update saat pertama kali dimuat
	setInterval(updateTime, 1000); // Perbarui setiap detik

	// REKOMENDASI PENCEGAHAN
	let filters_pencegahan = {
		category: 'BATAS PROVINSI',
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_pencegahan = {
			...filters_pencegahan,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_pencegahan = {
			...filters_pencegahan,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}

	let filter_pencegahan = filter_obj(filters_pencegahan);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/accident/prevention?` + filter_pencegahan
	).then((data) => {
		$("#rawan_laka").html(`
			<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
				<tbody id="t_list_rawan_laka"></tbody>
			</table>
		`);
		
		$("#rawan_langgar").html(`
			<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
				<tbody id="t_list_rawan_langgar"></tbody>
			</table>
		`);
		
		const lakaItems = data.data.map((item, key) => ({ ...item, originalIndex: key })).filter(item => item.type === 'laka');
		const langgarItems = data.data.map((item, key) => ({ ...item, originalIndex: key })).filter(item => item.type === 'langgar');
		
		if (lakaItems.length > 0) {
			$("#t_list_rawan_laka").html(`
				${lakaItems.map(item => `
					<tr class="d-flex align-items-start ${selectedBtnRawanIrsms == item.originalIndex ? 'active' : ''} btnrawanlaka-${item.originalIndex}" 
						role="button" 
						onclick='rawan_heat(${item.originalIndex + 1}, ${item.originalIndex}, "laka")'>
						<td class="cctv-name text-white" style="cursor: pointer;">
							${item.rekomendasi}
							<small class="d-block text-muted">• ${item.total + ' Titik'}</small>
						</td>
					</tr>
				`).join("")}
			`);
		} else {
			$("#rawan_laka").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
		
		if (langgarItems.length > 0) {
			$("#t_list_rawan_langgar").html(`
				${langgarItems.map(item => `
					<tr class="d-flex align-items-start ${selectedBtnRawanIrsms == item.originalIndex ? 'active' : ''} btnrawanlanggar-${item.originalIndex}" 
						role="button" 
						onclick='rawan_heat(${item.originalIndex + 1}, ${item.originalIndex}, "langgar")'>
						<td class="cctv-name text-white" style="cursor: pointer;">
							${item.rekomendasi}
							<small class="d-block text-muted">• ${item.total + ' Titik'}</small>
						</td>
					</tr>
				`).join("")}
			`);
		} else {
			$("#rawan_langgar").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
					
		$(".load_in").addClass("d-none");
	});
	
	// GET CCTV
	let filters_cctv_in_out = {
		category: 'BATAS PROVINSI',
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_cctv_in_out = {
			...filters_cctv_in_out,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_cctv_in_out = {
			...filters_cctv_in_out,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_cctv_in_out = {
			...filters_cctv_in_out,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_cctv_in_out = {
			...filters_cctv_in_out,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_cctv = filter_obj(filters_cctv_in_out);
	getDataSmNew(`${global_url_api}sm_integration/vcr?` + filter_cctv + '&status_cctv=IN').then((data) => {
        if (data.data && data.data.length > 0) {
            $("#kinerja_ruas_jalan_in").html(`
                <h6 class="fw-normal mb-2 mt-4">KINERJA RUAS JALAN</h6>
                <div class="swiper" id="swiper-stream-in"></div>
                <div class="d-flex flex-wrap justify-content-center">
                    <div class="d-flex flex-column m-2"><h5 class="text-primary fw-bold" id="vc_rasio_in"></h5><small>VC Rasio</small></div>
                    <div class="d-flex flex-column m-2"><h5 class="text-primary fw-bold" id="vcr_category_in"></h5><small>Level of Service</small></div>
                </div>
            `);

            vcr_stream_in = data.data;
            $("#swiper-stream-in").html(`
                <div class="swiper-wrapper">
                    ${data.data.map((item, key) => `
                        <div class="swiper-slide swipe-in" id="stream_in_${key}" data-cam-id="${item.id}">
                            <video class="d-none border border-2 border-primary" preload="none" muted playsinline></video>
                            <img src="${global_url_ci}/my/img_/thumbnailcctv.png" class="border border-2 border-primary" style="filter: blur(2px)">
                            <h6 class="fw-normal mt-3 w-50 mx-auto">${item.nama_cctv}</h6>
                        </div>
                    `).join("")}
                </div>
                <div class="swiperBtnPrevIn swiper-button-prev"></div>
                <div class="swiperBtnNextIn swiper-button-next"></div>
            `);

            if (swiperIN) swiperIN.destroy(true, true);

            swiperIN = new Swiper("#swiper-stream-in", {
                effect: "coverflow",
				grabCursor: false,
				centeredSlides: true,
				slidesPerView: "auto",
				loop: true,
				autoplay: {
					delay: 30000,
					disableOnInteraction: false,
					pauseOnMouseEnter: true
				},
				coverflowEffect: {
					rotate: 0,
					stretch: 50,
					depth: 150,
					modifier: 2,
					slideShadows: false,
				},
				allowTouchMove: false,
                navigation: { nextEl: "#swiper-stream-in .swiperBtnNextIn", prevEl: "#swiper-stream-in .swiperBtnPrevIn" },
                on: {
                    init: function() { 
						handleSwiperChange(this, 'in', vcr_stream_in);
					},
                    slideChange: function() {
						handleSwiperChange(this, 'in', vcr_stream_in);
					}
                }
            });
        } else {
			$("#kinerja_ruas_jalan_in").html("");
            $("#tab_vcr_in").html(`<em class="d-flex justify-content-center">Tidak ada data</em>`);
        }
    });

	getDataSmNew(`${global_url_api}sm_integration/vcr?` + filter_cctv + '&status_cctv=OUT').then((data) => {
        if (data.data && data.data.length > 0) {
            $("#kinerja_ruas_jalan_out").html(`
                <h6 class="fw-normal mb-2 mt-4">KINERJA RUAS JALAN</h6>
                <div class="swiper" id="swiper-stream-out"></div>
                <div class="d-flex flex-wrap justify-content-center">
                    <div class="d-flex flex-column m-2"><h5 class="text-danger fw-bold" id="vc_rasio_out"></h5><small>VC Rasio</small></div>
                    <div class="d-flex flex-column m-2"><h5 class="text-danger fw-bold" id="vcr_category_out"></h5><small>Level of Service</small></div>
                </div>
            `);

            vcr_stream_out = data.data;
            $("#swiper-stream-out").html(`
                <div class="swiper-wrapper">
                    ${data.data.map((item, key) => `
                        <div class="swiper-slide swipe-out" id="stream_out_${key}" data-cam-id="${item.id}">
                            <video class="d-none border border-2 border-danger" preload="none" muted playsinline></video>
                            <img src="${global_url_ci}/my/img_/thumbnailcctv.png" class="border border-2 border-danger" style="filter: blur(2px)">
                            <h6 class="fw-normal mt-3 w-50 mx-auto">${item.nama_cctv}</h6>
                        </div>
                    `).join("")}
                </div>
                <div class="swiperBtnPrevOut swiper-button-prev"></div>
                <div class="swiperBtnNextOut swiper-button-next"></div>
            `);

            if (swiperOUT) swiperOUT.destroy(true, true);

            swiperOUT = new Swiper("#swiper-stream-out", {
                effect: "coverflow",
				grabCursor: false,
				centeredSlides: true,
				slidesPerView: "auto",
				loop: true,
				autoplay: {
					delay: 30000,
					disableOnInteraction: false,
					pauseOnMouseEnter: true
				},
				coverflowEffect: {
					rotate: 0,
					stretch: 50,
					depth: 150,
					modifier: 2,
					slideShadows: false,
				},
				allowTouchMove: false,
                navigation: { nextEl: "#swiper-stream-out .swiperBtnNextOut", prevEl: "#swiper-stream-out .swiperBtnPrevOut" },
                on: {
                    init: function() { 
						handleSwiperChange(this, 'out', vcr_stream_out);
					},
                    slideChange: function() { 
						handleSwiperChange(this, 'out', vcr_stream_out);
					}
                }
            });
        } else {
            $("#kinerja_ruas_jalan_out").html("");
            $("#tab_vcr_out").html(`<em class="d-flex justify-content-center">Tidak ada data</em>`);
        }
    });

    function handleSwiperChange(swiperInstance, groupKey, dataList) {
		cleanupPC(groupKey);
		var timer = (groupKey === 'in') ? debounceTimerIn : debounceTimerOut;
		if (timer) clearTimeout(timer);

		const activeIndex = swiperInstance.realIndex;
		activeIndexes[groupKey] = swiperInstance.activeIndex;
	
		const delay = 500;

		const executeStream = () => {
			const activeSlideEl = $(swiperInstance.slides[swiperInstance.activeIndex]);
			const videoEl = activeSlideEl.find('video')[0];
			const camId = activeSlideEl.attr('data-cam-id');
	
			if (!videoEl || !camId) return;
	
			const wrapperId = groupKey === 'in' ? '#swiper-stream-in' : '#swiper-stream-out';
			$(`${wrapperId} .swiper-slide`).each(function() {
				if ($(this).hasClass('swiper-slide-active') || $(this).hasClass('swiper-slide-duplicate-active')) {
					$(this).find('video').removeClass('d-none');
					$(this).find('img').addClass('d-none');
				} else {
					$(this).find('video').addClass('d-none').removeAttr('src');
					$(this).find('img').removeClass('d-none');
				}
			});
			
            if(dataList && dataList[activeIndex]) {
                const dataItem = dataList[activeIndex];
                if(groupKey === 'in') {
                    vcr_chart_in(dataItem.nama_cctv);
                    $("#vc_rasio_in").html(dataItem.vcr);
                    $("#vcr_category_in").html(dataItem.vcrCategoryStr);
                } else {
                    vcr_chart_out(dataItem.nama_cctv);
                    $("#vc_rasio_out").html(dataItem.vcr);
                    $("#vcr_category_out").html(dataItem.vcrCategoryStr);
                }
            }
			
			// Start Stream
			loadStream(groupKey, camId, videoEl, swiperInstance.activeIndex); 
		};

		if (groupKey === 'in') {
			debounceTimerIn = setTimeout(executeStream, delay);
		} else {
			debounceTimerOut = setTimeout(executeStream, delay);
		}
	}

	function vcr_chart_in(nama_cctv) {
		let filters_vcr_in_out = {
			nama_cctv: nama_cctv,
			category: 'BATAS PROVINSI',
		};
		
		// Menggabungkan objek dengan validasi
		if (selectedPolda?.polda) {
			filters_vcr_in_out = {
				...filters_vcr_in_out,
				polda: selectedPolda.polda,
				polda_id: selectedPolda.polda_id,
			};
		} else if (usr_polda || paramsPolda) {
			filters_vcr_in_out = {
				...filters_vcr_in_out,
				polda: usr_polda ? usr_polda : paramsPolda,
				polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
			};
		}
		if (selectedPolda?.polres) {
			filters_vcr_in_out = {
				...filters_vcr_in_out,
				polres: selectedPolda.polres,
				polres_id: selectedPolda.polres_id,
			};
		} else if (usr_polres || paramsPolres) {
			filters_vcr_in_out = {
				...filters_vcr_in_out,
				polres: usr_polres ? usr_polres : paramsPolres,
				polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
			};
		}

		let filter_vcr_in = filter_obj(filters_vcr_in_out);
		getDataSmNew(
			`${global_url_api}smart_dashboard/main_dashboard/vcr/rekap_vcr_in?` + filter_vcr_in
		).then((data) => {
			$("#tab_vcr_in").html(`
				<h6 class="fw-normal my-3">GRAFIK VCR ${nama_cctv}</h6>
				<div id="chart-vcr-in"></div>
			`);
			if (data.data) {
				line_chart_vcr(data.data, "#chart-vcr-in", "render", "#1E90FF");
			} else {
				$("#tab_vcr_in").html('');
				$("#tab_vcr_in").html(`<em class="d-flex justify-content-center">Tidak ada data</em>`);
			}
			
			$(".load_vcr_in").addClass("d-none");
		});
	}

	function vcr_chart_out(nama_cctv) {
		let filters_vcr_in_out = {
			nama_cctv: nama_cctv,
			category: 'BATAS PROVINSI',
		};
		
		// Menggabungkan objek dengan validasi
		if (selectedPolda?.polda) {
			filters_vcr_in_out = {
				...filters_vcr_in_out,
				polda: selectedPolda.polda,
				polda_id: selectedPolda.polda_id,
			};
		} else if (usr_polda || paramsPolda) {
			filters_vcr_in_out = {
				...filters_vcr_in_out,
				polda: usr_polda ? usr_polda : paramsPolda,
				polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
			};
		}
		if (selectedPolda?.polres) {
			filters_vcr_in_out = {
				...filters_vcr_in_out,
				polres: selectedPolda.polres,
				polres_id: selectedPolda.polres_id,
			};
		} else if (usr_polres || paramsPolres) {
			filters_vcr_in_out = {
				...filters_vcr_in_out,
				polres: usr_polres ? usr_polres : paramsPolres,
				polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
			};
		}

		let filter_vcr_out = filter_obj(filters_vcr_in_out);
		getDataSmNew(
			`${global_url_api}smart_dashboard/main_dashboard/vcr/rekap_vcr_out?` + filter_vcr_out
		).then((data) => {
			$("#tab_vcr_out").html(`
				<h6 class="fw-normal my-3">GRAFIK VCR ${nama_cctv}</h6>
				<div id="chart-vcr-out"></div>
			`);
			if (data.data) {
				line_chart_vcr(data.data, "#chart-vcr-out", "render", "#BC1C1C");
			} else {
				$("#tab_vcr_out").html('');
				$("#tab_vcr_out").html(`<em class="d-flex justify-content-center">Tidak ada data</em>`);
			}
			$(".load_vcr_out").addClass("d-none");
		});
	}
	
	$("#pencemaran_udara_content").hover(
		function() {
			$(".tooltip-box").fadeIn(200); // Munculkan tooltip
		},
		function() {
			$(".tooltip-box").fadeOut(200); // Sembunyikan tooltip
		}
	);

	$("#alert_summaryIn").hover(
		function() {
			let tooltip = $("#alert_hover_summaryIn");
			tooltip.appendTo("body") // Pindahkan tooltip ke body agar tidak tertutup
				.removeClass("d-none")
				.fadeIn(200);
		},
		function() {
			let tooltip = $("#alert_hover_summaryIn");
			tooltip.addClass("d-none").fadeOut(200, function() {
				tooltip.appendTo("#alert_summaryIn").css({ position: "", top: "", left: "" }); // Kembalikan tooltip ke elemen asalnya
			});
		}
	);

	set_all_cctv(
		filters_cctv_in_out.polda_id != undefined ? filters_cctv_in_out.polda_id : usr_polda_id != "" ? usr_polda_id : paramsPoldaId,
		filters_cctv_in_out.polres_id != undefined ? filters_cctv_in_out.polres_id : usr_polres_id != "" ? usr_polres_id : paramsPolresId,
		"1",
		"road_safety_batas_provinsi",
		"only_live"
	);

	t_out_road_safety_batas_provinsi = setTimeout(() => {
		isAlertTriggered = false;
		$("#bottom-content").html(``);
		$("#konten-kiri").html(``);
		$("#konten-kanan").html(``);
		dash_road_safety_perbatasan_provinsi({
			start_date: date_time_filter(filter).start_date,
			end_date: date_time_filter(filter).end_date,
		}, selectedPolda);
	}, 300000);

	autoScroll()
}

function dash_road_safety_perbatasan_provinsi_cctv(nama_cctv="", filter = {}) {
	trafficLayer = new google.maps.TrafficLayer();
	trafficLayer.setMap(map);

	$("#konten-kiri").html(``);
	$("#konten-kanan").html(``);
	$("#bottom-content").html(``);

    $("#konten-kiri").html(`
		<div class="text-white pt-5">
			<div class="d-flex align-items-center justify-content-start gap-3 px-3">
				<img src="${global_url_ci}/my/logo/in.png" width="50" alt="">
				<div class="d-flex flex-column text-left">
					<h6 class="fw-normal">Total Kendaraan Masuk</h6>
					<div class="d-flex gap-2 position-relative">
						<h4 id="prediksi_masuk" class="text-white text-left"></h4>
						<img src="${global_url_ci}/my/satupeta/emergency.png" width="30" class="blinking d-none" id="alert_summaryIn" />
						<span class="d-flex align-items-center p-2 text-white position-absolute d-none" id="alert_hover_summaryIn">
							<img src="${global_url_ci}/my/satupeta/emergency.png" class="me-2" width="30">
							<small class="fw-semibold">Jumlah kendaraan masuk mencapai lebih dari <span id="vechileIn"></span> kendaraan. <b class="d-block">Arus lalu lintas akan diperkirakan macet.</b></small>
						</span>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column text-center mt-2">
				<ul class="nav nav-tabs nav-fill" id="tabIN" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="prediksi-in-tab" data-bs-toggle="tab" data-bs-target="#prediksi_in" type="button" role="tab" aria-controls="prediksi-in-tab" aria-selected="true">PREDIKSI</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="vcr-in-tab" data-bs-toggle="tab" data-bs-target="#tab_vcr_in" type="button" role="tab" aria-controls="vcr-in-tab" aria-selected="false">VC RASIO</button>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane fade show active px-2" id="prediksi_in" role="tabpanel">
						<div class="d-flex justify-content-center align-items-center mt-3">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					<div class="tab-pane fade px-2 overflow-hidden" id="tab_vcr_in" role="tabpanel">
						<div class="d-flex justify-content-center align-items-center mt-3 load_vcr_in">
							<div class="spinner-border text-primary" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column text-center px-2" id="kinerja_ruas_jalan_in"></div>
			<div class="d-flex flex-column px-2 mt-4" id="crowd_counting_content"></div>
			<div class="d-flex flex-column px-2 mt-4" id="pencemaran_udara_content"></div>
		</div>
	`);

    $("#konten-kanan").html(`
		<div class="text-white pt-5">
			<div class="d-flex align-items-center justify-content-end gap-3 px-3">
				<div class="d-flex flex-column text-right">
					<h6 class="fw-normal">Total Kendaraan Keluar</h6>
					<h4 id="prediksi_keluar" class="text-white text-end"></h4>
				</div>
				<img src="${global_url_ci}/my/logo/out.png" width="50" alt="">
			</div>
			<div class="d-flex flex-column text-center">
				<ul class="nav nav-tabs nav-fill mt-2" id="tabOUT" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="prediksi-out-tab" data-bs-toggle="tab" data-bs-target="#prediksi_out" type="button" role="tab" aria-controls="prediksi-out-tab" aria-selected="true">PREDIKSI</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="vcr-out-tab" data-bs-toggle="tab" data-bs-target="#tab_vcr_out" type="button" role="tab" aria-controls="vcr-out-tab" aria-selected="false">VC RASIO</button>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane fade show active px-2" id="prediksi_out" role="tabpanel">
						<div class="d-flex justify-content-center align-items-center mt-3">
							<div class="spinner-border text-danger" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					<div class="tab-pane fade px-2 overflow-hidden" id="tab_vcr_out" role="tabpanel">
						<div class="d-flex justify-content-center align-items-center mt-3 load_vcr_out">
							<div class="spinner-border text-danger" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column text-center px-2" id="kinerja_ruas_jalan_out"></div>
			<div class="d-flex flex-column px-2 mt-4" id="pelanggaran_content"></div>
			<div class="d-flex flex-column mt-4" id="top_mention_sentiment_content"></div>
		</div>
	`); 

    $("#bottom-content").html(`
		<div class="d-flex justify-content-between align-items-start text-white gap-2 px-3 pt-4">
			<div class="d-flex flex-column w-100">
				<h6 class="fw-normal text-center">INFO KEJADIAN</h6>
				<ul class="nav nav-tabs nav-fill mt-0" id="tabIN" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="object-vital-tab" data-bs-toggle="tab" data-bs-target="#info_kejadian" type="button" role="tab" aria-controls="info_kejadian-tab" aria-selected="false">HARI INI</button>
					</li>
				</ul>
				<div class="tab-content scroll-pane" style="height: 450px !important;overflow: auto;">
					<div class="tab-pane fade show active" id="info_kejadian" role="tabpanel" aria-labelledby="info_kejadian">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column w-100">
				<h6 class="fw-normal text-center">ANALISA WAKTU TEMPUH</h6>
				<ul class="nav nav-tabs nav-fill mt-0" id="tabIN" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="object-vital-tab" data-bs-toggle="tab" data-bs-target="#object_vital" type="button" role="tab" aria-controls="object_vital-tab" aria-selected="false">OBJEK VITAL</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="fasum-tab" data-bs-toggle="tab" data-bs-target="#fasum" type="button" role="tab" aria-controls="fasum-tab" aria-selected="false">FASUM</button>
					</li>
				</ul>
				<div class="tab-content scroll-pane" style="height: 450px !important;overflow: auto;">
					<div class="tab-pane fade show active" id="object_vital" role="tabpanel" aria-labelledby="object_vital">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					<div class="tab-pane fade show" id="fasum" role="tabpanel" aria-labelledby="fasum">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="d-flex flex-column w-100">
				<h6 class="fw-normal text-center">REKOMENDASI PENCEGAHAN</h6>
				<ul class="nav nav-tabs nav-fill mt-0" id="tabIN" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="rawan-laka-tab" data-bs-toggle="tab" data-bs-target="#rawan_laka" type="button" role="tab" aria-controls="rawan-laka-tab" aria-selected="false">RAWAN LAKA</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="rawan-langgar-tab" data-bs-toggle="tab" data-bs-target="#rawan_langgar" type="button" role="tab" aria-controls="rawan-langgar-tab" aria-selected="false">RAWAN LANGGAR</button>
					</li>
				</ul>
				<div class="tab-content scroll-pane" style="height: 450px !important;overflow: auto;">
					<div class="tab-pane fade show active" id="rawan_laka" role="tabpanel" aria-labelledby="rawan_laka">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
					<div class="tab-pane fade show" id="rawan_langgar" role="tabpanel" aria-labelledby="rawan_langgar">
						<div class="d-flex justify-content-center align-items-center mt-2">
							<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	`);   
	
	// Chart Prediksi IN & OUT
	let filters_prediksi_in_out = {
		nama_cctv: nama_cctv,
		category: 'BATAS PROVINSI',
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_prediksi_in_out = {
			...filters_prediksi_in_out,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_prediksi_in_out = {
			...filters_prediksi_in_out,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_prediksi_in_out = {
			...filters_prediksi_in_out,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_prediksi_in_out = {
			...filters_prediksi_in_out,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_prediksi_in = filter_obj(filters_prediksi_in_out);
	let filter_prediksi_out = filter_obj(filters_prediksi_in_out);
	checkPeningkatanAlert(date_time_filter(filter).start_date, date_time_filter(filter).end_date, nama_cctv)

	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/traffic/traffic_in?` + filter_prediksi_in
	).then((data) => {
		$("#prediksi_in").html(""); 
		if (data.data) {
			$("#prediksi_in").html(`
				<h6 class="fw-normal my-3">GRAFIK KENDARAAN MASUK</h6>
				<div id="chart-vechile-in" style="width:100%;"></div>
				<span class="alert-span-in d-flex justify-content-center rounded p-1 mb-2"><small class="fw-semibold" id="prediksi_teks_in"></small></span>
				<span class="bg-primary d-flex justify-content-center rounded p-1"><small class="fw-semibold" id="traffic_tinggi_in"></small></span>
			`);
			if (typeof data !== "undefined" && data !== null) {
				const congestionAlert = data.data.congestion_alert;
				const teksPrediksi = "Prediksi peningkatan lalin dalam 2 jam kedepan";
				const iconHtml = `<img src="${global_url_ci}/my/satupeta/emergency.png" class="me-2 blinking" />`;
				if (congestionAlert) {
					$("#prediksi_teks_in").html(teksPrediksi);
					$("#prediksi_teks_in").closest("span").prepend(iconHtml);
					$("#prediksi_teks_in").closest("span").show(); // Pastikan span tampil
					$("#alert_summaryIn").removeClass('d-none');
					$("#vechileIn").html(formatNumberWithThousandSeparator(data.data.total_kendaraan))
				} else {
					$("#prediksi_teks_in").closest("span").remove(); // Hapus span jika false
					$("#alert_summaryIn").addClass('d-none');
				}
			}
			line_chart_forecast(data.data, "#chart-vechile-in", "render", "#1E90FF");
		} else {
			$("#prediksi_in").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
		$("#prediksi_masuk").text(``);
		$("#prediksi_masuk").text(
			formatNumberWithThousandSeparator(data.data.total_kendaraan)
		);
		$("#traffic_tinggi_in").text(data.data.traffic_tertinggi);
		$(".load_in").addClass("d-none");
	});

	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/traffic/traffic_out?` + filter_prediksi_out
	).then((data) => {
		$("#prediksi_out").html(""); 
		if (data.data) {
			$("#prediksi_out").html(`
				<h6 class="fw-normal my-3">GRAFIK KENDARAAN KELUAR</h6>
				<div id="chart-vechile-out" style="width:100%;"></div>
				<span class="alert-span-out d-flex justify-content-center rounded p-1 mb-2"><small class="fw-semibold" id="prediksi_teks_out"></small></span>
				<span class="bg-danger d-flex justify-content-center rounded p-1"><small class="fw-semibold" id="traffic_tinggi_out"></small></span>
			`);
			if (typeof data !== "undefined" && data !== null) {
				const congestionAlert = data.data.congestion_alert;
				const teksPrediksi = "Prediksi peningkatan lalin dalam 2 jam kedepan";
				const iconHtml = `<img src="${global_url_ci}/my/satupeta/emergency.png" class="me-2 blinking" />`;
				if (congestionAlert) {
					$("#prediksi_teks_out").html(teksPrediksi);
					$("#prediksi_teks_out").closest("span").prepend(iconHtml);
					$("#prediksi_teks_out").closest("span").show(); // Pastikan span tampil
				} else {
					$("#prediksi_teks_out").closest("span").remove(); // Hapus span jika false
				}
			}
			line_chart_forecast(data.data, "#chart-vechile-out", "render", "#BC1C1C");
		} else {
			$("#prediksi_out").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
		$("#prediksi_keluar").text(``);
		$("#prediksi_keluar").text(
			formatNumberWithThousandSeparator(data.data.total_kendaraan)
		);
		$("#traffic_tinggi_out").text(data.data.traffic_tertinggi);
		$(".load_in").addClass("d-none");
	});
		
	// Chart VCR IN & OUT
	let filters_vcr_in_out = {
		nama_cctv: nama_cctv,
		category: 'BATAS PROVINSI',
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_vcr_in_out = {
			...filters_vcr_in_out,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_vcr_in_out = {
			...filters_vcr_in_out,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_vcr_in_out = {
			...filters_vcr_in_out,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_vcr_in_out = {
			...filters_vcr_in_out,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}
	
	let filter_vcr_in = filter_obj(filters_vcr_in_out);
	let filter_vcr_out = filter_obj(filters_vcr_in_out);
	
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/vcr/rekap_vcr_in?` + filter_vcr_in
	).then((data) => {
		$("#tab_vcr_in").html(`
			<h6 class="fw-normal my-3">GRAFIK VCR ${nama_cctv}</h6>
			<div id="chart-vcr-in"></div>
		`);
		if (data.data) {
			line_chart_vcr(data.data, "#chart-vcr-in", "render", "#1E90FF");
		} else {
			$("#tab_vcr_in").html(`<em class="d-flex justify-content-center">Tidak ada data</em>`);
		}
			
		$(".load_in").addClass("d-none");
	});
	
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/vcr/rekap_vcr_out?` + filter_vcr_out
	).then((data) => {
		$("#tab_vcr_out").html(`
			<h6 class="fw-normal my-3">GRAFIK VCR ${nama_cctv}</h6>
			<div id="chart-vcr-out"></div>
		`);
		if (data.data) {
			line_chart_vcr(data.data, "#chart-vcr-out", "render", "#BC1C1C");
		} else {
			$("#tab_vcr_out").html(`<em class="d-flex justify-content-center">Tidak ada data</em>`);
		}
		$(".load_in").addClass("d-none");
	});

	// TOP MENTION & SENTIMENT
	let filters_mention_sentiment = {
		category: 'BATAS PROVINSI',
		tgl_start: date_time_filter(filter).start_date,
		tgl_end: date_time_filter(filter).end_date,
		source: 'facebook,instagram,twitter,reddit,youtube,news-blogs,web',
		alert_id: alert_id_global,
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_mention_sentiment = {
			...filters_mention_sentiment,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_mention_sentiment = {
			...filters_mention_sentiment,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_mention_sentiment = {
			...filters_mention_sentiment,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_mention_sentiment = {
			...filters_mention_sentiment,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_top_mention = filter_obj(filters_mention_sentiment);
	let filter_sentiment = filter_obj(filters_mention_sentiment);

	setTimeout(() => {
		$("#top_mention_sentiment_content").html(`
			<h6 class="fw-normal text-center mb-2">TOP MENTION & SENTIMENT</h6>
			<ul class="nav nav-tabs nav-fill" id="tabIN" role="tablist">
				<li class="nav-item" role="presentation">
					<button class="nav-link active" id="mention-tab" data-bs-toggle="tab" data-bs-target="#mention" type="button" role="tab" aria-controls="mention-tab" aria-selected="true">MENTION</button>
				</li>
				<li class="nav-item" role="presentation">
					<button class="nav-link" id="sentiment-tab" data-bs-toggle="tab" data-bs-target="#sentiment" type="button" role="tab" aria-controls="sentiment-tab" aria-selected="false">SENTIMENT</button>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane fade show active px-2" id="mention" role="tabpanel" aria-labelledby="mention">
					<div class="d-flex justify-content-center align-items-center mt-3">
						<div class="spinner-border text-danger" role="status">
							<span class="visually-hidden">Loading...</span>
						</div>
					</div>
				</div>
				<div class="tab-pane fade px-2 overflow-hidden" id="sentiment" role="tabpanel" aria-labelledby="thirty-minute-tab-in">
					<div class="d-flex justify-content-center align-items-center mt-3">
						<div class="spinner-border text-danger" role="status">
							<span class="visually-hidden">Loading...</span>
						</div>
					</div>
				</div>
			</div>
		`)
		
		getDataSmNew(
			`${global_url_api}smart_dashboard/smart_media/top_mention?` + filter_top_mention
		).then((data) => {
			$("#mention").html(""); 
			if (data.data.length > 0) {
				$("#mention").html(`
					<div class="scroll-pane" style="height: 500px !important; overflow: auto;">       
						<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
							<tbody>
								${data.data.map(item => `
									<tr>
										<td>
											<a href="${item.link_mention}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
												<div class="d-flex align-items-center">
													<img src="${global_url_ci + '/my/logo/social-media/' + item.source + '.svg'}" 
														class="rounded-circle" width="35"
														onerror="this.onerror=null; this.src='${global_url_ci + '/my/logo/social-media/' + item.source + '.png'}';"/>
													<div class="mx-3">
														<div class="d-flex align-items-center text-white">
															<img src="${item.icon}" alt="sosmed" class="rounded-circle mx-2" width="25" onerror="this.src='https://app.awario.com/assets/images/elements/no-avatar-4.svg'"/>
															<p class="mb-0 font-weight-bold">${item.user ? item.user : 'Anonim'}</p>
														</div>
													<p class="text-muted small mb-0">${item.post}</p>
													</div>
												</div>
											</a>
										</td>
									</tr>
								`).join("")}
							</tbody>
						</table>
					</div>
				`);
			} else {
				$("#mention").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
			}
					
			$(".load_in").addClass("d-none");
		});
	
		getDataSmNew(
			`${global_url_api}smart_dashboard/smart_media/sentiment?` + filter_sentiment
		).then((data) => {
			$("#sentiment").html(""); 
			if (data.data.length > 0) {
				$("#sentiment").html(`
					<div class="mt-3" id="chart-sentiment" style="width:100%;"></div>
				`);
				conut_chart_statement(data.data, "#chart-sentiment", "render");
			} else {
				$("#sentiment").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
			}
					
			$(".load_in").addClass("d-none");
		});
	}, 2000);

	// INFO KEJADIAN HARI INI
	let filters_kejadian = {
		category: 'BATAS PROVINSI',
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_kejadian = {
			...filters_kejadian,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_kejadian = {
			...filters_kejadian,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_kejadian = {
			...filters_kejadian,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_kejadian = {
			...filters_kejadian,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_kejadian = filter_obj(filters_kejadian);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/event/todays_event_info?` + filter_kejadian
	).then((data) => {
		$("#info_kejadian").html(""); 
		if (data.data.length > 0) {
			$("#info_kejadian").html(`
				<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
					<tbody id="info_kejadian">
						${data.data.map(item => {
							// Format tanggal
							const date_kejadian = new Date(item.tanggal);
							const formattedDateKejadian = date_kejadian.toLocaleDateString("en-GB", {
								day: "2-digit",
								month: "short",
								year: "2-digit",
							}).replace(",", ""); // Hapus koma jika ada
			
							// Format waktu
							const [hh, mm] = item.jam.split(":"); // Ambil jam dan menit
							const formattedTimeKejadian = `${hh.padStart(2, "0")}:${mm}`;
			
							return `
								<tr class="d-flex ${selectedInfoKejadian == item.id ? 'active' : ''} btnkejadian-${item.id}" role="button" 
									onclick="marker_info_kejadian('${global_url_api}smart_dashboard/main_dashboard/event/todays_event_info?${filter_kejadian}', ${item.id}, ${item.id}, ${item.id})">
									<td class="text-white d-flex flex-column w-100">
										<div class="d-flex justify-content-between">
											<div>
												${item.deskripsi}
												<small class="d-block text-muted">${item.nama_jalan}<span class="text-warning d-none">Akan Diperkirakan Macet</span></small>
											</div>
											<div class="text-end">
												${formattedTimeKejadian}
												<small class="d-block text-muted">${formattedDateKejadian}</small>
											</div>
										</div>
										<div class="text-warning text-rekomendasi">
											<span>${item.rekomendasi}</span>
										</div>
									</td>
								</tr>
							`;
						}).join("")}
					</tbody>
				</table>
			`);				
		} else {
			$("#info_kejadian").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
		
		$(".load_in").addClass("d-none");
	});

	// OBJECT VITAL
	let filters_object_vital = {
		category: 'BATAS PROVINSI',
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_object_vital = {
			...filters_object_vital,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_object_vital = {
			...filters_object_vital,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}

	let filter_object_vital = filter_obj(filters_object_vital);
	getDataSmNew(
		`${global_url_api_v2}map/v2/distance-matrix?` + filter_object_vital
	).then((data) => {
		$("#object_vital").html("");
		$("#fasum").html("");
		const vitalItems = data.data.filter(item => item.type == 'vital');
		const publicItems = data.data.filter(item => item.type == 'public');
		if (data.data.length > 0 && vitalItems.length > 0) {
			$("#object_vital").html(`
				<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
					<tbody>
						${data.data.filter(item => item.type == 'vital').map((item, key) => `
							<tr class="${selectedBtnAnalisaWaktu == key ? 'active' : ''} btnvital-${key}" role="button" onclick="rute_analisa_waktu(\`${item.start_coordinates}\`, \`${item.end_coordinates}\`, ${key}, 'vital')">
								<td class="text-white d-flex flex-column">
									<div class="d-flex justify-content-between">
										<div class="w-75">
											${item.start_name + ' - ' + item.end_name}
											<span class="alert-icon d-none">⚠</span>
											<small class="d-block text-muted">• ${item.average_speed}<span class="text-warning d-none">Akan Diperkirakan Macet</span></small>
										</div>
										<div class="w-25 text-end">
											${item.distance}
											<small class="d-block text-muted">${item.duration}</small>
										</div>
									</div>
									<div class="w-100">
										<small class="d-block text-muted">• Estimasi BBM</small>
										<div class="d-flex gap-2 mt-1">
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/mobil.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_car}</small>
											</div>
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/motor.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_motor}</small>
											</div>
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/bus.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_bus}</small>
											</div>
										</div>
									</div>
								</td>
							</tr>
						`).join("")}
					</tbody>
				</table>
			`);
		} else {
			$("#object_vital").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}

		if (data.data.length > 0 && publicItems.length > 0) {
			$("#fasum").html(`
				<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
					<tbody>
						${data.data.filter(item => item.type == 'public').map((item, key) => `
							<tr class="${selectedBtnAnalisaWaktu == key ? 'active' : ''} btnpublic-${key}" role="button" onclick="rute_analisa_waktu(\`${item.start_coordinates}\`, \`${item.end_coordinates}\`, ${key}, 'public')">
								<td class="text-white d-flex flex-column">
									<div class="d-flex justify-content-between">
										<div class="w-75">
											${item.start_name + ' - ' + item.end_name}
											<span class="alert-icon d-none">⚠</span>
											<small class="d-block text-muted">• ${item.average_speed}<span class="text-warning d-none">Akan Diperkirakan Macet</span></small>
										</div>
										<div class="w-25 text-end">
											${item.distance}
											<small class="d-block text-muted">${item.duration}</small>
										</div>
									</div>
									<div class="w-100">
										<small class="d-block text-muted">• Estimasi BBM</small>
										<div class="d-flex gap-2 mt-1">
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/mobil.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_car}</small>
											</div>
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/motor.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_motor}</small>
											</div>
											<div>
												<img src="${global_url_ci}/my/logo/analisa-waktu/bus.png" width="15" height="15" alt="">
												<small class="text-muted">${item.consume_fuel_bus}</small>
											</div>
										</div>
									</div>
								</td>
							</tr>
						`).join("")}
					</tbody>
				</table>
			`);
		} else {
			$("#object_vital").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
					
		$(".load_in").addClass("d-none");
	});

	// RUNNING TEXT
	let filters_runningtext = {
		category: 'BATAS PROVINSI',
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_runningtext = {
			...filters_runningtext,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_runningtext = {
			...filters_runningtext,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}

	let filter_runningtext = filter_obj(filters_runningtext);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/accident/prevention?` + filter_runningtext
	).then((data) => {
		$(".ticker-text").html(""); 
		if (data.data.length > 0) {
			$(".ticker-text").html(data.data.map(item => item.rekomendasi).join("  |  "));
		} else {
			$(".ticker-text").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
	});
	
	// PENCEMARAN UDARA
	let filters_pencemaran_udara = {
		lat: `${global_lat_center}`,
		long: `${global_lng_center}`
	};
	
	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_pencemaran_udara = {
			...filters_pencemaran_udara,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_pencemaran_udara = {
			...filters_pencemaran_udara,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_pencemaran_udara = {
			...filters_pencemaran_udara,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_pencemaran_udara = {
			...filters_pencemaran_udara,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_pencemaran_udara = filter_obj(filters_pencemaran_udara);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/polution/quality-air?` + filter_pencemaran_udara
	).then((data) => {
		$("#pencemaran_udara_content").html(`	
			<h6 class="fw-normal text-center mb-2">INDEKS PENCEMARAN UDARA</h6>
			<div class="d-flex flex-column" id="pencemar_udara"></div>
		`); 

		const airQuality = data.data.air_quality.aqi; // Ambil nilai kualitas udara
		const airQualityClasses = {
			1: "good",       // Baik
			2: "fair",       // Cukup
			3: "moderate",   // Sedang
			4: "poor",       // Buruk
			5: "very-poor"   // Sangat Buruk
		};

		const airQualityClass = airQualityClasses[airQuality];
		if (data.status) {
			$("#pencemar_udara").html(`
				<div class="d-flex justify-content-start gap-3 align-items-center">
					<div class="text-dark rounded fw-bold py-3 px-4 ${airQualityClass}">${data.data.air_quality.aqi}</div>
					<div class="d-flex flex-column">
						<span class="fw-bold text-${airQualityClass}">${data.data.air_quality.status}</span>
						<span class="small text-secondary">${data.data.air_quality.description}</span>
					</div>
				</div>
				<hr class="border-secondary">
				<div class="d-flex gap-3">
					<div class="d-flex gap-2 mb-2">
						<img src="${global_url_ci}/my/logo/weather/temp.png" width="20" alt="">
						<b>${data.data.weather.temperature}</b>
					</div>
					<div class="d-flex gap-2 mb-2">
						<img src="${global_url_ci}/my/logo/weather/humidity.png" width="20" alt="">
						<b>${data.data.weather.humidity}</b>
					</div>
					<div class="d-flex gap-2 mb-2">
						<img src="${global_url_ci}/my/logo/weather/wind.png" width="20" alt="">
						<b>${data.data.weather.wind_speed}</b>
					</div>
				</div>
				<p class="small text-secondary" id="datetime"></p>

				<!-- Tooltip Box -->
				<div class="card tooltip-box" style="background: #000C1E">
					<div class="card-body p-1">
						<div class="d-flex align-items-center">
							<div class="good text-dark fw-semibold px-3">1</div>
							<div class="mx-3">Baik</div>
						</div>
						<div class="d-flex align-items-center">
							<div class="fair text-dark fw-semibold px-3">2</div>
							<div class="mx-3">Cukup</div>
						</div>
						<div class="d-flex align-items-center">
							<div class="moderate text-dark fw-semibold px-3">3</div>
							<div class="mx-3">Sedang</div>
						</div>
						<div class="d-flex align-items-center">
							<div class="poor text-dark fw-semibold px-3">4</div>
							<div class="mx-3">Buruk</div>
						</div>
						<div class="d-flex align-items-center">
							<div class="very-poor text-dark fw-semibold px-3">5</div>
							<div class="mx-3">Sangat Buruk</div>
						</div>
					</div>
				</div>
			`);
		} else {
			$("#pencemar_udara").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
	});

	// PELANGGARAN
	let filters_pelanggaran = {
		category: 'BATAS PROVINSI',
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_pelanggaran = {
			...filters_pelanggaran,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_pelanggaran = {
			...filters_pelanggaran,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_pelanggaran = {
			...filters_pelanggaran,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_pelanggaran = {
			...filters_pelanggaran,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_pelanggaran = filter_obj(filters_pelanggaran);
	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_enforcement/jalan?kategori_pelanggaran=PERDA` + filter_pelanggaran
	).then((data) => {
		$("#pelanggaran_content").html(`	
			<h6 class="fw-normal text-center mb-2">PELANGGARAN</h6>
			<div class="d-flex flex-column" id="t_list_pelanggaran"></div>	
		`);
		let scrollStyle = data.data.length > 5 ? 'height: 300px !important; overflow: auto;' : '';
		
		if (data.data.perda.length > 0) {
			$("#t_list_pelanggaran").html(`
				<div class="scroll-pane" style="${scrollStyle}">
					<table class="table table-hover custom-table-road-safety mb-0">
						<thead>
							<tr>
								<th>JALAN</th>
								<th>JUMLAH</th>
							</tr>
						</thead>
						<tbody>
							${data.data.perda.map((item, key) => `
								<tr>
									<td>${item.nama_jalan}</td>
									<td>${item.total}</td>
								</tr>
							`).join("")}
						</tbody>
					</table>
				</div>
			`)
		} else {
			$("#t_list_pelanggaran").html(`
				<table class="table table-hover custom-table-road-safety">
					<thead>
						<tr>
							<th>JALAN</th>
							<th>JUMLAH</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="text-center" colspan="3">
								<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>
							</td>
						</tr>
					</tbody>
				</table>	
			`);
		}
	});
	
	// CROWD COUNTING
	let filters_crowd_counting = {
		nama_cctv: nama_cctv,
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_crowd_counting = {
			...filters_crowd_counting,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_crowd_counting = {
			...filters_crowd_counting,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_crowd_counting = {
			...filters_crowd_counting,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_crowd_counting = {
			...filters_crowd_counting,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_crowd_counting = filter_obj(filters_crowd_counting);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/crowd/rekap_crowd_counting?` + filter_crowd_counting
	).then((data) => {
		$("#crowd_counting_content").html(`
			<h6 class="fw-normal text-center mb-2">CROWD COUNTING</h6>
			<div class="d-flex flex-column" id="t_list_crowd_counting"></div>
		`);

		let scrollStyle = data.data.length > 5 ? 'height: 300px !important; overflow: auto;' : '';
		if (data.data.length > 0) {
			$("#t_list_crowd_counting").html(`
				<div class="scroll-pane" style="${scrollStyle}">
					<table class="table table-hover custom-table-road-safety mb-0">
						<thead>
							<tr>
								<th>CCTV</th>
								<th>PRIA</th>
								<th>WANITA</th>
							</tr>
						</thead>
						<tbody>
							${data.data.map((item, key) => `
								<tr>
									<td>${item.nama_cctv}</td>
									<td>${item.pria}</td>
									<td>${item.wanita}</td>
								</tr>
							`).join("")}
						</tbody>
					</table>
				</div>
			`)
		} else {
			$("#t_list_crowd_counting").html(`
				<table class="table table-hover custom-table-road-safety">
					<thead>
						<tr>
							<th>CCTV</th>
							<th>PRIA</th>
							<th>WANITA</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="text-center" colspan="3">
								<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>
							</td>
						</tr>
					</tbody>
				</table>	
			`);
		}
	});
	
	function updateTime() {
		const now = new Date();
		const options = { 
		  timeZone: global_timezone, 
		  hour12: false, 
		  hour: '2-digit', 
		  minute: '2-digit' 
		};
		
		const time = now.toLocaleTimeString('id-ID', options);
		const date = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
		$("#datetime").html(`${global_weather_name} • ${date} • ${time}`);
	}
	
	updateTime(); // Update saat pertama kali dimuat
	setInterval(updateTime, 1000); // Perbarui setiap detik

	// REKOMENDASI PENCEGAHAN
	let filters_pencegahan = {
		category: 'BATAS PROVINSI',
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_pencegahan = {
			...filters_pencegahan,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_pencegahan = {
			...filters_pencegahan,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}

	let filter_pencegahan = filter_obj(filters_pencegahan);
	getDataSmNew(
		`${global_url_api}smart_dashboard/main_dashboard/accident/prevention?` + filter_pencegahan
	).then((data) => {
		
		$("#rawan_laka").html(`
			<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
				<tbody id="t_list_rawan_laka"></tbody>
			</table>
		`);
		
		$("#rawan_langgar").html(`
			<table class="table table-hover table-hover-custom-road" style="border-bottom: 1px solid #8e8e8e;">
				<tbody id="t_list_rawan_langgar"></tbody>
			</table>
		`);
		
		const lakaItems = data.data.map((item, key) => ({ ...item, originalIndex: key })).filter(item => item.type === 'laka');
		const langgarItems = data.data.map((item, key) => ({ ...item, originalIndex: key })).filter(item => item.type === 'langgar');
		
		if (lakaItems.length > 0) {
			$("#t_list_rawan_laka").html(`
				${lakaItems.map(item => `
					<tr class="d-flex align-items-start ${selectedBtnRawanIrsms == item.originalIndex ? 'active' : ''} btnrawanlaka-${item.originalIndex}" 
						role="button" 
						onclick='rawan_heat(${item.originalIndex + 1}, ${item.originalIndex}, "laka")'>
						<td class="cctv-name text-white" style="cursor: pointer;">
							${item.rekomendasi}
							<small class="d-block text-muted">• ${item.total + ' Titik'}</small>
						</td>
					</tr>
				`).join("")}
			`);
		} else {
			$("#rawan_laka").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
		
		if (langgarItems.length > 0) {
			$("#t_list_rawan_langgar").html(`
				${langgarItems.map(item => `
					<tr class="d-flex align-items-start ${selectedBtnRawanIrsms == item.originalIndex ? 'active' : ''} btnrawanlanggar-${item.originalIndex}" 
						role="button" 
						onclick='rawan_heat(${item.originalIndex + 1}, ${item.originalIndex}, "langgar")'>
						<td class="cctv-name text-white" style="cursor: pointer;">
							${item.rekomendasi}
							<small class="d-block text-muted">• ${item.total + ' Titik'}</small>
						</td>
					</tr>
				`).join("")}
			`);
		} else {
			$("#rawan_langgar").html(`<em class="mt-2 d-flex justify-content-center">Tidak ada data</em>`);
		}
					
		$(".load_in").addClass("d-none");
	});
	
	// GET CCTV
	let filters_cctv_in_out = {
		category: 'BATAS PROVINSI',
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
	};

	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_cctv_in_out = {
			...filters_cctv_in_out,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_cctv_in_out = {
			...filters_cctv_in_out,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_cctv_in_out = {
			...filters_cctv_in_out,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_cctv_in_out = {
			...filters_cctv_in_out,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

	let filter_cctv = filter_obj(filters_cctv_in_out);
	getDataSmNew(
		`${global_url_api}sm_integration/vcr?` + filter_cctv + '&status_cctv=IN'
	).then((data) => {
		if (data.data.length > 0) {
			$("#kinerja_ruas_jalan_in").html(`
				<h6 class="fw-normal mb-2 mt-4">KINERJA RUAS JALAN</h6>
				<div class="swiper" id="swiper-stream-in"></div>
				<div class="d-flex flex-wrap justify-content-center">
					<div class="d-flex flex-column m-2">
						<h5 class="text-primary fw-bold" id="vc_rasio_in"></h5>
						<small>VC Rasio</small>
					</div>
					<div class="d-flex flex-column m-2">
						<h5 class="text-primary fw-bold" id="vcr_category_in"></h5>
						<small>Level of Service</small>
					</div>
				</div>
			`);

			vcr_stream_in = data.data;
            $("#swiper-stream-in").html(`
                <div class="swiper-wrapper">
                    ${data.data.map((item, key) => `
                        <div class="swiper-slide swipe-in" id="stream_in_${key}" data-cam-id="${item.id}">
                            <video class="d-none border border-2 border-primary" preload="none" muted playsinline></video>
                            <img src="${global_url_ci}/my/img_/thumbnailcctv.png" class="border border-2 border-primary" style="filter: blur(2px)">
                            <h6 class="fw-normal mt-3 w-50 mx-auto">${item.nama_cctv}</h6>
                        </div>
                    `).join("")}
                </div>
                <div class="swiperBtnPrevIn swiper-button-prev"></div>
                <div class="swiperBtnNextIn swiper-button-next"></div>
            `);

            if (swiperIN) swiperIN.destroy(true, true);

            swiperIN = new Swiper("#swiper-stream-in", {
                effect: "coverflow",
				grabCursor: false,
				centeredSlides: true,
				slidesPerView: "auto",
				loop: true,
				autoplay: {
					delay: 30000,
					disableOnInteraction: false,
					pauseOnMouseEnter: true
				},
				coverflowEffect: {
					rotate: 0,
					stretch: 50,
					depth: 150,
					modifier: 2,
					slideShadows: false,
				},
				allowTouchMove: false,
                navigation: { nextEl: "#swiper-stream-in .swiperBtnNextIn", prevEl: "#swiper-stream-in .swiperBtnPrevIn" },
                on: {
                    init: function() { 
						handleSwiperChange(this, 'in', vcr_stream_in);
					},
                    slideChange: function() {
						handleSwiperChange(this, 'in', vcr_stream_in);
					}
                }
            });
		}
	});

	getDataSmNew(
		`${global_url_api}sm_integration/vcr?` + filter_cctv + '&status_cctv=OUT'
	).then((data) => {
		if (data.data.length > 0) {
			$("#kinerja_ruas_jalan_out").html(`
                <h6 class="fw-normal mb-2 mt-4">KINERJA RUAS JALAN</h6>
                <div class="swiper" id="swiper-stream-out"></div>
                <div class="d-flex flex-wrap justify-content-center">
                    <div class="d-flex flex-column m-2"><h5 class="text-danger fw-bold" id="vc_rasio_out"></h5><small>VC Rasio</small></div>
                    <div class="d-flex flex-column m-2"><h5 class="text-danger fw-bold" id="vcr_category_out"></h5><small>Level of Service</small></div>
                </div>
            `);

            vcr_stream_out = data.data;
            $("#swiper-stream-out").html(`
                <div class="swiper-wrapper">
                    ${data.data.map((item, key) => `
                        <div class="swiper-slide swipe-out" id="stream_out_${key}" data-cam-id="${item.id}">
                            <video class="d-none border border-2 border-danger" preload="none" muted playsinline></video>
                            <img src="${global_url_ci}/my/img_/thumbnailcctv.png" class="border border-2 border-danger" style="filter: blur(2px)">
                            <h6 class="fw-normal mt-3 w-50 mx-auto">${item.nama_cctv}</h6>
                        </div>
                    `).join("")}
                </div>
                <div class="swiperBtnPrevOut swiper-button-prev"></div>
                <div class="swiperBtnNextOut swiper-button-next"></div>
            `);

            if (swiperOUT) swiperOUT.destroy(true, true);

            swiperOUT = new Swiper("#swiper-stream-out", {
                effect: "coverflow",
				grabCursor: false,
				centeredSlides: true,
				slidesPerView: "auto",
				loop: true,
				autoplay: {
					delay: 30000,
					disableOnInteraction: false,
					pauseOnMouseEnter: true
				},
				coverflowEffect: {
					rotate: 0,
					stretch: 50,
					depth: 150,
					modifier: 2,
					slideShadows: false,
				},
				allowTouchMove: false,
                navigation: { nextEl: "#swiper-stream-out .swiperBtnNextOut", prevEl: "#swiper-stream-out .swiperBtnPrevOut" },
                on: {
                    init: function() { 
						handleSwiperChange(this, 'out', vcr_stream_out);
					},
                    slideChange: function() { 
						handleSwiperChange(this, 'out', vcr_stream_out);
					}
                }
            });
		} else {
			$("#kinerja_ruas_jalan_out").html("");
		}
	});

	function handleSwiperChange(swiperInstance, groupKey, dataList) {
		cleanupPC(groupKey);
		var timer = (groupKey === 'in') ? debounceTimerIn : debounceTimerOut;
		if (timer) clearTimeout(timer);

		const activeIndex = swiperInstance.realIndex;
		activeIndexes[groupKey] = swiperInstance.activeIndex;
	
		const delay = 500;

		const executeStream = () => {
			const activeSlideEl = $(swiperInstance.slides[swiperInstance.activeIndex]);
			const videoEl = activeSlideEl.find('video')[0];
			const camId = activeSlideEl.attr('data-cam-id');
	
			if (!videoEl || !camId) return;
	
			const wrapperId = groupKey === 'in' ? '#swiper-stream-in' : '#swiper-stream-out';
			$(`${wrapperId} .swiper-slide`).each(function() {
				if ($(this).hasClass('swiper-slide-active') || $(this).hasClass('swiper-slide-duplicate-active')) {
					$(this).find('video').removeClass('d-none');
					$(this).find('img').addClass('d-none');
				} else {
					$(this).find('video').addClass('d-none').removeAttr('src');
					$(this).find('img').removeClass('d-none');
				}
			});
			
			// Start Stream
			loadStream(groupKey, camId, videoEl, swiperInstance.activeIndex); 
		};

		if (groupKey === 'in') {
			debounceTimerIn = setTimeout(executeStream, delay);
		} else {
			debounceTimerOut = setTimeout(executeStream, delay);
		}
	}
	
	$("#pencemaran_udara_content").hover(
		function() {
			$(".tooltip-box").fadeIn(200); // Munculkan tooltip
		},
		function() {
			$(".tooltip-box").fadeOut(200); // Sembunyikan tooltip
		}
	);

	$("#alert_summaryIn").hover(
		function() {
			let tooltip = $("#alert_hover_summaryIn");
			tooltip.appendTo("body") // Pindahkan tooltip ke body agar tidak tertutup
				.removeClass("d-none")
				.fadeIn(200);
		},
		function() {
			let tooltip = $("#alert_hover_summaryIn");
			tooltip.addClass("d-none").fadeOut(200, function() {
				tooltip.appendTo("#alert_summaryIn").css({ position: "", top: "", left: "" }); // Kembalikan tooltip ke elemen asalnya
			});
		}
	);

	t_out_road_safety_batas_provinsi_cctv = setTimeout(() => {
		$("#bottom-content").html(``);
		$("#konten-kiri").html(``);
		$("#konten-kanan").html(``);
		if (nama_cctv != "") {
			dash_road_safety_perbatasan_provinsi_cctv(nama_cctv, {
				start_date: date_time_filter(filter).start_date,
				end_date: date_time_filter(filter).end_date,
			});
		} else {
			dash_road_safety_perbatasan_provinsi({
				start_date: date_time_filter(filter).start_date,
				end_date: date_time_filter(filter).end_date,
			});
		}
	}, 300000);

	autoScroll()
}

function formatNumberWithThousandSeparator(number) {
	return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}