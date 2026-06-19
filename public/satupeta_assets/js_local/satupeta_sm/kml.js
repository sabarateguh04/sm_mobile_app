var kml_link = [];
var kml_arr = [];
var kmlLayer;

function get_list_kml() {
	if ($('#cstm-content').hasClass('d-none')) {
		$('#cstm-content').removeClass('d-none')
		$('#cstm-content').append(`
		<div class="accordion">
			<div class="accordion-item">
				<h2 class="accordion-header" id="panelsStayOpen-headingOne">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
					PRAOPS
				</button>
				</h2>
				<div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
				<div class="accordion-body">
					<div class="ml-2 row mx-0" style="text-align:left!important;">
                      <div class="col-3">
                        <input type="checkbox" class="form-check-input" id="select_all_kml" onchange="check_kml()"><span class="me-1">All</span>
                      </div>
                        <div class="col">
                          <input type="text" class="form-control" placeholder="Cari" id="search-kml" onkeyup="search_kml()">
                        </div>
                    </div>
                    <ul class="list-group mt-2 text-start pt-2 border-top list_data list_kml" style="height:200px!important" id="list_kml">
                    </ul>
				</div>
				</div>
			</div>
		</div>
		`)
		kml()
		//select all kml
		$("#select_all_kml").change(function(){
			var status = this.checked;
			$('.check-kml').each(function(){
			  this.checked = status;
			});
		  });
		
		  $('.check-kml').change(function(){ 
			if(this.checked == false){
			  $("#select_all_kml")[0].checked = false;
			}
			
			if ($('.check-kml:checked').length == $('.check-kml').length ){ 
			  $("#select_all_kml")[0].checked = true;
			}
		  });
	}else{
		$('#cstm-content').html('')
		$('#cstm-content').addClass('d-none')
	}
}

function kml(id) {
	// prom_get_data('./PemetaanNew/get_kml?kategori_kml_id='+id).then(function (data) {
    //     kml_link = data
	// 	list_kml(data);
	// });
	$("#list_kml").html("");
	let url = getEmbedUrl('kml_maps')
	getDataSmNew(url).then(r => {
	//   showToast()
	  if (r.status == true) {
		kml_link = r.data
		list_kml(r.data);
		// closeToast()
	  }else {
		$("#list_kml").append(`<li class="list-group-item list-group-item-action text-center">
          <span class="name_kml"> Tidak ada data KML </span></li>`);
		// closeToast()
	  }
	});
}

function list_kml(data) {
	$("#list_kml").html("");
	setTimeout(() => {
		var no = 0;
		kml_link.forEach((e) => {
			$("#list_kml").append(`<li class="list-group-item list-group-item-action">
          <input type="checkbox" class="me-2 check-kml" onchange="check_kml()" name="kml[]" value="${e.rowid}">
          <span class="name_kml">${e.nama} </span></li>`);
		});
	}, 300);
}

function check_kml() {
	prom_check_kml().then(function (hasil) {
        // console.log(hasil);
		prom_clearKml().then(function (data) {
		// 	// cctv = hasil;
			create_kml(hasil);
		});
	});
}

function prom_check_kml() {
	return new Promise(function (resolve, reject) {
		var check_kml = [];
		setTimeout(() => {
			$('input[name="kml[]"]:checked').each(function () {
                const arr = kml_link.find(o => o.rowid === parseInt(this.value))
                check_kml.push(arr)
			});
			resolve(check_kml);
		}, 300);
	});
}

function create_kml(data) {
    return new Promise(function (resolve, reject) {
        kml_arr = [];
        for (let i = 0; i < data.length; i++) {
            kmlLayer = new google.maps.KmlLayer({
                url : data[i].file,
                map: map
              });
            kml_arr.push(kmlLayer)
        }
        resolve(kml_arr)
    })
}

function prom_clearKml() {
	return new Promise(function (resolve, reject) {
		if (kml_arr) {
				kml_arr.forEach((v,i) => {
					
				kml_arr[i].setMap(null);
			});
			
			resolve(kml_arr);
		}
	});
}

function search_kml() {
	let f_kml = $('#search-kml').val().toLowerCase()
	let i_kml = $('.list_kml>.list-group-item')
	for(var i = 0; i<i_kml.length; i++){
		let span = $('.list-group-item>span')[i];
		if(span.innerHTML.toLowerCase().indexOf(f_kml) > -1){
			i_kml[i].style.display = "";
		}else{
			i_kml[i].style.display = "none";
		}
	}
}