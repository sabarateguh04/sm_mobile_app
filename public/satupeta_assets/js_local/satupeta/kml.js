var kml_link = [];
var kml_arr = [];
var kmlLayer;

function fetchLocalKmlList(id) {
	const polda_id = $('#polda_id').val();
	const polres_id = $('#polres_id').val();

	$.ajax({
		type: 'GET',
		url: global_url_ci + 'PemetaanNew/get_kml',
		dataType: 'json',
		data: {
			kategori_kml_id: id,
			polda_id: polda_id,
			polres_id: polres_id,
		},
		success: function (rows) {
			if (rows && rows.length > 0) {
				kml_link = rows.map(function (e) {
					return {
						rowid: e.id || e.rowid,
						nama: e.nama,
						file: e.file,
					};
				});
				list_kml(kml_link);
			} else {
				$("#list_kml").append(`<li class="list-group-item list-group-item-action text-center">
          <span class="name_kml"> Tidak ada data KML </span></li>`);
			}
		},
		error: function () {
			$("#list_kml").append(`<li class="list-group-item list-group-item-action text-center">
          <span class="name_kml"> Tidak ada data KML </span></li>`);
		},
	});
}

function kml(id) {
	$("#list_kml").html("");

	if (!global_url_api) {
		fetchLocalKmlList(id);
		return;
	}

	let url = getEmbedUrl('kml_maps');
	getDataSmNew(url + `&kategori_kml_id=${id}`).then(r => {
		if (r.status == true) {
			kml_link = r.data;
			list_kml(r.data);
		} else {
			fetchLocalKmlList(id);
		}
	}).catch(function () {
		fetchLocalKmlList(id);
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
