let map; //variable map
let bs = []; //variable marker black_spot
let ts = []; //variable marker trouble_spot
let ag = []; //variable marker ambang_gangguan
let gm = []; //variable marker giat_masyarakat
let rg = [];
let ro = [];
let fasum = [];
let ov = [];
let pko = [];
var titik_arr = [];
var titik_link = [];
var label = ''

let geotom

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

function transformDeeplyNestedCoordinates(dataArray) {
  const transformedData = [];

  // Fungsi rekursif untuk menemukan dan memproses pasangan koordinat
  function processArray(arr, currentPath) {
    if (!Array.isArray(arr)) {
      return; // Abaikan jika bukan array
    }

    // Jika ini adalah array pasangan koordinat [[lng, lat], [lng, lat], ...]
    // Kita cek apakah elemen pertama adalah array dengan 2 angka
    if (arr.length > 0 && Array.isArray(arr[0]) && arr[0].length === 2 &&
        typeof arr[0][0] === 'number' && typeof arr[0][1] === 'number') {
      const newSubArray = [];
      for (const coords of arr) {
        if (Array.isArray(coords) && coords.length === 2 &&
            typeof coords[0] === 'number' && typeof coords[1] === 'number') {
          newSubArray.push({
            lat: coords[1], // Latitude adalah elemen kedua (indeks 1)
            lng: coords[0]  // Longitude adalah elemen pertama (indeks 0)
          });
        }
      }
      if (newSubArray.length > 0) {
        transformedData.push(newSubArray);
      }
    } else {
      // Jika bukan array koordinat, telusuri lebih dalam
      for (const item of arr) {
        processArray(item, currentPath + ' -> Array');
      }
    }
  }

  processArray(dataArray, 'Root');
  return transformedData;
}

// const hasilNestedPoly = transformDeeplyNestedCoordinates(polyyy);


async function initMap() {
  let zoom 
  const { Map } = await google.maps.importLibrary("maps");
  const bounds = JSON.parse(global_bounds)
  if (user_lat == -1.3292304 && user_lng ==117.6913772 ) {
    zoom = 5
  }else {
    zoom = parseInt(global_zoom_map)
  }
  map = new Map(document.getElementById("mapid"), {
    center: { lat: user_lat , lng: user_lng },
    zoom: zoom,
    // disableDefaultUI: true,
    zoomControl: false,
    mapTypeControl: false,
    scaleControl: false,
    streetViewControl: false,
    rotateControl: false,
    fullscreenControl: false,
    styles : styles.default,
    // mapTypeId: 'terrain',
    mapId: "SM_MAP",
    gestureHandling: "greedy",
    minZoom: parseInt(global_minzoom), // Batasi zoom out
    // maxZoom: 18, // Batasi zoom in
    restriction: {
        latLngBounds: bounds,
    },
  })
  // Tunggu sampai map selesai render
  google.maps.event.addListenerOnce(map, "idle", () => {
    const bounds = map.getBounds();
    const ne = bounds.getNorthEast();
    const sw = bounds.getSouthWest();

    const BOUNDS = {
      south: sw.lat(),
      west: sw.lng(),
      north: ne.lat(),
      east: ne.lng()
    };

    console.log("BOUNDS=", JSON.stringify(BOUNDS));
  });
    // const arn = map.styles.filter(function (x) {
    //   return x.featureType != 'poi' &&
    //          x.featureType != 'poi.park' &&
    //          x.featureType != 'transit' &&
    //          x.featureType != 'transit.station' &&
    //          x.featureType != 'transit.line'
    // });
    

    const outerBounds = [
        { lat: 6.0, lng: 94.0 },   // Titik barat laut (atas kiri)
        { lat: -11.0, lng: 94.0 }, // Titik barat daya (bawah kiri)
        { lat: -11.0, lng: 141.0 },// Titik tenggara (bawah kanan)
        { lat: 6.0, lng: 141.0 },  // Titik timur laut (atas kanan)
    ];

    let polygonss = [outerBounds]

    // for (let i = 0; i < hasilNestedPoly.length; i++) {
    //   polygonss.push(hasilNestedPoly[i])
      
    // }

    // const polygon = new google.maps.Polygon({
    //   paths: polygonss,
    //   strokeOpacity: 0,
    //   fillColor: "#ffffffff",
    //   fillOpacity: 0.8,
    //   draggable: false,
    //   map: map,
    // });


    function drawDashedBorders(paths, color = "#ff4848") {
      paths.forEach((boundary) => {
        new google.maps.Polyline({
          path: boundary,
          strokeOpacity: 0,
          strokeColor: color,
          strokeWeight: 2,
          icons: [{
            icon: {
              path: 'M 0,-1 0,1', 
              strokeOpacity: 1,
              scale: 1.8,
            },
            offset: '0',
            repeat: '10px'
          }],
          map: map,
        });
      });
    }
    
    // drawDashedBorders(polygonss);
    
    // Create a custom zoom control container
    var customZoomControl = document.createElement("div");
    customZoomControl.classList.add("custom-zoom-control");
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(
      customZoomControl
    );
  
    // Add default zoom in and zoom out buttons to the custom container
    var zoomInButton = document.createElement("button");
    zoomInButton.textContent = "+";
    customZoomControl.appendChild(zoomInButton);
  
    var zoomOutButton = document.createElement("button");
    zoomOutButton.textContent = "-";
    customZoomControl.appendChild(zoomOutButton);
  
    // Add event listeners to handle zooming
    zoomInButton.addEventListener("click", function () {
      map.setZoom(map.getZoom() + 1);
    });
  
    zoomOutButton.addEventListener("click", function () {
      map.setZoom(map.getZoom() - 1);
    });
  
    // trafficLayer = new google.maps.TrafficLayer();
    // trafficLayer.setMap(map);
  
    map.addListener('drag', function() {
      if (popover != undefined) {
        popover.hide()
      }
    });
  
    map.addListener('click', function () {
      $('#offcanvasScrolling').offcanvas('hide')
      $('#offcanvasRight').offcanvas('hide')
    })
  
    trafficLayer = new google.maps.TrafficLayer();
    trafficLayer.setMap(map);
  
    // const arm = [...arn, ...styles.hiding]
    // map.setOptions({ styles: arm });

}

initMap()

function change_basemap() {
  const selectStyle = $('#basemap').val()
  base_map = selectStyle
  map.setOptions({ styles: styles[selectStyle] });
  $('#hidding_map').prop('checked', false);
}

