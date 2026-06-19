var map; //variable map
let bs = []; //variable marker black_spot
let ts = []; //variable marker trouble_spot
let ag = []; //variable marker ambang_gangguan
let gm = []; //variable marker giat_masyarakat
var titik_arr = [];
var titik_link = [];
var label = ''

var user_lat = parseFloat($('#user_lat').val());
var user_lng = parseFloat($('#user_lng').val());

const styles = {
  default: [],
  silver: [
    {
      elementType: "geometry",
      stylers: [{ color: "#f5f5f5" }],
    },
    {
      elementType: "labels.icon",
      stylers: [{ visibility: "off" }],
    },
    {
      elementType: "labels.text.fill",
      stylers: [{ color: "#616161" }],
    },
    {
      elementType: "labels.text.stroke",
      stylers: [{ color: "#f5f5f5" }],
    },
    {
      featureType: "administrative.land_parcel",
      elementType: "labels.text.fill",
      stylers: [{ color: "#bdbdbd" }],
    },
    {
      featureType: "poi",
      elementType: "geometry",
      stylers: [{ color: "#eeeeee" }],
    },
    {
      featureType: "poi",
      elementType: "labels.text.fill",
      stylers: [{ color: "#757575" }],
    },
    {
      featureType: "poi.park",
      elementType: "geometry",
      stylers: [{ color: "#e5e5e5" }],
    },
    {
      featureType: "poi.park",
      elementType: "labels.text.fill",
      stylers: [{ color: "#9e9e9e" }],
    },
    {
      featureType: "road",
      elementType: "geometry",
      stylers: [{ color: "#ffffff" }],
    },
    {
      featureType: "road.arterial",
      elementType: "labels.text.fill",
      stylers: [{ color: "#757575" }],
    },
    {
      featureType: "road.highway",
      elementType: "geometry",
      stylers: [{ color: "#dadada" }],
    },
    {
      featureType: "road.highway",
      elementType: "labels.text.fill",
      stylers: [{ color: "#616161" }],
    },
    {
      featureType: "road.local",
      elementType: "labels.text.fill",
      stylers: [{ color: "#9e9e9e" }],
    },
    {
      featureType: "transit.line",
      elementType: "geometry",
      stylers: [{ color: "#e5e5e5" }],
    },
    {
      featureType: "transit.station",
      elementType: "geometry",
      stylers: [{ color: "#eeeeee" }],
    },
    {
      featureType: "water",
      elementType: "geometry",
      stylers: [{ color: "#c9c9c9" }],
    },
    {
      featureType: "water",
      elementType: "labels.text.fill",
      stylers: [{ color: "#9e9e9e" }],
    },
  ],
  night: [
    { elementType: "geometry", stylers: [{ color: "#242f3e" }] },
    { elementType: "labels.text.stroke", stylers: [{ color: "#242f3e" }] },
    { elementType: "labels.text.fill", stylers: [{ color: "#746855" }] },
    {
      featureType: "administrative.locality",
      elementType: "labels.text.fill",
      stylers: [{ color: "#d59563" }],
    },
    {
      featureType: "poi",
      elementType: "labels.text.fill",
      stylers: [{ color: "#d59563" }],
    },
    {
      featureType: "poi.park",
      elementType: "geometry",
      stylers: [{ color: "#263c3f" }],
    },
    {
      featureType: "poi.park",
      elementType: "labels.text.fill",
      stylers: [{ color: "#6b9a76" }],
    },
    {
      featureType: "road",
      elementType: "geometry",
      stylers: [{ color: "#38414e" }],
    },
    {
      featureType: "road",
      elementType: "geometry.stroke",
      stylers: [{ color: "#212a37" }],
    },
    {
      featureType: "road",
      elementType: "labels.text.fill",
      stylers: [{ color: "#9ca5b3" }],
    },
    {
      featureType: "road.highway",
      elementType: "geometry",
      stylers: [{ color: "#746855" }],
    },
    {
      featureType: "road.highway",
      elementType: "geometry.stroke",
      stylers: [{ color: "#1f2835" }],
    },
    {
      featureType: "road.highway",
      elementType: "labels.text.fill",
      stylers: [{ color: "#f3d19c" }],
    },
    {
      featureType: "transit",
      elementType: "geometry",
      stylers: [{ color: "#2f3948" }],
    },
    {
      featureType: "transit.station",
      elementType: "labels.text.fill",
      stylers: [{ color: "#d59563" }],
    },
    {
      featureType: "water",
      elementType: "geometry",
      stylers: [{ color: "#17263c" }],
    },
    {
      featureType: "water",
      elementType: "labels.text.fill",
      stylers: [{ color: "#515c6d" }],
    },
    {
      featureType: "water",
      elementType: "labels.text.stroke",
      stylers: [{ color: "#17263c" }],
    },
  ],
  retro: [
    { elementType: "geometry", stylers: [{ color: "#ebe3cd" }] },
    { elementType: "labels.text.fill", stylers: [{ color: "#523735" }] },
    { elementType: "labels.text.stroke", stylers: [{ color: "#f5f1e6" }] },
    {
      featureType: "administrative",
      elementType: "geometry.stroke",
      stylers: [{ color: "#c9b2a6" }],
    },
    {
      featureType: "administrative.land_parcel",
      elementType: "geometry.stroke",
      stylers: [{ color: "#dcd2be" }],
    },
    {
      featureType: "administrative.land_parcel",
      elementType: "labels.text.fill",
      stylers: [{ color: "#ae9e90" }],
    },
    {
      featureType: "landscape.natural",
      elementType: "geometry",
      stylers: [{ color: "#dfd2ae" }],
    },
    {
      featureType: "poi",
      elementType: "geometry",
      stylers: [{ color: "#dfd2ae" }],
    },
    {
      featureType: "poi",
      elementType: "labels.text.fill",
      stylers: [{ color: "#93817c" }],
    },
    {
      featureType: "poi.park",
      elementType: "geometry.fill",
      stylers: [{ color: "#a5b076" }],
    },
    {
      featureType: "poi.park",
      elementType: "labels.text.fill",
      stylers: [{ color: "#447530" }],
    },
    {
      featureType: "road",
      elementType: "geometry",
      stylers: [{ color: "#f5f1e6" }],
    },
    {
      featureType: "road.arterial",
      elementType: "geometry",
      stylers: [{ color: "#fdfcf8" }],
    },
    {
      featureType: "road.highway",
      elementType: "geometry",
      stylers: [{ color: "#f8c967" }],
    },
    {
      featureType: "road.highway",
      elementType: "geometry.stroke",
      stylers: [{ color: "#e9bc62" }],
    },
    {
      featureType: "road.highway.controlled_access",
      elementType: "geometry",
      stylers: [{ color: "#e98d58" }],
    },
    {
      featureType: "road.highway.controlled_access",
      elementType: "geometry.stroke",
      stylers: [{ color: "#db8555" }],
    },
    {
      featureType: "road.local",
      elementType: "labels.text.fill",
      stylers: [{ color: "#806b63" }],
    },
    {
      featureType: "transit.line",
      elementType: "geometry",
      stylers: [{ color: "#dfd2ae" }],
    },
    {
      featureType: "transit.line",
      elementType: "labels.text.fill",
      stylers: [{ color: "#8f7d77" }],
    },
    {
      featureType: "transit.line",
      elementType: "labels.text.stroke",
      stylers: [{ color: "#ebe3cd" }],
    },
    {
      featureType: "transit.station",
      elementType: "geometry",
      stylers: [{ color: "#dfd2ae" }],
    },
    {
      featureType: "water",
      elementType: "geometry.fill",
      stylers: [{ color: "#b9d3c2" }],
    },
    {
      featureType: "water",
      elementType: "labels.text.fill",
      stylers: [{ color: "#92998d" }],
    },
  ],
  hiding: [
    {
      featureType: "poi",
      stylers: [{ visibility: "off" }],
    },
    {
      featureType: "transit",
      elementType: "labels.icon",
      stylers: [{ visibility: "off" }],
    },
  ],
};

