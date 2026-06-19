function dash_enforcement_lalin(filter={}) {
  cleartimeout()
  // add_geo(geojsonFiles)

  let param1 = {
    start_date:date_time_filter(filter).start_date,
    end_date:date_time_filter(filter).end_date,
    polda:global_polda,
    kategori_pelanggaran:"LALIN"
  }
  
  paramsPolres ? (param1["polres"] = paramsPolres) : "";
  if ($('#f-polres').val() != undefined) {
    if ($('#f-polres').val() != '') {
      param1.polres = $('#f-polres').val().split(',')[0]
    }
  }

  let filter_ = filter_obj(param1);

  let fltr
  if (filter.date != undefined) {
    fltr = filter.date
  }else{
    fltr = date_time_filter().date_now
  }

  // konten kiri
  getDataSmNew(`${global_url_api}smart_dashboard/smart_enforcement?${filter_}`)
  .then(data => {
    $("#konten-kiri").append(`
    <div class="mt-4">
      <h4 class="text-white text-center w-75 m-auto">
      DETEKSI PELANGGARAN PERATURAN LALU LINTAS
      </h4>
      <div class="row mt-4">
        <div class="col mt-1 p-1">
          <div class="card text-white text-center card-bt-biru" role="button" onClick="konten_kanan_lalin('${filter_}', '')">
            <div class="card-body">
            <h6 class="mb-2 fw-lighter">Total Pelanggaran</h6>
            <h3 class="">${data.data.pelanggaran_lalin.total}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-4" id="list_img_lalin">
    </div>
  `);
  if (data.data.pelanggaran_lalin.data.length > 0) {
    data.data.pelanggaran_lalin.data.forEach(item => {
      $('#list_img_lalin').append(`
        <div class="col-4" role="button" onClick="konten_kanan_lalin('${filter_}', '&jenis_pelanggaran=${item.jenis_pelanggaran.toUpperCase()}')">
          <div class="text-center"><img src='${item.icon}' alt=""></div>
          <div class="text-center"><h5 class="text-white"><small>${item.jenis_pelanggaran}</small></h5></div>
          <div class="text-center"><h5 class="text-white">${item.jumlah}</h5></div>
        </div>
      `)
    });
  }else {
    $('#list_img_lalin').append(`
      <div class="text-white m-auto mt-5 text-center"><h5>Tidak Ada Data</h5></div></div>
    `)
  }
  for (let a = 0; a < data.data.pelanggaran_lalin.list_cctv.length; a++) {
    heatmapDataEnforcementLalin.push({location: new google.maps.LatLng(parseFloat(data.data.pelanggaran_lalin.list_cctv[a].lat), parseFloat(data.data.pelanggaran_lalin.list_cctv[a].lng)), weight:parseInt(data.data.pelanggaran_lalin.list_cctv[a].jumlah)})
  }
  // Buat objek heatmap (menggunakan Circle overlay, HeatmapLayer sudah deprecated di v3.65+)
  heatmap_enforcement_lalin = createHeatmapCircles(map, heatmapDataEnforcementLalin, 300);
  })
  
  // Konten Kanan
  konten_kanan_lalin(`${filter_}`, "");

  t_out_enforcement_lalin =setTimeout(() => {
    // clearTimeout(t_out_enforcement_lalin)
    dash_enforcement_lalin({date : fltr})
    $('#konten-kiri').html('');
    $('#title-konten-tengah').html(`${global_app_name}`);
    $('#subtitle-konten-tengah').html('Smart Enforcement - Pelanggaran Lalin')
    $('#konten-kanan').html('');
    
    // hapus_geo()
  }, 300000);
}

function konten_kanan_lalin(filter = "", jenis_pelanggaran = "") {
	getDataSmNew(
		`${global_url_api}smart_dashboard/smart_enforcement/jalan?${filter}${jenis_pelanggaran}`
	).then((data) => {
		$("#konten-kanan").html("");
		$("#konten-kanan").append(`
      <div class="mt-4" style="height: 500px !important;overflow: auto;">
        <table class="table table-hover custom-table">
            <thead>
              <tr>
                <th class="text-center">No</th>
                <th>Jalan</th>
                <th class="text-center">Jumlah</th>
              </tr>
            </thead>
            <tbody id="t_list_jl_enforcement_lalin">
            </tbody>
          </table>
      </div>
    `);

		$("#t_list_jl_enforcement_lalin").html("");
		if (data.data.lalin.length > 0) {
			data.data.lalin.forEach((e, no) => {
				$("#t_list_jl_enforcement_lalin").append(`
          <tr>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-light" disabled>${
							no + 1
						}</button></td>
            <td>${e.nama_jalan}</td>
            <td class="text-center">${e.total}</td>
          </tr>
        `);
			});
		} else {
			$("#t_list_jl_enforcement_lalin").append(`
          <tr>
            <td colspan="3" class="text-center">-</td>
          </tr>
        `);
		}
	});
}