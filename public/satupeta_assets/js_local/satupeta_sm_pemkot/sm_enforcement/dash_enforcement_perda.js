function dash_enforcement_perda(filter={}) {
  // hapus_geo()
  cleartimeout()
  // add_geo(geojsonFiles)

  let param1 = {
    start_date:date_time_filter(filter).start_date,
    end_date:date_time_filter(filter).end_date,
    polda:global_polda,
    kategori_pelanggaran:'PERDA',
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
      DETEKSI PELANGGARAN PERATURAN DAERAH
      </h4>
      <div class="row mt-4">
        <div class="col mt-1 p-1">
          <div class="card text-white text-center card-bt-kuning" role="button" onClick="konten_kanan_perda('${filter_}', '')">
            <div class="card-body">
            <h6 class="mb-2 fw-lighter">Total Pelanggaran</h6>
            <h3 class="">${data.data.pelanggaran_perda.total}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-4" id="list_img_perda">
    </div>
    `);
    if (data.data.pelanggaran_perda.data.length > 0) {
      data.data.pelanggaran_perda.data.forEach(item => {
        $('#list_img_perda').append(`
        <div class="col-4" role="button" onClick="konten_kanan_perda('${filter_}', '&jenis_pelanggaran=${item.jenis_pelanggaran.toUpperCase()}')">
            <div class="text-center"><img src='${item.icon}' alt=""></div>
            <div class="text-center"><h5 class="text-white"><small>${item.jenis_pelanggaran}</small></h5></div>
            <div class="text-center"><h5 class="text-white">${item.jumlah}</h5></div>
          </div>
        `)
      });
    }else {
      $('#list_img_perda').append(`
        <div class="text-white m-auto mt-5 text-center"><h5>Tidak Ada Data</h5></div></div>
      `)
    }

    for (let a = 0; a < data.data.pelanggaran_perda.list_cctv.length; a++) {
      heatmapDataEnforcementPerda.push({location: new google.maps.LatLng(parseFloat(data.data.pelanggaran_perda.list_cctv[a].lat), parseFloat(data.data.pelanggaran_perda.list_cctv[a].lng)), weight:parseInt(data.data.pelanggaran_perda.list_cctv[a].jumlah)})
    }
    // Buat objek heatmap (menggunakan Circle overlay, HeatmapLayer sudah deprecated di v3.65+)
    heatmap_enforcement_perda = createHeatmapCircles(map, heatmapDataEnforcementPerda, 300);
  })
  
  // Konten Kanan
  konten_kanan_perda(`${filter_}`, '');

  t_out_enforcement_perda =setTimeout(() => {
    // clearTimeout(t_out_enforcement_perda)
    dash_enforcement_perda({date : fltr})
    $('#konten-kiri').html('');
    $('#title-konten-tengah').html(`${global_app_name}`);
    $('#subtitle-konten-tengah').html('Smart Enforcement - Pelanggaran Perda')
    $('#konten-kanan').html('');
    
    // hapus_geo()
  }, 300000);
}


function konten_kanan_perda(filter="", jenis_pelanggaran="") {
  getDataSmNew(`${global_url_api}smart_dashboard/smart_enforcement/jalan?${filter}${jenis_pelanggaran}`)
  .then(data => {
    $('#konten-kanan').html('')
    $('#konten-kanan').append(`
      <div class="mt-4" style="height: 500px !important;overflow: auto;">
        <table class="table table-hover custom-table">
            <thead>
              <tr>
                <th class="text-center">No</th>
                <th>Jalan</th>
                <th class="text-center">Jumlah</th>
              </tr>
            </thead>
            <tbody id="t_list_jl_enforcement_perda">
            </tbody>
          </table>
      </div>
    `)

    $('#t_list_jl_enforcement_perda').html('');
    if (data.data.perda.length > 0) {
      data.data.perda.forEach((e,no) => {
        $('#t_list_jl_enforcement_perda').append(`
          <tr>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-light" disabled>${no+1}</button></td>
            <td>${e.nama_jalan}</td>
            <td class="text-center">${e.total}</td>
          </tr>
        `)
      });
    }else {
      $('#t_list_jl_enforcement_perda').append(`
          <tr>
            <td colspan="3" class="text-center">-</td>
          </tr>
        `)
    }
  })
}