function hidding_map_features() {
  let check = $('input[name="hidding_map"]:checked')
  const selectbase = $('#basemap').val()
  if (check.length == 0) {
    hide_label_map = false
    map.setOptions({ styles: styles[selectbase] });
  }else {
    hide_label_map =true
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
			if ($("#cvvip").hasClass("check-in-peta")) {
				$('#vip').addClass('d-none')
				$("#cvvip").removeClass("check-in-peta");
			} else {
				$('#vip').removeClass('d-none')
				$("#cvvip").addClass("check-in-peta");
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
          if ($("#ctco").hasClass("check-in-peta")) {
            if (dinas == 'korlantas') {
              icon = "./my/satupeta/cctv_icon_korlantas.png";
            }
            if (dinas == 'dishub') {
              icon = "./my/satupeta/cctv_icon_dishub.png";
            }
            item2 = ''
            $("#ctco").removeClass("check-in-peta");
          } else {
            $("#ctco").addClass("check-in-peta");
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
          if ($("#ctca").hasClass("check-in-peta")) {
            if (dinas == 'korlantas') {
              icon = "./my/satupeta/cctv_icon_korlantas.png";
            }
            if (dinas == 'dishub') {
              icon = "./my/satupeta/cctv_icon_dishub.png";
            }
            item2 = ''
            $("#ctca").removeClass("check-in-peta");
          } else {
            $("#ctca").addClass("check-in-peta");
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
          if ($("#cas").hasClass("check-in-peta")) {
            if (dinas == 'korlantas') {
              icon = "./my/satupeta/cctv_icon_korlantas.png";
            }
            if (dinas == 'dishub') {
              icon = "./my/satupeta/cctv_icon_dishub.png";
            }
            item2 = ''
            $("#cas").removeClass("check-in-peta");
          } else {
            $("#cas").addClass("check-in-peta");
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
      let url = getEmbedUrl('black_spot')
      varr = bs;
      if ($("#cbs").hasClass("check-in-peta")) {
        clearMarkerTitik(item);
        $("#cbs").removeClass("check-in-peta");
        $(`#jml_data_item_${item}`).remove()
      } else {
        icon = "./my/logo/menu_blackspot.png";
        showToast()
        $('#btn-cbs').prop('disabled', true);
        getDataSmNew(url).then(r => {
          if (r.status == true) {
            r.data = r.data.filter(item => 
              item.lat !== undefined && item.lat !== null && item.lat !== '' &&
              item.lng !== undefined && item.lng !== null && item.lng !== ''
            );
            titik_link = r
            create_titik_map(item, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${item}">
                  <span id="jml_data">Jumlah Black Spot <img src="./my/logo/menu_blackspot.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${item}`);
              animateValue(obj, 0, r.data.length, 200)
              closeToast()
            });
            $('#btn-cbs').prop('disabled', false);
            $("#cbs").addClass("check-in-peta");
          }else {
            swal(
              'Warning !',
              'Data Tidak Ditemukan !',
              'warning'
            );
            $('#btn-cbs').prop('disabled', false);
            closeToast()
          }
        });
      }
    }
    if (item == "trouble_spot") {
      let url = getEmbedUrl('trouble_spot')
      varr = ts;
      if ($("#cts").hasClass("check-in-peta")) {
        clearMarkerTitik(item);
        $("#cts").removeClass("check-in-peta");
        $(`#jml_data_item_${item}`).remove()
      } else {
        icon = "./my/logo/menu_troublespot.png";
        showToast()
        $('#btn-cts').prop('disabled', true);
        getDataSmNew(url).then(r => {
          if (r.status == true) {
            r.data = r.data.filter(item => 
              item.lat !== undefined && item.lat !== null && item.lat !== '' &&
              item.lng !== undefined && item.lng !== null && item.lng !== ''
            );
            titik_link = r
            create_titik_map(item, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${item}">
                  <span id="jml_data">Jumlah Trouble Spot <img src="./my/logo/menu_troublespot.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${item}`);
              animateValue(obj, 0, r.data.length, 200)
              closeToast()
            });
            $('#btn-cts').prop('disabled', false);
            $("#cts").addClass("check-in-peta");
          }else {
            swal(
              'Warning !',
              'Data Tidak Ditemukan !',
              'warning'
            );
            $('#btn-cts').prop('disabled', false);
            closeToast()
          }
        });
      }
    }
    if (item == "ambang_gangguan") {
      varr = ag;
      if ($("#cag").hasClass("check-in-peta")) {
        clearMarkerTitik(item);
        $("#cag").removeClass("check-in-peta");
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
          $("#cag").addClass("check-in-peta");
        });
      }
    }
  
    if (item == "rengiat_masyarakat") {
      let url = getEmbedUrl('rengiat_masyarakat')
      varr = gm;
      if ($("#cgm").hasClass("check-in-peta")) {
        clearMarkerTitik(item);
        $("#cgm").removeClass("check-in-peta");
        $(`#jml_data_item_${item}`).remove()
      } else {
        showToast()
        $('#btn-cgm').prop('disabled', true);
        icon = "./my/logo/menu_rengiatm.png";
        getDataSmNew(url).then(r => {
        if(r.status == true) {
          r.data = r.data.filter(item => 
            item.lat !== undefined && item.lat !== null && item.lat !== '' &&
            item.lng !== undefined && item.lng !== null && item.lng !== ''
          );
          titik_link = r
          create_titik_map(item, icon, r.data).then(e => {
            $('#jml_data_group').append(`
              <div class="jml_data mt-2" id="jml_data_item_${item}">
                <span id="jml_data">Jumlah Kegiatan Masyarakat <img src="./my/logo/menu_rengiatm.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
              </div>
            `)
            const obj = document.getElementById(`counter_${item}`);
            animateValue(obj, 0, r.data.length, 200)
          });
          $('#btn-cgm').prop('disabled', false);
          $("#cgm").addClass("check-in-peta");
        }else {
          swal(
            'Warning !',
            'Data Tidak Ditemukan !',
            'warning'
          );
          $('#btn-cgm').prop('disabled', false);
          closeToast()
        }
        });
      }
    }
    if (item == "rawan") {
      if ($("#cr").hasClass("check-in-peta")) {
        $('#rawan').addClass('d-none')
        $("#cr").removeClass("check-in-peta");
        clearAllMarkerRawan()
      } else {
        prom_get_kategori_rawan()
        $('#rawan').removeClass('d-none')
        $("#cr").addClass("check-in-peta");
      }
    }
  
    if (item == "rengiat") {
      let url = getEmbedUrl('rengiat')
      varr = rg;
      if ($("#rg").hasClass("check-in-peta")) {
        clearMarkerTitik(item);
        $("#rg").removeClass("check-in-peta");
        $(`#jml_data_item_${item}`).remove()
      } else {
        showToast()
        $('#btn-rg').prop('disabled', true);
        icon = "./my/logo/menu_rengiat.png";
        getDataSmNew(url).then(r => {
          if (r.status == true) {
            r.data = r.data.filter(item => 
              item.lat !== undefined && item.lat !== null && item.lat !== '' &&
              item.lng !== undefined && item.lng !== null && item.lng !== ''
            );
            titik_link = r
            create_titik_map(item, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${item}">
                  <span id="jml_data">Jumlah Ren Giat <img src="./my/logo/menu_rengiat.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)  <button onclick="dt_rengiat()" class="btn btn-primary btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#modal-detail"><i class="fa fa-info" style="padding:3px;"></i></button></span>
                </div>
              `)
              const obj = document.getElementById(`counter_${item}`);
              animateValue(obj, 0, r.data.length, 200)
              closeToast()
            });
            $('#btn-rg').prop('disabled', false);
            $("#rg").addClass("check-in-peta");
          }else {
            swal(
              'Warning !',
              'Data Tidak Ditemukan !',
              'warning'
            );
            $('#btn-rg').prop('disabled', false);
            closeToast()
          }
        });
      }
    }

    if (item == "renops_pol") {
      let url = getEmbedUrl('renops_pol')
      varr = ro;
      if ($("#ro").hasClass("check-in-peta")) {
        clearMarkerTitik(item);
        $("#ro").removeClass("check-in-peta");
        $(`#jml_data_item_${item}`).remove()
      } else {
        icon = "./my/logo/menu_renops.png";
        showToast()
        $('#btn-ro').prop('disabled', true);
        getDataSmNew(url).then(r => {
          if (r.status == true) {
            r.data = r.data.filter(item => 
              item.lat !== undefined && item.lat !== null && item.lat !== '' &&
              item.lng !== undefined && item.lng !== null && item.lng !== ''
            );
            titik_link = r
            create_titik_map(item, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${item}">
                  <span id="jml_data">Jumlah Ren Ops Pol <img src="./my/logo/menu_renops.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${item}`);
              animateValue(obj, 0, r.data.length, 200)
              closeToast()
            });
            $('#btn-ro').prop('disabled', false);
            $("#ro").addClass("check-in-peta");
          }else {
            swal(
              'Warning !',
              'Data Tidak Ditemukan !',
              'warning'
            );
            $('#btn-ro').prop('disabled', false);
            closeToast()
          }
        });
      }
    }
    if (item == "fasum") {
      if ($("#fsm").hasClass("check-in-peta")) {
        $('#fasum').addClass('d-none');
        $("#fsm").removeClass("check-in-peta");
        clearAllMarkerFasum()
      } else {
        prom_get_kategori_fasum();
        $('#fasum').removeClass('d-none');
        $("#fsm").addClass("check-in-peta");
      }
    }
    if (item == "objek_vital") {
      let url = getEmbedUrl('objek_vital')
      varr = ov;
      if ($("#ov").hasClass("check-in-peta")) {
        clearMarkerTitik(item);
        $("#ov").removeClass("check-in-peta");
        $(`#jml_data_item_${item}`).remove()
      } else {
        icon = "./my/logo/menu_ovit.png";
        showToast()
        $('#btn-ov').prop('disabled', true);
        getDataSmNew(url).then(r => {
          if (r.status == true) {
            r.data = r.data.filter(item => 
              item.lat !== undefined && item.lat !== null && item.lat !== '' &&
              item.lng !== undefined && item.lng !== null && item.lng !== ''
            );
            titik_link = r
            create_titik_map(item, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${item}">
                  <span id="jml_data">Jumlah Objek Vital <img src="./my/logo/menu_ovit.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${item}`);
              animateValue(obj, 0, r.data.length, 200)
              closeToast()
            });
            $('#btn-ov').prop('disabled', false);
            $("#ov").addClass("check-in-peta");
          }else {
            swal(
              'Warning !',
              'Data Tidak Ditemukan !',
              'warning'
            );
            $('#btn-ov').prop('disabled', false);
            closeToast()
          }
        });
      }
    }

    if (item == "posko") {
      let url = getEmbedUrl('posko')
      varr = pko;
      if ($("#pko").hasClass("check-in-peta")) {
        clearMarkerTitik(item);
        $("#pko").removeClass("check-in-peta");
        $(`#jml_data_item_${item}`).remove()
      } else {
        icon = "./my/logo/menu_posko.png";
        showToast()
        $('#btn-pko').prop('disabled', true);
        getDataSmNew(url).then(r => {
          if (r.status == true) {
            r.data = r.data.filter(item => 
              item.lat !== undefined && item.lat !== null && item.lat !== '' &&
              item.lng !== undefined && item.lng !== null && item.lng !== ''
            );
            titik_link = r
            create_titik_map(item, icon, r.data).then(e => {
              $('#jml_data_group').append(`
                <div class="jml_data mt-2" id="jml_data_item_${item}">
                  <span id="jml_data">Jumlah Posko <img src="./my/logo/menu_posko.png" class="jml_data_img"> (<span><b id="counter_${item}"></b></span>)</span>
                </div>
              `)
              const obj = document.getElementById(`counter_${item}`);
              animateValue(obj, 0, r.data.length, 200)
              closeToast()
            });
            $('#btn-pko').prop('disabled', false);
            $("#pko").addClass("check-in-peta");
          }else {
            swal(
              'Warning !',
              'Data Tidak Ditemukan !',
              'warning'
            );
            $('#btn-pko').prop('disabled', false);
            closeToast()
          }
        });
      }
    }

    if (item == "mgmt_route") {
      if ($("#mr").hasClass("check-in-peta")) {
        $('#mgmt_route').addClass('d-none');
        $("#mr").removeClass("check-in-peta");
        $('#jml_data_item_mgmt_route').remove()
      } else {
        mgmt_route()
        $('#mgmt_route').removeClass('d-none');
        $("#mr").addClass("check-in-peta");
      }
    }

    if (item == "tomtom") {
      if ($("#tom").hasClass("check-in-peta")) {
        // $('#mgmt_route').addClass('d-none');
        $("#tom").removeClass("check-in-peta");
        $('#jml_data_item_tom').remove()
        for (let i = 0; i < geotom.length; i++) {
          map.data.remove(geotom[i])
        }
      } else {
        // mgmt_route()
        const requestOptions = {
          method: "GET",
          redirect: "follow"
        };
        fetch(`${global_url_tomtom}traffic/services/5/incidentDetails?key=${global_key_tomtom}&bbox=${global_bbox_tomtom}&fields={incidents{type,geometry{type,coordinates},properties{iconCategory,startTime,endTime,from,to,length,delay,timeValidity}}}&timeValidityFilter=present`, requestOptions)
          .then((response) => response.text())
          .then(result => {
            let jsonData = JSON.parse(result)
            jsonData.type = "FeatureCollection"
            jsonData.features = jsonData.incidents
            delete jsonData.incidents
      
            const category = jsonData.features.reduce((acc, item) => {
              // Jika kota belum ada dalam akumulator, tambahkan array kosong
              if (!acc[item.properties.iconCategory]) {
                  acc[item.properties.iconCategory] = [];
              }
              // Tambahkan item ke array yang sesuai dengan kota
              acc[item.properties.iconCategory].push(item);
              return acc;
            }, {});
      
            // Menambahkan properti warna ke setiap fitur
            jsonData.features.forEach(function(feature, index) {
              // feature.properties.color = colors[index % colors.length];
              if (feature.properties.iconCategory == 6) {
                feature.properties.color ='pink';
              }else if(feature.properties.iconCategory == 8){
                feature.properties.color ='purple';
              }
            });

            $('#jml_data_group').append(`
            <div class="jml_data mt-2" id="jml_data_item_tom">
              <span id="jml_data" style="color:pink">Jumlah traffic tinggi  (<span>${category[6] != undefined ? category[6].length : '0'}</span>)</span><br>
              <span id="jml_data" style="color:purple">Jumlah jalan ditutup  (<span>${category[8] != undefined ? category[8].length : '0'}</span>)</span>
            </div>
          `)
            geotom = map.data.addGeoJson(jsonData);
      
            map.data.setStyle(function(feature) {
              return {
                strokeColor: feature.getProperty('color'),
                strokeWeight: 4
              };
            });

            // Tooltip menggunakan InfoWindow
            var infoWindowTom = new google.maps.InfoWindow();

            // Event listener untuk menampilkan tooltip pada setiap fitur
            map.data.addListener('click', function(event) {
              var content = `
                <h5>${event.feature.Fg.from} - ${event.feature.Fg.to}</h5>
                <table>
                  <tbody>
                    <tr>
                      <td>Category</td>
                      <td>:</td>
                      <td>${event.feature.Fg.iconCategory == 6 ? 'Traffic tinggi' : event.feature.Fg.iconCategory == 8 ? 'Jalan ditutup' : '-'}</td>
                    </tr>
                    <tr>
                      <td>Start Time</td>
                      <td>:</td>
                      <td>${event.feature.Fg.startTime ? isoDateToDateTime(event.feature.Fg.startTime): "-"}</td>
                    </tr>
                    <tr>
                      <td>End Time</td>
                      <td>:</td>
                      <td>${event.feature.Fg.endTime ? isoDateToDateTime(event.feature.Fg.endTime): "-"}</td>
                    </tr>
                  </tbody>
                </table>
              `
              var position = event.latLng;
              infoWindowTom.setContent(content);
              infoWindowTom.setPosition(position);
              infoWindowTom.open(map);
            });

            // Event listener untuk menyembunyikan tooltip ketika mouse keluar dari fitur
            // map.data.addListener('mouseout', function(event) {
            //   infoWindowTom.close();
            // });
          })
          .catch((error) => console.error(error));
        // $('#mgmt_route').removeClass('d-none');
        $("#tom").addClass("check-in-peta");
      }
    }
  }, 150);

}

