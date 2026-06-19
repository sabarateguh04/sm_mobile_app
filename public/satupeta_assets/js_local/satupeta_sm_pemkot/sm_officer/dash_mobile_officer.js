function hitungJumlahMobilPerGroup(data) {
  const hasil = {
		KORLANTAS: 0,
		DINKES: 0,
		DAMKAR: 0,
		DISHUB: 0,
		SATPOLPP: 0,
		BPBD: 0,
	};

  data.forEach((mobil) => {
    const { vehiclegroup } = mobil;

    // Jika merek belum ada dalam hasil, tambahkan dengan jumlah 1
    if (!hasil[vehiclegroup]) {
      hasil[vehiclegroup] = 1;
    } else {
      // Jika merek sudah ada, tambahkan jumlahnya
      hasil[vehiclegroup]++;
    }
  });

  return hasil;
}
function generateRandomHexColor() {
  // Generate random values for red, green, and blue components
  var red = Math.floor(Math.random() * 256);
  var green = Math.floor(Math.random() * 256);
  var blue = Math.floor(Math.random() * 256);

  // Convert decimal values to hexadecimal
  var redHex = red.toString(16).padStart(2, '0');
  var greenHex = green.toString(16).padStart(2, '0');
  var blueHex = blue.toString(16).padStart(2, '0');

  // Concatenate the components to form the hex color code
  var hexColor = '#' + redHex + greenHex + blueHex;

  return hexColor;
}
function dash_mobile_officer(filter= {}) {
  // add_geo(geojsonFiles)
  let param1 = {
		start_date: date_time_filter(filter).start_date,
		end_date: date_time_filter(filter).end_date,
		polda_id: global_polda_id,
	};

	paramsPolres ? (param1["polres"] = paramsPolres) : "";
	let filter_ = filter_obj(param1);

  // konten kiri
  getDataSmNew(`${global_url_api}smart_dashboard/smart_mobile_officer?${filter_}`)
  .then(data => {
    $('#konten-kiri').append(`
    <div class="mt-4">
      <span class="d-flex align-items-center justify-content-center"><img src="${global_url_ci}my/logo/logo_petugas.png" width="70" alt=""> <h5 class="text-white ms-3">PETUGAS KEPOLISIAN</h5></span>
      <div class="row">
        <div class="col mt-1 p-1">
          <div class="card text-white text-center card-bt-abu">
            <div class="card-body">
            <h6 class="mb-2 fw-lighter">Total Petugas</h6>
            <h3 class="">${data.data.total_petugas}</h3>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col mt-1 p-1">
          <div class="card text-white text-center card-bt-hijau">
            <div class="card-body">
            <h6 class="mb-2 fw-lighter">Petugas Online</h6>
            <h3 class="">${data.data.petugas_online}</h3>
            </div>
          </div>
        </div>
        <div class="col mt-1 p-1">
          <div class="card text-white text-center card-bt-merah">
            <div class="card-body">
            <h6 class="mb-2 fw-lighter">Petugas Offline</h6>
            <h3 class="">${data.data.petugas_offline}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="konten-kiri-2">
    <div class="mt-4">
          <h6 class="text-white text-center">
              TRACKING KENDARAAN
          </h6>
          <div class="row">
            <div class="col mt-1 p-1">
              <div class="card text-white text-center card-bt-biru">
                <div class="card-body">
                <h6 class="mb-2 fw-lighter">Total Tracking Kendaraan</h6>
                <h3 class="">0</h3>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-4">
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/POLISI.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Polisi</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">0</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/AMBULAN.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Ambulans</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">0</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/DAMKAR.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Damkar</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">0</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/DISHUB.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Dishub</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">0</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/SATPOLPP.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Satpol PP</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">0</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/BPBD.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">BPBD</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">0</h5></div>
          </div>
        </div>
    </div>
  `)
  })

  // konten kanan
  setTimeout(() => {
    getDataSmNew(global_url_api+`sm_integration/indicar_list_vehicle?token=${indicarKey.indicarToken}`).then((data) => {
      let mo = hitungJumlahMobilPerGroup(data.data.dataset.vehicles)
      
      $('#konten-kiri-2').html(`
        <div class="mt-4">
          <h6 class="text-white text-center">
              TRACKING KENDARAAN
          </h6>
          <div class="row">
            <div class="col mt-1 p-1">
              <div class="card text-white text-center card-bt-biru">
                <div class="card-body">
                <h6 class="mb-2 fw-lighter">Total Tracking Kendaraan</h6>
                <h3 class="">${data.data.dataset.vehicles.length}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-4">
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/POLISI.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Polisi</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">${mo.KORLANTAS}</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/AMBULAN.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Ambulans</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">${mo.DINKES}</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/DAMKAR.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Damkar</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">${mo.DAMKAR}</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/DISHUB.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Dishub</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">${mo.DISHUB}</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/SATPOLPP.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">Satpol PP</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">${mo.SATPOLPP}</h5></div>
          </div>
          <div class="row align-items-center mb-3 ms-3">
            <div class="col-3"><img src="../my/logo/BPBD.png" alt=""></div>
            <div class="col-4"><h5 class="text-white text-muted fw-lighter">BPBD</h5></div>
            <div class="col-5"><h5 class="text-white ms-4">${mo.BPBD}</h5></div>
          </div>
        </div>
      `)

  
      get_petugas("")
      get_mobil("");
    });
    dash_mobile_officer_kanan()
  }, 2000);

  
  t_out_mobile_officer =setTimeout(() => {
    // clearTimeout(t_out_mobile_officer)
    dash_mobile_officer({start_date: date_time_filter(filter).start_date, end_date:date_time_filter(filter).end_date})
    $('#konten-kiri').html('');
    $('#title-konten-tengah').html(`${global_app_name}`);
    $('#subtitle-konten-tengah').html('Smart Mobile Officer')
    $('#konten-kanan').html('');
    
    // hapus_geo()
  }, 300000);
}