function initMap() {
  map = new google.maps.Map(document.getElementById("mapid"), {
    center: { lat: user_lat , lng: user_lng },
    zoom: 10,
    // disableDefaultUI: true,
    zoomControl: true,
    mapTypeControl: false,
    scaleControl: false,
    streetViewControl: false,
    rotateControl: false,
    fullscreenControl: false,
    styles : styles.night,
    // mapTypeId: 'terrain'
  });
}

function change_basemap() {
  const selectStyle = $('#basemap').val()
  map.setOptions({ styles: styles[selectStyle] });
  $('#hidding_map').prop('checked', false);
}

function hidding_map_features() {
  let check = $('input[name="hidding_map"]:checked')
  const selectbase = $('#basemap').val()
  if (check.length == 0) {
    map.setOptions({ styles: styles[selectbase] });
  }else {
    const arn = map.styles.filter(function (x) {
      return x.featureType != 'poi' &&
             x.featureType != 'poi.park' &&
             x.featureType != 'transit' &&
             x.featureType != 'transit.station' &&
             x.featureType != 'transit.line'
    });

    const arm = [...arn, ...styles.hiding]
    map.setOptions({ styles: arm });
  }
}

function show_titik(item="",item2 = "") {
  setTimeout(() => {
    if (item == "vvip") {
			if ($("#cvvip").hasClass("check")) {
				$('#vip').addClass('d-none')
				$("#cvvip").removeClass("check");
			} else {
				$('#vip').removeClass('d-none')
				$("#cvvip").addClass("check");
			}
		}

    if (item == "cctv" &&(item2 == "traffic_counting" ||item2 == "traffic_category" ||item2 == "average_speed" || item2 == "length_ocupantion")) {
      label = item2;
      let dinas
  
      if (item2 == "traffic_counting") icon = "./my/satupeta/trafficcounting.png";
      if (item2 == "traffic_category") icon = "./my/satupeta/trafficcategory.png";
      if (item2 == "average_speed") icon = "./my/satupeta/avgspeed.png";
      if (item2 == "length_ocupantion") icon = "./my/satupeta/lengthocc.png";

      let rs = cctv_arr.filter(e => {
        return e.dinas
      })

      if (item == "cctv" && item2 == "traffic_counting") {
        if (cctv_arr.length != 0) {
          dinas = rs[0].dinas
          if ($("#ctco").hasClass("check")) {
            if (dinas == 'korlantas') {
              icon = "./my/satupeta/cctv_icon_korlantas.png";
            }
            if (dinas == 'dishub') {
              icon = "./my/satupeta/cctv_icon_dishub.png";
            }
            item2 = ''
            $("#ctco").removeClass("check");
          } else {
            $("#ctco").addClass("check");
          }
        }else {
          swal(
            'Warning !',
            'Pilih CCTV terlebih dahulu !',
            'warning'
          );
        }
      }
      if (item == "cctv" && item2 == "traffic_category") {
        if (cctv_arr.length != 0) {
          dinas = rs[0].dinas
          if ($("#ctca").hasClass("check")) {
            if (dinas == 'korlantas') {
              icon = "./my/satupeta/cctv_icon_korlantas.png";
            }
            if (dinas == 'dishub') {
              icon = "./my/satupeta/cctv_icon_dishub.png";
            }
            item2 = ''
            $("#ctca").removeClass("check");
          } else {
            $("#ctca").addClass("check");
          }
        }else {
          swal(
            'Warning !',
            'Pilih CCTV terlebih dahulu !',
            'warning'
          );
        }
      }
      if (item == "cctv" && item2 == "average_speed") {
        if (cctv_arr.length != 0) {
          dinas = rs[0].dinas
          if ($("#cas").hasClass("check")) {
            if (dinas == 'korlantas') {
              icon = "./my/satupeta/cctv_icon_korlantas.png";
            }
            if (dinas == 'dishub') {
              icon = "./my/satupeta/cctv_icon_dishub.png";
            }
            item2 = ''
            $("#cas").removeClass("check");
          } else {
            $("#cas").addClass("check");
          }
        }else {
          swal(
            'Warning !',
            'Pilih CCTV terlebih dahulu !',
            'warning'
          );
        }
      }
  
      prom_check_cctv().then(function (hasil) {
        prom_clearMarkerCctv().then(function (data) {
          let cctv = cctv_link;
          if (hasil) {
            cctv = hasil;
          }
          create_cctv_map(cctv, item2, icon, dinas);
        });
      });
    }
  
    if (item == "black_spot") {
      varr = bs;
      if ($("#cbs").hasClass("check")) {
        clearMarkerTitik(item);
        $("#cbs").removeClass("check");
        $(`#jml_data_item_${item}`).remove()
      } else {
        icon = "./my/satupeta/blackspot2.png";
        get_titik("Black Spot").then(r => {
          create_titik_map(item, icon, r.data).then(e => {
            $('#jml_data_group').append(`
              <div class="jml_data mt-2" id="jml_data_item_${item}">
                <span id="jml_data">Jumlah Black Spot <img src="./my/satupeta/blackspot2.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
              </div>
            `)
            const obj = document.getElementById(`counter_${item}`);
            animateValue(obj, 0, r.total, 200)
          });
          $("#cbs").addClass("check");
        });
      }
    }
    if (item == "trouble_spot") {
      varr = ts;
      if ($("#cts").hasClass("check")) {
        clearMarkerTitik(item);
        $("#cts").removeClass("check");
        $(`#jml_data_item_${item}`).remove()
      } else {
        icon = "./my/satupeta/troublespot2.png";
        get_titik("Trouble Spot").then(r => {
          create_titik_map(item, icon, r.data).then(e => {
            $('#jml_data_group').append(`
              <div class="jml_data mt-2" id="jml_data_item_${item}">
                <span id="jml_data">Jumlah Trouble Spot <img src="./my/satupeta/troublespot2.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
              </div>
            `)
            const obj = document.getElementById(`counter_${item}`);
            animateValue(obj, 0, r.total, 200)
          });
          $("#cts").addClass("check");
        });
      }
    }
    if (item == "ambang_gangguan") {
      varr = ag;
      if ($("#cag").hasClass("check")) {
        clearMarkerTitik(item);
        $("#cag").removeClass("check");
        $(`#jml_data_item_${item}`).remove()
      } else {
        icon = "./my/satupeta/ambanggangguan.png";
        get_titik("Ambang Gangguan").then(r => {
          create_titik_map(item, icon, r.data).then(e => {
            $('#jml_data_group').append(`
              <div class="jml_data mt-2" id="jml_data_item_${item}">
                <span id="jml_data">Jumlah Ambang Gangguan <img src="./my/satupeta/ambanggangguan.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
              </div>
            `)
            const obj = document.getElementById(`counter_${item}`);
            animateValue(obj, 0, r.total, 200)
          });
          $("#cag").addClass("check");
        });
      }
    }
  
    if (item == "giat_masyarakat") {
      varr = gm;
      if ($("#cgm").hasClass("check")) {
        clearMarkerTitik(item);
        $("#cgm").removeClass("check");
        $(`#jml_data_item_${item}`).remove()
      } else {
        icon = "./my/satupeta/giatmasyarakat.png";
        get_titik("Kegiatan Masyarakat").then(r => {
          create_titik_map(item, icon, r.data).then(e => {
            $('#jml_data_group').append(`
              <div class="jml_data mt-2" id="jml_data_item_${item}">
                <span id="jml_data">Jumlah Kegiatan Masyarakat <img src="./my/satupeta/giatmasyarakat.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
              </div>
            `)
            const obj = document.getElementById(`counter_${item}`);
            animateValue(obj, 0, r.total, 200)
          });
          $("#cgm").addClass("check");
        });
      }
    }
    if (item == "rawan") {
      if ($("#cr").hasClass("check")) {
        $('#rawan').addClass('d-none')
        $("#cr").removeClass("check");
      } else {
        $('#rawan').removeClass('d-none')
        $("#cr").addClass("check");
      }
    }
  
    if (item == "rengiat") {
      if ($("#rg").hasClass("check")) {
        $('#rengiat').addClass('d-none')
        $("#rg").removeClass("check");
      } else {
        $('#rengiat').removeClass('d-none')
        $("#rg").addClass("check");
      }
    }
  }, 150);

}

