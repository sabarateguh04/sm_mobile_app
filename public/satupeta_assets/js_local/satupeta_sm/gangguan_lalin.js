var list_arr_gangguan_lalin = {}
var dt_arr_gangguan_lalin = {}

setTimeout(() => {
    prom_get_data_gangguan_lalin(usr_polda_id != '' ? usr_polda_id : '', usr_polres_id != '' ? usr_polres_id : '').then(function (data) {
        list_gangguan_lalin(data)
    })
}, 1000);

function prom_get_data_gangguan_lalin(pd_id = "", ps_id = "") {
    let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()
	let filter = ''

	if (polda_id != '') {
		filter += `&polda_id=${polda_id}`
	}else if(pd_id != '') {
		filter += `&polda_id=${pd_id}`
	}

	if (polres_id != '') {
		filter += `&polres_id=${polres_id}`
	}else if(ps_id != ''){
		filter += `&polres_id=${ps_id}`
	}

    var today = new Date();
	var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    var yyyy = today.getFullYear();
	let c_date = yyyy + '-' + mm + '-' + dd

    filter += `&tgl_start=${c_date}&tgl_end=${c_date}`
    

    // let a = getRToken().then(async r=>{
    //     // Default options are marked with *
    //     if (r.status != true) {
    //         window.location.href = `${global_url_ci}Auth/logout`
    //     }else {
    return new Promise(function (resolve, reject) {
        $.ajax({
          type: "GET",
          url: `${global_url_api}opsdal/emergency_masyarakat?${filter}`,
          dataType: "json",
          headers: {
            "token": "5b3dac76aaee24d14185cbc3d010fd20",
            // "second_bearer" : r.data.second_bearer
            "secondbearer" : `${global_sbearer}`
            },
          success: function (r) {
            // closeToast()
            resolve(r.data);
          },
          error: function (e) {
            if (e.status == 0) {
              swal(
                'Error !',
                'Koneksi server error',
                'error'
              );
            }else if(e.status == 400) {
              resolve([])
            }
            // closeToast()
          }
          
        });
      });
//     }
// })
// return a
}

function list_gangguan_lalin(data,pd_id='',ps_id='') {
    list_arr_gangguan_lalin = {}
    $('#list_gangguan_lalin').html('')
    $('#total_gangguan_lalin').html('')
    $('#mtotal_gangguan_lalin').html('')
    $('#total_gangguan_lalin').append(data.length)
    $('#mtotal_gangguan_lalin').append(data.length)
    if (data.length != 0) {
        data.forEach(item => {
            if (!list_arr_gangguan_lalin[item.sub_kategori]) {
              list_arr_gangguan_lalin[item.sub_kategori] = [];
            }
          
            list_arr_gangguan_lalin[item.sub_kategori].push(item);
        });
          
            const keys = Object.keys(list_arr_gangguan_lalin);
            keys.forEach((e,i) => {
                $('#list_gangguan_lalin').append(`
                    <div>
                        <div class="row bg-danger align-items-center text-center fw-bold text-white pt-2">
                        <h6>
                            ${keys[i]}
                        </h6>
                        </div>
                        <div class="row align-items-center text-center">
                        <div class="col-4 py-4 border">
                            <h3 class="fw-bold" id="bd_${keys[i].toLowerCase().replace(/ /g, "_")}"></h3>
                            <p>Belum Ditangani</p>
                        </div>
                        <div class="col-4 py-4 border">
                            <h3 class="fw-bold" id="dp_${keys[i].toLowerCase().replace(/ /g, "_")}"></h3>
                            <p>Dalam Penanganan</p>
                        </div>
                        <div class="col-4 py-4 border">
                            <h3 class="fw-bold" id="s_${keys[i].toLowerCase().replace(/ /g, "_")}"></h3>
                            <p>Selesai</p><br>
                        </div>
                        </div>
                    </div>
                `)
                list_arr_gangguan_lalin[e].forEach(tes => {
                    if (!list_arr_gangguan_lalin[e][tes.status]) {
                        list_arr_gangguan_lalin[e][tes.status] = [];
                      }
                    
                      list_arr_gangguan_lalin[e][tes.status].push(tes);
                });
                $(`#bd_${keys[i].toLowerCase().replace(/ /g, "_")}`).html('0')
                $(`#dp_${keys[i].toLowerCase().replace(/ /g, "_")}`).html('0')
                $(`#s_${keys[i].toLowerCase().replace(/ /g, "_")}`).html('0')
                
                if ('new' in list_arr_gangguan_lalin[e]) {
                    $(`#bd_${keys[i].toLowerCase().replace(/ /g, "_")}`).html(list_arr_gangguan_lalin[e].new.length)
                }
    
                if ('proses' in list_arr_gangguan_lalin[e]) {
                    $(`#dp_${keys[i].toLowerCase().replace(/ /g, "_")}`).html(list_arr_gangguan_lalin[e].proses.length)
                }
    
                if ('done' in list_arr_gangguan_lalin[e]) {
                    $(`#s_${keys[i].toLowerCase().replace(/ /g, "_")}`).html(list_arr_gangguan_lalin[e].done.length)
                }
                
            });
        
    }else {
        $('#list_gangguan_lalin').append(`
            <div>
                <div class="row bg-danger align-items-center text-center fw-bold text-white pt-2">
                <h6>
                    -
                </h6>
                </div>
                <div class="row align-items-center text-center">
                <div class="col-4 py-4 border">
                    <h3 class="fw-bold" id="bd_}">0</h3>
                    <p>Belum Ditangani</p>
                </div>
                <div class="col-4 py-4 border">
                    <h3 class="fw-bold" id="dp_}">0</h3>
                    <p>Dalam Penanganan</p>
                </div>
                <div class="col-4 py-4 border">
                    <h3 class="fw-bold" id="s_}">0</h3>
                    <p>Selesai</p><br>
                </div>
                </div>
            </div>
        `)
    }
    setTimeout(() => {
        prom_get_data_gangguan_lalin(pd_id,ps_id).then(function (data) {
            list_gangguan_lalin(data,pd_id,ps_id)
        })
    }, 3000);
}

function dt_gangguan_lalin() {
    var filter = ''
      let t_s = $('#f_date_from').val()
        let t_e = $('#f_date_to').val()
      let da = $('#polda_id').val()
      let res = $('#polres_id').val()
      if (da != '' && da != undefined) {
          filter += `&polda_id=${da}`
      }
      if (res != '' && res != undefined) {
          filter += `&polres_id=${res}`
      }
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
      var yyyy = today.getFullYear();
      let c_date = yyyy + '-' + mm + '-' + dd
  
      filter += `&tgl_start=${c_date}&tgl_end=${c_date}`
    if ( ! $.fn.DataTable.isDataTable( '#dt-gangguan-lalin' ) ) {
      $('#dt-gangguan-lalin').DataTable({
        columns: [
            { "data": "nama_jalan_s" },
            { "data": "lat" },
            { "data": "lng" },
            { "data": "sub_kategori" },
            { "data": "tanggal" },
            { "data": "penjelasan_singkat" },
            { "data": "status" },
            { "data": "situasi_lalin" },
            { "data": "ctdupd" }
        ],
        "ajax": {
          "url": './Peta/dt_gangguan_lalin',
          "type": "POST",
          "data" : {'filter' : filter, 'bearer' : tber}
        },
      });
    }
  }