function dash_mobile_officer_kanan(filter={}) {
  $('#konten-kanan').html(`
    <div class="mt-4">
      <span class="d-flex align-items-center justify-content-center"><img src="${global_url_ci}my/logo/officer.png" width="70" alt=""> <h5 class="text-white ms-3">OPERASI DAN PENGENDALIAN </h5></span>
    </div>
    <div class="mt-2">
      <h4 class="text-white">GIAT RUTIN</h4>
      <table class="table table-hover-custom custom-table">
        <tbody id="t_giat_rutin">
        </tbody>
      </table>
    </div>
    <div class="mt-2">
      <h4 class="text-white">OPS KEPOLISIAN</h4>
      <table class="table table-hover-custom custom-table">
        <tbody id="t_ops_kepolisian">
        </tbody>
      </table>
    </div>
    <div class="mt-2">
      <h4 class="text-white">PENGAWALAN</h4>
      <table class="table table-hover-custom custom-table">
        <tbody id="t_pengawalan">
        </tbody>
      </table>
    </div>
  `)
  
  let param1 = {
		tgl_start: date_time_filter(filter).start_date,
		tgl_end: date_time_filter(filter).end_date,
		polda: global_polda,
	};
	paramsPolres ? (param1["polres"] = paramsPolres) : "";
	let filter_ = filter_obj(param1);
  getDataSmNew(`${global_url_api}smart_dashboard/smart_mobile_officer/laphar_opsdal?${filter_}&laphar=DATA GIAT RUTIN`)
  .then(data=>  {
    data = data.data
    if (data.length > 0) {
      data.forEach((v,no) => {
        $('#t_giat_rutin').append(`
          <tr>
            <td>${no+1}</td>
            <td>${v.jenis_kegiatan}</td>
            <td>${v.jumlah_kegiatan}</td>
          </tr>
        `)
      });
    } else {
      $('#t_giat_rutin').append(`
          <tr>
            <td>TIDAK ADA GIAT RUTIN</td>
          </tr>
        `)
    }
  })

  getDataSmNew(`${global_url_api}smart_dashboard/smart_mobile_officer/laphar_opsdal?${filter_}&laphar=DATA OPS KEPOLISIAN`)
  .then(data=>  {
    data = data.data
    if (data.length > 0) {
      for (let i = 0; i < data.length; i++) {
        $('#t_ops_kepolisian').append(`
          <tr style="background-color: ${generateRandomHexColor()}!important;">
            <td>#</td>
            <td>${data[i].kategori_kegiatan}</td>
            <td>${data[i].jumlah}</td>
          </tr>
        `)
        data[i].detail_kegiatan.forEach((v, no) => {
          $('#t_ops_kepolisian').append(`
          <tr>
            <td>${no+1}</td>
            <td>${v.jenis_kegiatan}</td>
            <td>${v.jumlah}</td>
          </tr>
        `)
        });
      }
    } else {
      $('#t_ops_kepolisian').append(`
          <tr>
            <td>TIDAK ADA OPS KEPOLISIAN</td>
          </tr>
        `)
    }
  })

  // getDataSmNew(`${global_url_api}smart_dashboard/smart_mobile_officer/laphar_opsdal?${filter_}&laphar=DATA PAM GIAT MASYARAKAT`)
  // .then(data=>  {
  //   data = data.data
  //   if (data.length > 0) {
  //     data.forEach((v,no) => {
  //       $('#t_giat_masyarakat').append(`
  //         <tr>
  //           <td>${no+1}</td>
  //           <td>${v.jenis_kegiatan}</td>
  //           <td>${v.jumlah_kegiatan}</td>
  //         </tr>
  //       `)
  //     });
  //   } else {
  //     $('#t_giat_masyarakat').append(`
  //         <tr>
  //           <td>TIDAK ADA PAM GIAT MASYARAKAT</td>
  //         </tr>
  //       `)
  //   }
  // })

  getDataSmNew(`${global_url_api}smart_dashboard/smart_mobile_officer/laphar_opsdal?${filter_}&laphar=DATA PENGAWALAN`)
  .then(data=>  {
    data = data.data
    if (data.length > 0) {
      data.forEach((v,no) => {
        $('#t_pengawalan').append(`
          <tr>
            <td>${no+1}</td>
            <td>${v.object_pengawalan}</td>
            <td>${v.jumlah_pengawalan}</td>
          </tr>
        `)
      });
    } else {
      $('#t_pengawalan').append(`
          <tr>
            <td>TIDAK ADA PENGAWALAN</td>
          </tr>
        `)
    }
  })
}