function create_titik_map(item, img, arr) {
  return new Promise(function (resolve, reject) {
		titik_arr = [];
		let m_titik = [];
	
		var icon = {
      url: img, // url
      // url : "my/satupeta/blackspot2.png",
      scaledSize: new google.maps.Size(30, 30), // scaled size
    };

    
    for (let i = 0; i < titik_link.data.length; i++) {
      // window.setTimeout(() => {
        
        m_titik.push(
          new google.maps.Marker({
            position: { lat : parseFloat(titik_link.data[i].lat), lng : parseFloat(titik_link.data[i].lng)},
            map,
            icon : icon,
            animation: google.maps.Animation.DROP,
            polda_id : titik_link.data[i].polda_id,
            polres_id : titik_link.data[i].polres_id
          })
        );
      // }, i * 200);
    }

    if (item == "black_spot") {
      bs.push(m_titik);
      setInfoWindows(arr, bs[0], "Black Spot")
    }

    if (item == "trouble_spot") {
      ts.push(m_titik);
      setInfoWindows(arr, ts[0], "Trouble Spot")
    }

    if (item == "ambang_gangguan") {
      ag.push(m_titik);
      setInfoWindows(arr, ag[0], "Ambang Gangguan")
    }

    if (item == "giat_masyarakat") {
      gm.push(m_titik);
      setInfoWindows(arr, gm[0], "Giat Masyarakat")
    }

		resolve(titik_link.data);
	})
}