var circle_bs = []
var circle_ts = []
var clr_bs = []
var clr_ts = []
var clr_rg = []
var clr_ro = []
var clr_fasum = []
var clr_ov= []
var clr_pko= []
function create_titik_map(item, img, arr) {
  return new Promise(async function (resolve, reject) {
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

		// GET DROP ANIMATION CSS
		initMarkerDropAnimationCSS();

		titik_arr = [];
		let m_titik = [];
	
		// var icon = {
    //   url: img, // url
    //   // url : "my/satupeta/blackspot2.png",
    //   scaledSize: new google.maps.Size(30, 30), // scaled size
    // };
    
    for (let i = 0; i < titik_link.data.length; i++) {
      const gambar = document.createElement('img');
        gambar.src = img; // Bisa JPG atau PNG
        gambar.style.width = '30px';
        gambar.style.height = '30px';
				const content = gambar;
				content.style.setProperty('--delay-time', `${Math.random() + 0.5}s`);
				content.style.opacity = '0';

      // window.setTimeout(() => {
        
        // m_titik.push(
          const marker = new AdvancedMarkerElement({
            position: { lat : parseFloat(titik_link.data[i].lat), lng : parseFloat(titik_link.data[i].lng)},
            map,
            content:  gambar,
            // icon : icon,
            // animation: google.maps.Animation.DROP,
            // polda_id : titik_link.data[i].polda_id,
            // polres_id : titik_link.data[i].polres_id
          });
        // );
      // }, i * 200);

      
      if (item == "black_spot") {
        circle_bs.push(new google.maps.Circle({
          map: map,
          radius: 500,
          fillColor: '#F80404',
          fillOpacity: .3,
          strokeColor: '#313131',
          strokeOpacity: .4,
          strokeWeight: .8,
          center: { lat : parseFloat(titik_link.data[i].lat), lng : parseFloat(titik_link.data[i].lng)}
        }))
        // circle_bs[i].bindTo('center', m_titik[i], 'position');
      }
      if (item == "trouble_spot") {
        circle_ts.push(new google.maps.Circle({
          map: map,
          // clickable: false,
          // metres
          radius: 500,
          fillColor: '#F80404',
          fillOpacity: .3,
          strokeColor: '#313131',
          strokeOpacity: .4,
          strokeWeight: .8,
          center: { lat : parseFloat(titik_link.data[i].lat), lng : parseFloat(titik_link.data[i].lng)}
        }))
        // circle_ts[i].bindTo('center', m_titik[i], 'position');
      }

			content.addEventListener('animationend', () => {
				content.classList.remove('marker-animation');
				content.style.opacity = '1';
			});

			// ANIMATION OF THE PATH AS IT DROPS
			intersectionObserver.observe(content);

			m_titik.push(marker);
    }

    if (item == "black_spot") {
      bs.push(m_titik);
      // clr_bs.push(new MarkerClusterer( map, bs[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
      setInfoWindows(arr, bs[0], "Black Spot")
    }

    if (item == "trouble_spot") {
      ts.push(m_titik);
      // clr_ts.push(new MarkerClusterer( map, ts[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
      setInfoWindows(arr, ts[0], "Trouble Spot")
    }

    if (item == "ambang_gangguan") {
      ag.push(m_titik);
      setInfoWindows(arr, ag[0], "Ambang Gangguan")
    }

    if (item == "rengiat_masyarakat") {
      gm.push(m_titik);
      setInfoWindows(arr, gm[0], "Rengiat Masyarakat")
    }

    if (item == "rengiat") {
      rg.push(m_titik);
      // clr_rg.push(new MarkerClusterer( map, rg[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
      setInfoWindows(arr, rg[0], "Rengiat")
    }

    if (item == "renops_pol") {
      ro.push(m_titik);
      // clr_ro.push(new MarkerClusterer( map, ro[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
      setInfoWindows(arr, ro[0], "Rengiat")
    }

    if (item == "fasum") {
      fasum.push(m_titik);
      // clr_fasum.push(new MarkerClusterer( map, fasum[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
      setInfoWindows(arr, fasum[0], "Fasilitas Umum")
    }

    if (item == "objek_vital") {
      ov.push(m_titik);
      // clr_ov.push(new MarkerClusterer( map, ov[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
      setInfoWindows(arr, ov[0], "Objek Vital")
    }

    if (item == "posko") {
      pko.push(m_titik);
      // clr_pko.push(new MarkerClusterer( map, pko[0] ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'}))
      setInfoWindows(arr, pko[0], "Posko")
    }

    // new MarkerClusterer( map, m_titik ,{imagePath: 'https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/images/m'});
		resolve(titik_link.data);
	})
}

