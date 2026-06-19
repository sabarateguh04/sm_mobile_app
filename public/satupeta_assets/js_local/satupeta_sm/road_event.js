var list_arr_road_event = {}
var dt_arr_gangguan_lalin = {}

setTimeout(() => {
    prom_get_data_road_event(usr_polda_id != '' ? usr_polda_id : '', usr_polres_id != '' ? usr_polres_id : '').then(function (data) {
        list_road_event(data.data, data.total)
    })
}, 1000);

function prom_get_data_road_event(pd_id = "", ps_id = "") {
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

    filter += `&tanggal=${c_date}`

    // let a = getRToken().then(async r=>{
    //     // Default options are marked with *
    //     if (r.status != true) {
    //         window.location.href = `${global_url_ci}Auth/logout`
    //     }else {
    let data = {
        "data" : [],
        "total" : 0
    }
    return new Promise(function (resolve, reject) {
        $.ajax({
          type: "GET",
          url: `${global_url_api}sm_integration/road_event?${filter}`,
          dataType: "json",
          headers: {
            "token": "5b3dac76aaee24d14185cbc3d010fd20",
            // "second_bearer" : r.data.second_bearer
            "second_bearer" : `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1bml0X2Z1bmdzaW9uYWwiOm51bGwsImxhdCI6bnVsbCwibG5nIjpudWxsLCJwcm92X2lkIjpudWxsLCJwcm92aW5zaSI6bnVsbCwia290YV9pZCI6bnVsbCwia290YSI6bnVsbCwidmVyaWZ5IjpmYWxzZSwia29kZV9zdWJfdW5pdCI6bnVsbCwia29kZV91c2VyX21hc3RlciI6IjQyMDIwMDAxT1JHQU5JSyIsImtlc2F0dWFuIjpudWxsLCJwb2xkYV9pZCI6bnVsbCwicG9scmVzX2lkIjpudWxsLCJpZF91bmlxdWUiOiI0MjAyMDAwMSIsImtvZGVfamVuaXNfdXNlciI6Ik9SR0FOSUsiLCJrb2RlX21vZHVsIjoiIiwibW9kdWxfdXJsIjpudWxsLCJhcHBzX3VybCI6Ii9Icm0vcHJvZmlsZXVzZXIiLCJpY29uX2JvIjpudWxsLCJrb2RlX2xvZ2luIjoiYmU4Njc3MjIwNTkyN2RmMWFlYzY5M2UyODVmNWYwYWJhZjNmYmQ5YzMwYTgwMGIyNjdlZDRjZTI0YzlhYzQ2YiIsImlhdCI6MTcxMzQ5Nzk4NSwiZXhwIjozMzI0OTQ5Nzk4NX0.2Yt8by9Qzdi0_C_oKTjJB6CtTDE8Au77BoaOfH3YhAw`
            },
          success: function (r) {
            // closeToast()
            data = {
                "data" : r.data,
                "total" : r.total
            }
            resolve(data);
          },
          error: function (e) {
            if (e.status == 0) {
              swal(
                'Error !',
                'Koneksi server error',
                'error'
              );
            }else if(e.status == 400) {
              resolve(data)
            }else if(e.status == 404) {
              resolve(data)
            }
            // closeToast()
          }
          
        });
      });
//     }
// })
// return a
}

function list_road_event(data,total,pd_id='',ps_id='') {
    list_arr_road_event = {}
    $('#list_road_event').html('')
    $('#total_road_event').html('')
    $('#mtotal_road_event').html('')
    $('#mtotal_road_event').append(total)
    if (data.length != 0) {
        $('#total_road_event').append(total)
        data.forEach(e => {
            $('#list_road_event').append(`
            <div class="col-4 py-4 border">
              <h3 class="fw-bold">${e.jml}</h3>
              <p>${e.event_name}</p>
            </div>
            `);
        });
        
    }else {
        $('#list_road_event').append(`
        <div class="col-4 py-4 border">
              <h3 class="fw-bold">0</h3>
              <p>-</p>
        </div>
        <div class="col-4 py-4 border">
              <h3 class="fw-bold">0</h3>
              <p>-</p>
        </div>
        <div class="col-4 py-4 border">
              <h3 class="fw-bold">0</h3>
              <p>-</p>
        </div>
        `)
    }
    setTimeout(() => {
        prom_get_data_road_event(pd_id,ps_id).then(function (data) {
            list_road_event(data.data, data.total, pd_id, ps_id)
        })
    }, 60000);
}

function dt_road_event() {
    var filter = ''
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
  
      filter += `&tanggal=${c_date}`
    if ( ! $.fn.DataTable.isDataTable( '#dt-road-event' ) ) {
      $('#dt-road-event').DataTable({
        columns: [
            { "data": "nama_cctv" },
            { "data": "lat" },
            { "data": "lng" },
            { "data": "event_name" },
            { "data": "tanggal" },
            { "data": "waktu" }
        ],
        "ajax": {
          "url": './Peta/dt_road_event',
          "type": "POST",
          "data" : {'filter' : filter, 'bearer' : tber}
        },
      });
    }
  }