function clearMarkerTitik(arr) {
	if (arr == "black_spot") {
		for (let i = 0; i < bs[0].length; i++) {
			bs[0][i].setMap(null);
		}

    bs = [];
	}
	if (arr == "trouble_spot") {
		for (let i = 0; i < ts[0].length; i++) {
			ts[0][i].setMap(null);
		}

    ts = [];
	}
	if (arr == "ambang_gangguan") {
		for (let i = 0; i < ag[0].length; i++) {
			ag[0][i].setMap(null);
		}

    ag = [];
	}
	if (arr == "giat_masyarakat") {
		for (let i = 0; i < gm[0].length; i++) {
			gm[0][i].setMap(null);
		}

    gm = [];
	}

}

function get_titik(s = "") {
	let f_date_from = $('#f_date_from').val()
	let f_date_to = $('#f_date_to').val()
  let polda_id = $('#polda_id').val()
  let polres_id = $('#polres_id').val()
	return new Promise(function (resolve, reject) {
		$.ajax({
			type: "POST",
			url: "./Pemetaan/get_titik?s=" + s,
			dataType: "json",
			data: {from : f_date_from, to : f_date_to, polda_id : polda_id, polres_id : polres_id},
			success: function (r) {
				titik_link = r;
				$("#n_titik").text(s);
				resolve(titik_link);
			},
		});
	});
	
}