function clearMarkerTitik(arr) {
	if (arr == "black_spot") {
		for (let i = 0; i < bs[0].length; i++) {
			bs[0][i].setMap(null);
		}

    bs = [];
    for (let i = 0; i < circle_bs.length; i++) {
      circle_bs[i].setMap(null)
		}
    circle_bs = []

    for (let i = 0; i < clr_bs.length; i++) {
      clr_bs[i].clearMarkers()
		}
    clr_bs = []
	}
	if (arr == "trouble_spot") {
		for (let i = 0; i < ts[0].length; i++) {
			ts[0][i].setMap(null);
		}

    ts = [];
    for (let i = 0; i < circle_ts.length; i++) {
      circle_ts[i].setMap(null)
		}
    circle_ts = []

    for (let i = 0; i < clr_ts.length; i++) {
      clr_ts[i].clearMarkers()
		}
    clr_ts = []
	}
	if (arr == "ambang_gangguan") {
		for (let i = 0; i < ag[0].length; i++) {
			ag[0][i].setMap(null);
		}

    ag = [];
	}
	if (arr == "rengiat_masyarakat") {
		for (let i = 0; i < gm[0].length; i++) {
			gm[0][i].setMap(null);
		}

    gm = [];
	}
  if (arr == "rengiat") {
		for (let i = 0; i < rg[0].length; i++) {
			rg[0][i].setMap(null);
		}

    rg = [];

    for (let i = 0; i < clr_rg.length; i++) {
      clr_rg[i].clearMarkers()
		}
    clr_rg = []
	}

  if (arr == "renops_pol") {
		for (let i = 0; i < ro[0].length; i++) {
			ro[0][i].setMap(null);
		}

    ro = [];

    for (let i = 0; i < clr_ro.length; i++) {
      clr_ro[i].clearMarkers()
		}
    clr_ro = []
	}

  if (arr == "fasum") {
		for (let i = 0; i < fasum[0].length; i++) {
			fasum[0][i].setMap(null);
		}

    fasum = [];

    for (let i = 0; i < clr_fasum.length; i++) {
      clr_fasum[i].clearMarkers()
		}
    clr_fasum = []
	}

  if (arr == "objek_vital") {
		for (let i = 0; i < ov[0].length; i++) {
			ov[0][i].setMap(null);
		}

    ov = [];

    for (let i = 0; i < clr_ov.length; i++) {
      clr_ov[i].clearMarkers()
		}
    clr_ov = []
	}

  if (arr == "posko") {
		for (let i = 0; i < pko[0].length; i++) {
			pko[0][i].setMap(null);
		}

    pko = [];

    for (let i = 0; i < clr_pko.length; i++) {
      clr_pko[i].clearMarkers()
		}
    clr_pko = []
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
        <td>${arr[i].nama_jalan_s}</td>
        </tr>
        <tr>
        <td><b>Kordinat</b></td>
        <td>:</td>
        <td>${arr[i].lat + "," + arr[i].lng}</td>
        </tr>
        <tr>
        <td><b>Tanggal</b></td>
        <td>:</td>
        <td>${isoDateToDate(arr[i].tanggal)}</td>
        </tr>
        <tr>
        <td><b>Status</b></td>
        <td>:</td>
        <td>${arr[i].status}</td>
        </tr>
        <tr>
        <td><b>Jumlah Laka</b></td>
        <td>:</td>
        <td>${arr[i].jml_laka}</td>
        </tr>
        <tr>
        <td><b>Jumlah Korban MD</b></td>
        <td>:</td>
        <td>${arr[i].jml_korban_md}</td>
        </tr>
        <tr>
        <td><b>Jumlah Korban LB</b></td>
        <td>:</td>
        <td>${arr[i].jml_korban_lb}</td>
        </tr>
        </table>
        </div>
        `);
        infowindow.open(map, titik[i]);
      });
	  }
	}

  if (n_titik== "Trouble Spot") {
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
        <td>${arr[i].nama_jalan_s}</td>
        </tr>
        <tr>
        <td><b>Kordinat</b></td>
        <td>:</td>
        <td>${arr[i].lat + "," + arr[i].lng}</td>
        </tr>
        <tr>
        <td><b>Tanggal</b></td>
        <td>:</td>
        <td>${isoDateToDate(arr[i].tanggal)}</td>
        </tr>
        <tr>
        <td><b>Status</b></td>
        <td>:</td>
        <td>${arr[i].status}</td>
        </tr>
        <tr>
        <td><b>Jumlah Kemacetan</b></td>
        <td>:</td>
        <td>${arr[i].jumlah_kemacetan}</td>
        </tr>
        <tr>
        <td><b>Durasi Macet</b></td>
        <td>:</td>
        <td>${arr[i].durasi_macet + ' Menit'}</td>
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

  if (n_titik=="Rengiat Masyarakat") {
    for (let i = 0; i < arr.length; i++) {
      google.maps.event.addListener(titik[i], 'click', function(){
            map.setZoom(15);
            map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
          infowindow.setContent(`
          <div><p><b>${n_titik}</b></p>
          <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
          <table class="w-100">
          <tr>
          <td><b>Jenis Kegiatan</b></td>
          <td>:</td>
          <td>${arr[i].jenis_kegiatan}</td>
          </tr>
          <tr>
          <td><b>Penanggung Jawab</b></td>
          <td>:</td>
          <td>${arr[i].penanggung_jawab}</td>
          </tr>
          <tr>
          <tr>
          <td><b>Jumlah Peserta</b></td>
          <td>:</td>
          <td>${arr[i].jumlah_peserta}</td>
          </tr>
          <tr>
          <td><b>Nama Jalan</b></td>
          <td>:</td>
          <td>${arr[i].nama_jalan_s}</td>
          </tr>
          <tr>
          <td><b>Kordinat</b></td>
          <td>:</td>
          <td>${arr[i].lat + "," + arr[i].lng}</td>
          </tr>
          <tr>
          <td><b>Tanggal</b></td>
          <td>:</td>
          <td>${isoDateToDate(arr[i].tanggal)}</td>
          </tr>
          </table>
          </div>
          `);
          infowindow.open(map, titik[i]);
      });
    }
  }

  if (n_titik=="Rengiat") {
		for (let i = 0; i < arr.length; i++) {
		  google.maps.event.addListener(titik[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
			  infowindow.setContent(`
			  <div><p><b>${n_titik}</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Jenis Kegiatan</b></td>
			  <td>:</td>
			  <td>${arr[i].jenis_kegiatan}</td>
			  </tr>
			  <tr>
			  <td><b>Kategori Kegiatan</b></td>
			  <td>:</td>
			  <td>${arr[i].kategori_kegiatan}</td>
			  </tr>
			  <tr>
			  <td><b>Nama Jalan</b></td>
			  <td>:</td>
			  <td>${arr[i].nama_jalan_s}</td>
			  </tr>
			  <tr>
			  <td><b>Kordinat</b></td>
			  <td>:</td>
			  <td>${arr[i].lat + "," + arr[i].lng}</td>
			  </tr>
			  <tr>
			  <td><b>Tanggal</b></td>
			  <td>:</td>
			  <td>${isoDateToDate(arr[i].tanggal)}</td>
			  </tr>
			  <tr>
			  <td><b>Sasaran Giat</b></td>
			  <td>:</td>
			  <td>${arr[i].sasaran_giat}</td>
			  </tr>
        <tr>
        <td><b>Penanggung Jawab</b></td>
			  <td>:</td>
			  <td>${arr[i].penanggung_jawab}</td>
			  </tr>
        <tr>
        <td><b>Keterangan</b></td>
			  <td>:</td>
			  <td>${arr[i].keterangan != null ? arr[i].keterangan : '-'}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
	}

  if (n_titik=="Fasilitas Umum") {
		for (let i = 0; i < arr.length; i++) {
		  google.maps.event.addListener(titik[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
			  infowindow.setContent(`
			  <div><p><b>${n_titik}</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Fasilitas Umum</b></td>
			  <td>:</td>
			  <td>${arr[i].fasilitas}</td>
			  </tr>
			  <tr>
			  <td><b>Nama Jalan</b></td>
			  <td>:</td>
			  <td>${arr[i].nama_jalan_s}</td>
			  </tr>
			  <tr>
			  <td><b>Kordinat</b></td>
			  <td>:</td>
			  <td>${arr[i].lat + "," + arr[i].lng}</td>
			  </tr>
			  <tr>
			  <td><b>Kapasitas Orang</b></td>
			  <td>:</td>
			  <td>${arr[i].kapasitas_orang}</td>
			  </tr>
			  <tr>
			  <td><b>Kapasitas Parkir</b></td>
			  <td>:</td>
			  <td>${arr[i].kapasitas_parkir}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, titik[i]);
		  });
		}
	  }

    if (n_titik=="Objek Vital") {
      for (let i = 0; i < arr.length; i++) {
        google.maps.event.addListener(titik[i], 'click', function(){
          map.setZoom(15);
          map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
          infowindow.setContent(`
          <div><p><b>${n_titik}</b></p>
          <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
          <table class="w-100">
          <tr>
          <td><b>Fasilitas</b></td>
          <td>:</td>
          <td>${arr[i].fasilitas}</td>
          </tr>
          <tr>
          <td><b>Keterangan Objek Vital</b></td>
          <td>:</td>
          <td>${arr[i].ket_fasilitas}</td>
          </tr>
          <tr>
          <td><b>Nama Jalan</b></td>
          <td>:</td>
          <td>${arr[i].nama_jalan_s}</td>
          </tr>
          <tr>
          <td><b>Kordinat</b></td>
          <td>:</td>
          <td>${arr[i].lat + "," + arr[i].lng}</td>
          </tr>
          <tr>
          <td><b>${arr[i].fasilitas.toUpperCase() === "HOTEL" || arr[i].fasilitas.toUpperCase() === "PUSKESMAS" || arr[i].fasilitas.toUpperCase() === "RUMAH SAKIT" ? "Jumlah Kamar" : "Kapasitas Orang"}</b></td>
          <td>:</td>
          <td>${arr[i].kapasitas_orang}</td>
          </tr>
          <tr>
          <td><b>Kapasitas Parkir</b></td>
          <td>:</td>
          <td>${arr[i].kapasitas_parkir}</td>
          </tr>
          </table>
          </div>
          `);
          infowindow.open(map, titik[i]);
        });
      }
      }

      if (n_titik=="Posko") {
        for (let i = 0; i < arr.length; i++) {
          google.maps.event.addListener(titik[i], 'click', function(){
            map.setZoom(15);
            map.setCenter({ lat : parseFloat(arr[i].lat), lng : parseFloat(arr[i].lng)});
            infowindow.setContent(`
            <div><p><b>${n_titik}</b></p>
            <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
            <table class="w-100">
            <tr>
            <td><b>Status Posko</b></td>
            <td>:</td>
            <td>${arr[i].status_posko}</td>
            </tr>
            <tr>
            <td><b>Type Posko</b></td>
            <td>:</td>
            <td>${arr[i].type_posko}</td>
            </tr>
            <tr>
            <td><b>Jenis Operasi</b></td>
            <td>:</td>
            <td>OPERASI KETUPAT - ${arr[i].jenis_kegiatan}</td>
            </tr>
            <tr>
            <td><b>Kategori Kegiatan</b></td>
            <td>:</td>
            <td>${arr[i].kategori_kegiatan}</td>
            </tr>
            <tr>
            <td><b>Nama Jalan</b></td>
            <td>:</td>
            <td>${arr[i].nama_jalan_s}</td>
            </tr>
            <tr>
            <td><b>Kordinat</b></td>
            <td>:</td>
            <td>${arr[i].lat + "," + arr[i].lng}</td>
            </tr>
            <tr>
            <td><b>Tanggal</b></td>
            <td>:</td>
            <td>${isoDateToDate(arr[i].tanggal)}</td>
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
		// $.ajax({
		// 	type: "POST",
		// 	url: './PemetaanNew/get_kategori_kml',
		// 	dataType: "json",
		// 	data: {polda_id : polda_id, polres_id : polres_id},
		// 	success: function (r) {
    //     r.forEach(e => {
    //       $('#v-kategori-kml').append(`
    //       <a class="nav-link list d-flex align-items-center border-bottom border-end" id="v-kml-tab" onclick="kml(${e.id})" data-bs-toggle="pill" data-bs-target="#v-kml" href="javascript:void(0);" role="tab" aria-controls="v-kml" aria-selected="false">
    //         <div class="wrapper ml-3">
    //           <p class="mb-0">
    //           ${e.value}</p>
    //         </div>
    //       </a> 
    //       `)
    //     });
		// 	},
		// });
    let url = getEmbedUrl('kml_kategori')
    getDataSmNew(url).then(r => {
      showToast()
      if (r.status == true) {
        r.data.forEach(e => {
          $('#v-kategori-kml').append(`
          <a class="nav-link list d-flex align-items-center border-bottom border-end" id="v-kml-tab" onclick="kml(${e.rowid})" data-bs-toggle="pill" data-bs-target="#v-kml" href="javascript:void(0);" role="tab" aria-controls="v-kml" aria-selected="false">
            <div class="wrapper ml-3">
              <p class="mb-0">
              ${e.kategori}</p>
            </div>
          </a> 
          `)
        });
        closeToast()
      }else {
        // swal(
        //   'Warning !',
        //   'Data KML Tidak Ditemukan !',
        //   'warning'
        // );
        closeToast()
      }
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
    hide_traffic_map = false
    trafficLayer.setMap(null);
  }else {
    hide_traffic_map = true
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

function GetURLParameter(sParam) {
	var sPageURL = window.location.search.substring(1);
	var sURLVariables = sPageURL.split("&");
	for (var i = 0; i < sURLVariables.length; i++) {
		var sParameterName = sURLVariables[i].split("=");
		if (sParameterName[0] == sParam) {
			return sParameterName[1];
		}
	}
}

// let u = GetURLParameter("u")
let display = GetURLParameter("display")
let lokasi = GetURLParameter("lokasi")
let center = GetURLParameter("center")
let car = GetURLParameter("car")
let officer = GetURLParameter("officer")
let giat = GetURLParameter("giat")
let giat_m = GetURLParameter("giat_m")
let ops = GetURLParameter("ops")
let btn_out = GetURLParameter("btn_out")
var urlParameter = {
  // u : u,
  display : display,
	lokasi: lokasi,
  center: center,
  car : car,
  officer : officer,
  giat : giat,
  giat_m : giat_m,
  ops : ops,
  btn_out : btn_out,
};

$(function () {
  // setInterval(() => {
  //     cek_sso(`${id_unique}`)
  //   }, 3000);
  setTimeout(() => {
    get_polda( usr_polda_id != '' ? `polda_id=${usr_polda_id}` : '').then(function (e) {
      e.forEach(f => {
        $('#polda_id').append(`
          <option ${usr_polda_id == f.polda_id ? `selected` : ''} value="${f.polda_id}">${f.polda}</option>
        `)
        usr_polda_id == f.polda_id ? $('#polda_id').prop('disabled', true) : ''
      });
      $( '.custom-select2' ).select2( {
        placeholder: $( this ).data( 'placeholder' ),
        allowClear: true
      } );
      $('#title_card_kanan').html('')
      $('#title_card_kanan').append($("#polda_id option:selected").text())
    })
    // if (usr_polres_id != '') {
      $('#f_polres_id').removeClass('d-none')
      get_polres( `polda_id=${usr_polda_id}`).then(function (e) {
        e.forEach(f => {
          $('#polres_id').append(`
            <option ${usr_polres_id == f.polres_id ? `selected` : ''} value="${f.polres_id}">${f.polres}</option>
          `)
          usr_polres_id == f.polres_id ? $('#polres_id').prop('disabled', true) : ''
        });
        $( '.custom-select2' ).select2( {
          placeholder: $( this ).data( 'placeholder' ),
          allowClear: true
        } );
        $('#title_card_kanan').html('')
        $('#title_card_kanan').append($("#polres_id option:selected").text())
      })
    // }
  }, 1000);

  // if (urlParameter["u"] == "" || urlParameter["u"] == undefined) {
  //     alert('silahkan login terlebih dahulu !!!')
  //     window.location.href = `${global_url_ci}Auth/logout_sso`;
  // }
  
  // if (urlParameter["u"] != undefined && urlParameter["u"] != "") {
  //   if (sess_sso != "1") {
  //     let code = atob(u)
  //     sess_sso = "0"
  //     window.location.href = `${global_url_ci}Auth/login_sso?code=${code}`;
  //   }
  // }

  // if (localStorage.getItem("rtsp")) {
  //   let ls = JSON.parse(localStorage.getItem("rtsp"));
  //   if (ls.length != '') {
  //     for (let i = 0; i < ls.length; i++) {
  //       stream_jump_dum('stop', { unique_id : ls[i]}).then((data) => {
  //         console.log('stop stream');
  //         g_rtsp = []
  //         localStorage.setItem('rtsp',JSON.stringify([]))
  //         retrying = false
  //         clearInterval(retry)
  //         player.reset()
  //         player.dispose()
  //         hls.destroy()
  //       })
  //     }
  //   }
  // }

  if (localStorage.getItem("rtsp_vehicle")) {
    let ls = JSON.parse(localStorage.getItem("rtsp_vehicle"));
    if (ls.length != '') {
      for (let i = 0; i < ls.length; i++) {
        stream_jump_dum('stop', { unique_id : ls[i]}).then((data) => {
          console.log('stop stream car');
          g_rtsp_vehicle = []
          localStorage.setItem('rtsp_vehicle',JSON.stringify([]))
          retrying_vehicle = false
          clearInterval(retry_vehicle)
          player_vehicle.reset()
          player_vehicle.dispose()
          hls_vehicle.destroy()
        })
      }
    }
  }
  

  if (urlParameter["display"] == "off") {
		$('#menu-panel').remove();
		$('#menu-filter').remove();
	}
  if (urlParameter["btn_out"] == "off") {
		$('.button-logout').remove();
	}
	if (urlParameter["lokasi"] != undefined && urlParameter["lokasi"] != "") {
		get_lokasiPengaduan((zoomGet = false ))
	}
  if (urlParameter["giat"] != undefined && urlParameter["giat"] != "") {
		get_lokasiRengiat()
	}
  if (urlParameter["giat_m"] != undefined && urlParameter["giat_m"] != "") {
		get_lokasiRengiatM()
	}
  if (urlParameter["ops"] != undefined && urlParameter["ops"] != "") {
		get_lokasiRenOps()
	}
  if (urlParameter["car"] != undefined && urlParameter["car"] == "on") {
    // alert(car)
    get_mobil("");
  }

  if (urlParameter["officer"] != undefined && urlParameter["officer"] == "on") {
    // alert(officer)
    get_petugas("");
  }

  if (urlParameter["center"] != undefined && urlParameter["center"] != "") {
    let kordinat = center.split(",")
    setTimeout(() => {
        map.setOptions({ center: { lat: parseFloat(kordinat[0]) , lng: parseFloat(kordinat[1]) } });
    }, 100);
}
});

var loks = {};

function get_lokasiPengaduan(zoomGet) {
	let lk = lokasi.split(",");
	var updatedJson = {
		BMS: [
			{
				id: "lokasi",
				lat: lk[0],
				long: lk[1],
				nama: "",
				zoom: zoomGet,
			},
		],
	};
	setTimeout(() => {
			setMarkers(updatedJson);
	}, 1000);
	// return updatedJson;
}

function setMarkers(data) {
      loks[data.BMS[0].id] = new google.maps.Marker({
			  position: { lat : parseFloat(data.BMS[0].lat), lng : parseFloat(data.BMS[0].long)},
			  map,
			  icon : './my/satupeta/emergency.png',
			  // animation: google.maps.Animation.DROP,
			})

      var myoverlay = new google.maps.OverlayView();
       myoverlay.draw = function () {
           // add an id to the layer that includes all the markers so you can use it in CSS
           this.getPanes().markerLayer.id='markerLayer';
       };
       myoverlay.setMap(map);

       map.setCenter({ lat : parseFloat(data.BMS[0].lat), lng : parseFloat(data.BMS[0].long)});

      const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow

      google.maps.event.addListener(loks.lokasi, 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(data.BMS[0].lat), lng : parseFloat(data.BMS[0].long)});
			  infowindow.setContent(`
			  <div><p><b>TITIK PENGADUAN</b></p>
			  <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
			  <table class="w-100">
			  <tr>
			  <td><b>Kordinat</b></td>
			  <td>:</td>
			  <td> ${data.BMS[0].lat},${data.BMS[0].long}</td>
			  </tr>
			  </table>
			  </div>
			  `);
			  infowindow.open(map, loks.lokasi);
		  });

	  playAudio();
}

function playAudio() {
	var audio = document.getElementById("musicplayer");
	audio.load(); // Load the new audio source
  // audio.currentTime = 10;
	audio.play().catch(function (error) {
		console.log("Failed to play audio:", error);
	});
}

function get_lokasiRengiat() {
	let rgt = giat.split(",");

  var icon = {
    url : "my/satupeta/rengiat.png",
    scaledSize: new google.maps.Size(30, 30), // scaled size
  };
  setTimeout(() => {
    rengiat = new google.maps.Marker({
      position: { lat : parseFloat(rgt[0]), lng : parseFloat(rgt[1])},
      map,
      icon : icon,
      // animation: google.maps.Animation.DROP,
    })
    map.setCenter({ lat : parseFloat(rgt[0]), lng : parseFloat(rgt[1])});
    const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow

    getDataSmNew(global_url_api+'data_collecting/rengiat/'+rgt[2]).then(r => {
      google.maps.event.addListener(rengiat, 'click', function(){
        map.setZoom(15);
        map.setCenter({ lat : parseFloat(rgt[0]), lng : parseFloat(rgt[1])});
        infowindow.setContent(`
        <div><p><b>RENGIAT</b></p>
        <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
        <table class="w-100">
        <tr>
        <td><b>Jenis Kegiatan</b></td>
        <td>:</td>
        <td>${r.data.jenis_kegiatan}</td>
        </tr>
        <tr>
        <td><b>Kategori Kegiatan</b></td>
        <td>:</td>
        <td>${r.data.kategori_kegiatan}</td>
        </tr>
        <tr>
        <td><b>Nama Jalan</b></td>
        <td>:</td>
        <td>${r.data.nama_jalan_s}</td>
        </tr>
        <tr>
        <td><b>Kordinat</b></td>
        <td>:</td>
        <td>${r.data.lat + "," + r.data.lng}</td>
        </tr>
        <tr>
        <td><b>Tanggal</b></td>
        <td>:</td>
        <td>${isoDateToDate(r.data.tanggal)}</td>
        </tr>
        <tr>
        <td><b>Sasaran Giat</b></td>
        <td>:</td>
        <td>${r.data.sasaran_giat}</td>
        </tr>
        </table>
        </div>
        `);
        infowindow.open(map, rengiat);
      });
    })
  }, 1000);
}

function get_lokasiRengiatM() {
	let rgt_m = giat_m.split(",");

  var icon = {
    url : "my/satupeta/giatmasyarakat.png",
    scaledSize: new google.maps.Size(30, 30), // scaled size
  };
  setTimeout(() => {
    rengiat_m = new google.maps.Marker({
      position: { lat : parseFloat(rgt_m[0]), lng : parseFloat(rgt_m[1])},
      map,
      icon : icon,
      // animation: google.maps.Animation.DROP,
    })
    const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow

    map.setCenter({ lat : parseFloat(rgt_m[0]), lng : parseFloat(rgt_m[1])});
    getDataSmNew(global_url_api+'data_collecting/rengiat_masyarakat/'+rgt_m[2]).then(r => {
      google.maps.event.addListener(rengiat_m, 'click', function(){
        map.setZoom(15);
        map.setCenter({ lat : parseFloat(rgt_m[0]), lng : parseFloat(rgt_m[1])});
        infowindow.setContent(`
        <div><p><b>RENGIAT MASYARAKAT</b></p>
        <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
        <table class="w-100">
        <tr>
        <td><b>Jenis Kegiatan</b></td>
        <td>:</td>
        <td>${r.data.jenis_kegiatan}</td>
        </tr>
        <tr>
        <td><b>Penanggung Jawab</b></td>
        <td>:</td>
        <td>${r.data.penanggung_jawab}</td>
        </tr>
        <tr>
        <tr>
        <td><b>Jumlah Peserta</b></td>
        <td>:</td>
        <td>${r.data.jumlah_peserta}</td>
        </tr>
        <tr>
        <td><b>Nama Jalan</b></td>
        <td>:</td>
        <td>${r.data.nama_jalan_s}</td>
        </tr>
        <tr>
        <td><b>Kordinat</b></td>
        <td>:</td>
        <td>${r.data.lat + "," + r.data.lng}</td>
        </tr>
        <tr>
        <td><b>Tanggal</b></td>
        <td>:</td>
        <td>${isoDateToDate(r.data.tanggal)}</td>
        </tr>
        </table>
        </div>
        `);
        infowindow.open(map, rengiat_m);
      });
    })
  }, 1000);
}

function get_lokasiRenOps() {
	let rgops = ops.split(",");

  var icon = {
    url : "my/satupeta/renopspol.png",
    scaledSize: new google.maps.Size(30, 30), // scaled size
  };
  setTimeout(() => {
    renopspol = new google.maps.Marker({
      position: { lat : parseFloat(rgops[0]), lng : parseFloat(rgops[1])},
      map,
      icon : icon,
      // animation: google.maps.Animation.DROP,
    })
    map.setCenter({ lat : parseFloat(rgops[0]), lng : parseFloat(rgops[1])});
    const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow

    getDataSmNew(global_url_api+'data_collecting/renops_pol/'+rgops[2]).then(r => {
      google.maps.event.addListener(renopspol, 'click', function(){
        map.setZoom(15);
        map.setCenter({ lat : parseFloat(rgops[0]), lng : parseFloat(rgops[1])});
        infowindow.setContent(`
        <div><p><b>RENGIAT</b></p>
        <hr style="margin-top:0 !important;margin-bottom:1rem !important;">
        <table class="w-100">
        <tr>
        <td><b>Jenis Kegiatan</b></td>
        <td>:</td>
        <td>${r.data.jenis_kegiatan}</td>
        </tr>
        <tr>
        <td><b>Kategori Kegiatan</b></td>
        <td>:</td>
        <td>${r.data.kategori_kegiatan}</td>
        </tr>
        <tr>
        <td><b>Nama Jalan</b></td>
        <td>:</td>
        <td>${r.data.nama_jalan_s}</td>
        </tr>
        <tr>
        <td><b>Kordinat</b></td>
        <td>:</td>
        <td>${r.data.lat + "," + r.data.lng}</td>
        </tr>
        <tr>
        <td><b>Tanggal</b></td>
        <td>:</td>
        <td>${isoDateToDate(r.data.tanggal)}</td>
        </tr>
        <tr>
        <td><b>Sasaran Giat</b></td>
        <td>:</td>
        <td>${r.data.sasaran_giat}</td>
        </tr>
        </table>
        </div>
        `);
        infowindow.open(map, renopspol);
      });
    })
  }, 1000);
}

function dt_rengiat() {
  var filter = ''
  
	let f_date_from = $('#f_date_from').val()
	let f_date_to = $('#f_date_to').val()
  let da = $('#polda_id').val()
  let res = $('#polres_id').val()
	if (da != '' && da != undefined) {
		filter += `&polda_id=${da}`
	}
	if (res != '' && res != undefined) {
		filter += `&polres_id=${res}`
	}
	if (f_date_from != '' && f_date_from != undefined) {
		filter += `&tgl_start=${f_date_from}`
	}
	if (f_date_to != '' && f_date_to != undefined) {
		filter += `&tgl_end=${f_date_to}`
	}

  // Check if DataTable already exists
  if ($.fn.DataTable.isDataTable('#dt-detail')) {
    // DataTable exists, destroy it
    $('#dt-detail').DataTable().destroy();
  }
  
  console.log(filter)
  $('#dt-detail').DataTable({
    columns: [
        { "data": "jenis_kegiatan" },
        { "data": "kategori_kegiatan" },
        { "data": "nama_jalan_s", render: function(data, type, row) {
          // Check if data is null or empty, then return "-"
          if (data === null || data === "") {
            return "-";
          }
          return data;
        }},
        { "data": "penanggung_jawab" },
        { "data": "kuat_personil" },
        { "data": "tanggal" },
        { "data": "sasaran_giat" }
    ],
    "ajax": {
      "url": './Peta/dt_rengiat',
      "type": "POST",
      "data" : {'filter' : filter, 'bearer' : tber}
    },
  });
}


function logout_sso(username='') {
  window.location.href = `${global_url_ci}Auth/logout_sso/${username}`;
}

$('#polda_id').change(function(){
  let pd_id = `${$(this).val()}`
  if (pd_id != '') {
    $('#f_polres_id').removeClass('d-none')
    $('#polres_id').html('')
    setTimeout(() => {
      get_polres(`polda_id=${pd_id}`).then(function (e) {
        $('#polres_id').append(`
          <option value="">POLRES</option>
        `)        
         e.forEach(f => {
            $('#polres_id').append(`
              <option value="${f.polres_id}">${f.polres}</option>
            `)
          });
          $( '.custom-select2' ).select2( {
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true
          } );
          $('#title_card_kanan').html('')
          $('#title_card_kanan').append($("#polres_id option:selected").text())
      })
    }, 100);
  }else{
    $('#polres_id').html('')
    $('#f_polres_id').addClass('d-none')
  }
  set_all_cctv(pd_id)
  live_marker_cctv(pd_id)
  // prom_get_data_road_event(pd_id).then(function (data) {
  //   list_road_event(data.data, data.total, pd_id)
  // })
  // prom_get_data_gangguan_lalin(pd_id).then(function (data) {
  //   list_gangguan_lalin(data,pd_id)
  // })
  $('#title_card_kanan').html('')
  $('#title_card_kanan').append($("#polda_id option:selected").text())
})

$('#polres_id').change(function(){
  let pd_id = $('#polda_id').val()
  let ps_id = `${$(this).val()}`
  set_all_cctv(pd_id,ps_id)
  live_marker_cctv(pd_id,ps_id)
  // prom_get_data_road_event(pd_id,ps_id).then(function (data) {
  //   list_road_event(data.data, data.total, pd_id,ps_id)
  // })
  // prom_get_data_gangguan_lalin(pd_id,ps_id).then(function (data) {
  //   list_gangguan_lalin(data,pd_id,ps_id)
  // })
  if (ps_id == 'null') {
    $('#title_card_kanan').html('')
    $('#title_card_kanan').append($("#polda_id option:selected").text())
  }else{
    $('#title_card_kanan').html('')
    $('#title_card_kanan').append($("#polres_id option:selected").text())
  }
})

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