function setInfoWindows(arr,titik,n_titik) {
  const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
  infowindow.close(); // Close previously opened infowindow
  if (n_titik=="Black Spot") {
    for (let i = 0; i < arr.length; i++) {
      google.maps.event.addListener(titik[i], 'click', function(){
            map.setZoom(15);
            map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
          infowindow.setContent(`
          <div><p><b>${n_titik}</b></p>
          <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
          <table class="w-100">
          <tr>
          <td><b>Nama Jalan</b></td>
          <td>:</td>
          <td>${arr[i].namajalan}</td>
          </tr>
          <tr>
          <td><b>Kordinat</b></td>
          <td>:</td>
          <td>${arr[i].lat + "," + arr[i].lng}</td>
          </tr>
          <tr>
          <td><b>Tanggal</b></td>
          <td>:</td>
          <td>${arr[i].dtm}</td>
          </tr>
          <tr>
          <td><b>Status</b></td>
          <td>:</td>
          <td>${arr[i].status}</td>
          </tr>
          </table>
          </div>
          `);
          infowindow.open(map, titik[i]);
      });
    }
  }
  if (n_titik=="Trouble Spot") {
    for (let i = 0; i < arr.length; i++) {
      google.maps.event.addListener(titik[i], 'click', function(){
            map.setZoom(15);
            map.setCenter({ lat : arr[i].lat, lng : arr[i].lng});
          infowindow.setContent(`
          <div><p><b>${n_titik}</b></p>
          <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
          <table class="w-100">
          <tr>
          <td><b>Nama Jalan</b></td>
          <td>:</td>
          <td>${arr[i].namajalan}</td>
          </tr>
          <tr>
          <td><b>Kordinat</b></td>
          <td>:</td>
          <td>${arr[i].lat + "," + arr[i].lng}</td>
          </tr>
          <tr>
          <td><b>Tanggal</b></td>
          <td>:</td>
          <td>${arr[i].dtm}</td>
          </tr>
          <tr>
          <td><b>Status</b></td>
          <td>:</td>
          <td>${arr[i].status}</td>
          </tr>
          </table>
          </div>
          `);
          infowindow.open(map, titik[i]);
      });
    }
  }
  if (n_titik=="Ambang Gangguan") {
    for (let i = 0; i < arr.length; i++) {
      google.maps.event.addListener(titik[i], 'click', function(){
            map.setZoom(15);
            map.setCenter({ lat : arr[i].lat, lng : arr[i].lng});
          infowindow.setContent(`
          <div><p><b>${n_titik}</b></p>
          <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
          <table class="w-100">
          <tr>
          <td><b>Nama Jalan</b></td>
          <td>:</td>
          <td>${arr[i].namajalan}</td>
          </tr>
          <tr>
          <td><b>Kordinat</b></td>
          <td>:</td>
          <td>${arr[i].lat + "," + arr[i].lng}</td>
          </tr>
          <tr>
          <td><b>Tanggal</b></td>
          <td>:</td>
          <td>${arr[i].dtm}</td>
          </tr>
          <tr>
          <td><b>Status</b></td>
          <td>:</td>
          <td>${arr[i].status}</td>
          </tr>
          <tr>
          <td><b>Penyebab</b></td>
          <td>:</td>
          <td>${arr[i].penyebab}</td>
          </tr>
          <tr>
          <td><b>Detail Penyebab</b></td>
          <td>:</td>
          <td>${arr[i].penyebabd}</td>
          </tr>
          </table>
          </div>
          `);
          infowindow.open(map, titik[i]);
      });
    }
  }
  if (n_titik=="Giat Masyarakat") {
    for (let i = 0; i < arr.length; i++) {
      google.maps.event.addListener(titik[i], 'click', function(){
            map.setZoom(15);
            map.setCenter({ lat : arr[i].lat, lng : arr[i].lng});
          infowindow.setContent(`
          <div><p><b>${n_titik}</b></p>
          <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
          <table class="w-100">
          <tr>
          <td><b>Nama Jalan</b></td>
          <td>:</td>
          <td>${titik_link.data[i].namajalan}</td>
          </tr>
          <tr>
          <td><b>Kordinat</b></td>
          <td>:</td>
          <td>${titik_link.data[i].lat + "," + titik_link.data[i].lng}</td>
          </tr>
          <tr>
          <td><b>Tanggal</b></td>
          <td>:</td>
          <td>${titik_link.data[i].dtm}</td>
          </tr>
          <tr>
          <td><b>Giat</b></td>
          <td>:</td>
          <td>${titik_link.data[i].giat}</td>
          </tr>
          <tr>
          <td><b>Jam Dari</b></td>
          <td>:</td>
          <td>${titik_link.data[i].jamdari}</td>
          </tr>
          <tr>
          <td><b>Jam Sampai</b></td>
          <td>:</td>
          <td>${titik_link.data[i].jamsampai}</td>
          </tr>
          <tr>
          <td><b>Tanggal Dari</b></td>
          <td>:</td>
          <td>${titik_link.data[i].tgldari}</td>
          </tr>
          <tr>
          <td><b>Tanggal Sampai</b></td>
          <td>:</td>
          <td>${titik_link.data[i].tglsampai}</td>
          </tr>
          </table>
          </div>
          `);
          infowindow.open(map, titik[i]);
      });
    }
  }
}

// window.initMap = initMap; 

function fltr() {
	if ($(".content-filter").hasClass("d-none")) {
		$(".content-filter").removeClass("d-none");
	} else {
		$(".content-filter").addClass("d-none");
	}
}

function get_kategori_lokasi() {
  let polda_id = $('#polda_id').val()
  let polres_id = $('#polres_id').val()
  $('#v-kategori-kml').html('');
  $('#v-kategori-kml').append(`
  <a class="nav-link list d-flex align-items-center border-bottom border-end" href="javascript:void(0);" id="v-layers-tab" data-bs-toggle="pill" data-bs-target="#v-layers" role="tab" aria-controls="v-layers" aria-selected="false">
    <div class="wrapper">
      <p class="mb-0">
      Layer Setting</p>
    </div>
  </a>
  <a class="nav-link list d-flex align-items-center border-bottom border-end" href="javascript:void(0);" id="v-poly-tab" data-bs-toggle="pill" data-bs-target="#v-poly" role="tab" aria-controls="v-poly" aria-selected="false">
    <div class="wrapper">
      <p class="mb-0">
      Batas Wilayah</p>
    </div>
  </a> `)
		$.ajax({
			type: "POST",
			url: './Pemetaan/get_kategori_kml',
			dataType: "json",
			data: {polda_id : polda_id, polres_id : polres_id},
			success: function (r) {
        r.forEach(e => {
          $('#v-kategori-kml').append(`
          <a class="nav-link list d-flex align-items-center border-bottom border-end" id="v-kml-tab" onclick="kml(${e.id})" data-bs-toggle="pill" data-bs-target="#v-kml" href="javascript:void(0);" role="tab" aria-controls="v-kml" aria-selected="false">
            <div class="wrapper ml-3">
              <p class="mb-0">
              ${e.value}</p>
            </div>
          </a> 
          `)
        });
			},
		});
}


var trafficLayer;

function layers() {
  getSelectedLayer().then((e) => {
    if (e == 'traffic') {
      trafficLayer = new google.maps.TrafficLayer();
      trafficLayer.setMap(map);
    }

  })
}

$('#clearLayer').on('click', (e) => {
  const radioButtons = document.querySelectorAll('input[name="layer"]');
      let selectedValue;
      for (const radioButton of radioButtons) {
          if (radioButton.checked) {
              selectedValue = radioButton.value;
              break;
          }
      }

      if (selectedValue == 'traffic') {
        trafficLayer.setMap(null);
      }
      
      for(var i=0;i<radioButtons.length;i++)
      radioButtons[i].checked = false
})

function getSelectedLayer() {
  return new Promise( function (resolve, reject) {
    const radioButtons = document.querySelectorAll('input[name="layer"]');
      let selectedValue;
      for (const radioButton of radioButtons) {
          if (radioButton.checked) {
              selectedValue = radioButton.value;
              break;
          }
      }

      resolve(selectedValue)
  })
}

function check_traffic_layer() {
  let check = $('input[name="traffic_layer"]:checked')
  if (check.length == 0) {
    trafficLayer.setMap(null);
  }else {
    check.each(function () {
      trafficLayer = new google.maps.TrafficLayer();
      trafficLayer.setMap(map);
    });
  }
}

function select_k4(params) {
  $('#title_k4').html(capitalizeFirstLetter(params))
  $('#select_k4').html(capitalizeFirstLetter(params))
}