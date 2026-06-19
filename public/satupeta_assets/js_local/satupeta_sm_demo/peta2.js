var map; //variable map
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

let changePolres = false;
let dataPolda = [];
let dataPolres = [];
let selectedPolda = {};
let g_start_filter = '';
let g_end_filter = '';
var user_lat = parseFloat($('#user_lat').val());
var user_lng = parseFloat($('#user_lng').val());
let buttonTextActive = "BATAS PROVINSI";

let t_out_road_safety_batas_kota = null
let t_out_road_safety_batas_kota_cctv = null
let t_out_road_safety_batas_provinsi = null
let t_out_road_safety_batas_provinsi_cctv = null

const styles = {
  default: [
    {
        "featureType": "all",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "color": "#ffffff"
            }
        ]
    },
    {
        "featureType": "all",
        "elementType": "labels.text.stroke",
        "stylers": [
            {
                "color": "#000000"
            },
            {
                "lightness": 13
            }
        ]
    },
    {
        "featureType": "administrative",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#000000"
            }
        ]
    },
    {
        "featureType": "administrative",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#144b53"
            },
            {
                "lightness": 14
            },
            {
                "weight": 1.4
            }
        ]
    },
    {
        "featureType": "landscape",
        "elementType": "all",
        "stylers": [
            {
                "color": "#08304b"
            }
        ]
    },
    {
        "featureType": "poi",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#0c4152"
            },
            {
                "lightness": 5
            }
        ]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#000000"
            }
        ]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#0b434f"
            },
            {
                "lightness": 25
            }
        ]
    },
    {
        "featureType": "road.arterial",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#000000"
            }
        ]
    },
    {
        "featureType": "road.arterial",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#0b3d51"
            },
            {
                "lightness": 16
            }
        ]
    },
    {
        "featureType": "road.local",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#000000"
            }
        ]
    },
    {
        "featureType": "transit",
        "elementType": "all",
        "stylers": [
            {
                "color": "#146474"
            }
        ]
    },
    {
        "featureType": "water",
        "elementType": "all",
        "stylers": [
            {
                "color": "#021019"
            }
        ]
    }
  ],
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

let paramsUrl = new URLSearchParams(window.location.search);
let paramsPolda = paramsUrl.get("polda") ? paramsUrl.get("polda") : null;
let paramsPolres = paramsUrl.get("polres") ? paramsUrl.get("polres") : null;
let paramsPoldaId = paramsUrl.get("polda_id") != "0" ? paramsUrl.get("polda_id") : null;
let paramsPolresId = paramsUrl.get("polres_id") != "0" ? paramsUrl.get("polres_id") : null;

let zoom = 10;
function initMap() {
  map = new google.maps.Map(document.getElementById("mapid"), {
    center: { lat: -7.8014443, lng: 110.3860487},
    zoom: zoom,
    zoomControl: false,
    mapTypeControl: false,
    scaleControl: false,
    streetViewControl: false,
    rotateControl: false,
    fullscreenControl: false,
    styles : styles.default,
  });
  const arn = map.styles.filter(function (x) {
    return x.featureType != 'poi' &&
           x.featureType != 'poi.park' &&
           x.featureType != 'transit' &&
           x.featureType != 'transit.station' &&
           x.featureType != 'transit.line'
  });

  trafficLayer = new google.maps.TrafficLayer();
  trafficLayer.setMap(map);

  map.addListener('drag', function() {
    if (popover != undefined) {
      popover.hide()
    }
  });

  map.addListener('click', function () {
    $('#offcanvasScrolling').offcanvas('hide')
    $('#offcanvasRight').offcanvas('hide')
  })

  const arm = [...arn, ...styles.hiding]
  map.setOptions({ styles: arm });
}

$(function () {
    heatmapDataRawanIrsms = [];
    const menuActiveButton = document.querySelector(".btn-menu-custom small");
    if (menuActiveButton && menuActiveButton.textContent.trim() === "BATAS PROVINSI") {
      const buttonElement = menuActiveButton.closest(".btn-menu-custom");
      buttonElement.classList.add("btn-menu-custom-active");
    }
    
    $("#menu-polda").removeClass("d-none");
    selectedPolda = {
      polda: 'POLDA DI YOGYAKARTA',
      polres: paramsPolres,
      polda_id: 15,
      polres_id: usr_polres_id || paramsPolresId,
    }

    $("#konten-filter").html(`
        <div id="reportrange" key="batas_provinsi_road_safety" class="text-white filter-custom" style="background-color: #000C1E">
            <i class="fa fa-calendar"></i>&nbsp;
            <span></span> <i class="fa fa-caret-down"></i>
        </div>
    `);
    
    filter_range()
    menu_sm_road_safety_policy("batas_provinsi")
    $("#filter-polres").select2();
    if (buttonTextActive == 'BATAS KOTA') {
      $("#f_polres").removeClass('d-none')
    }
});

function menu_sm_road_safety_policy(val, fillPolda) {
  cleartimeout();
  document.querySelectorAll(".btn-menu-custom").forEach(button => {
    button.classList.remove("btn-menu-custom-active");
  });

  // Menentukan tombol yang sesuai berdasarkan val
  let catDateRange = "";

  switch (val) {
    case "batas_provinsi":
      filter_polres(15)
      if (changePolres) {
        selectedPolda = {
          ...selectedPolda,
          polres: paramsPolres,
          polda_id: usr_polda_id || paramsPoldaId,
        };
        changePolres = false;
      }

      clearRawanHeat()
      heatmapDataRawanIrsms = [];
      catDateRange = val + "_road_safety"
      buttonTextActive = "BATAS PROVINSI";
      $("#f_polres").addClass('d-none')
      dash_road_safety_perbatasan_provinsi({ start_date: g_start_filter.format('YYYY-MM-DD'), end_date: g_end_filter.format('YYYY-MM-DD') });
      break;
    case "batas_kota":
      clearRawanHeat()
      heatmapDataRawanIrsms = [];
      catDateRange = val + "_road_safety"
      buttonTextActive = "BATAS KOTA";
      $("#f_polres").removeClass('d-none')
      dash_road_safety_perbatasan_kota({ start_date: g_start_filter.format('YYYY-MM-DD'), end_date: g_end_filter.format('YYYY-MM-DD') });
      break;
    default:
      alert("js belum dibuat!");
      return;
  }

  // Aktifkan tombol yang sesuai
  document.querySelectorAll(".btn-menu-custom small").forEach(small => {
    if (small.textContent.trim() === buttonTextActive) {
      small.closest(".btn-menu-custom").classList.add("btn-menu-custom-active");
    }
  });

  // Atur filter tampilan
  $("#konten-filter").html(`
    <div id="reportrange" key="${catDateRange}" class="text-white filter-custom" style="background-color: #000C1E">
        <i class="fa fa-calendar"></i>&nbsp;
        <span></span> <i class="fa fa-caret-down"></i>
    </div>
  `);

  filter_range();
}

let heatmap_irsms;
let heatmapDataRawanIrsms = [];
let infowindowHeatRawanIrsms;
let selectedRawanIrsms;
let selectedBtnRawanIrsms;
let geocoder 

function rawan_heat(key, btnkey, type) {
  clearRawanHeat();
  let btnRawan = $(`.btnrawan${type}-${btnkey}`);
  if (btnRawan.hasClass("active")) {
    selectedBtnRawanIrsms = null
    heatmapDataRawanIrsms = [];
    btnRawan.removeClass("active");
  } else {
    if (type == 'laka') {
      getDataSmNew(
        `${global_url_api}smart_dashboard/main_dashboard/accident/prevention/${key}`
      ).then((data) => {
        heatmapDataRawanIrsms = data.data.map((item) => ({
          location: new google.maps.LatLng(
            parseFloat(item.koordinat_gps_lintang),
            parseFloat(item.koordinat_gps_bujur)
          ),
          weight: parseInt(item.korban),
          tingkat_kecelakaan: item.tingkat_kecelakaan,
          kelas_jalan: item.kelas_jalan,
        }));
  
        // Buat objek heatmap
        heatmap_irsms = new google.maps.visualization.HeatmapLayer({
          data: heatmapDataRawanIrsms.map((item) => item.location),
          map: map,
          radius: 20,
        });
  
        // Tambahkan event listener untuk menangani klik
        setupInfoWindowForHeatmap('laka');
      });
    }else{
      getDataSmNew(
        `${global_url_api}smart_dashboard/main_dashboard/accident/prevention/${key}`
      ).then((data) => {
        heatmapDataRawanIrsms = data.data.map((item) => ({
          location: new google.maps.LatLng(
            parseFloat(item.lat),
            parseFloat(item.lng)
          ),
          weight: parseInt(item.total),
          event_name: item.event_name
        }));
  
        // Buat objek heatmap
        heatmap_irsms = new google.maps.visualization.HeatmapLayer({
          data: heatmapDataRawanIrsms.map((item) => item.location),
          map: map,
          radius: 20,
        });
  
        // Tambahkan event listener untuk menangani klik
        setupInfoWindowForHeatmap('langgar');
      });
    }

    $(`.btnrawanlaka-${selectedBtnRawanIrsms}`).removeClass("active");
    $(`.btnrawanlanggar-${selectedBtnRawanIrsms}`).removeClass("active");
    selectedBtnRawanIrsms = btnkey;
    btnRawan.addClass("active");
  }
}

function clearRawanHeat() {
  if (heatmap_irsms) {
    heatmap_irsms.setMap(null);
  }
}

function setupInfoWindowForHeatmap(type) {
  if (!infowindowHeatRawanIrsms) {
    infowindowHeatRawanIrsms = new google.maps.InfoWindow();
  }

  map.addListener("click", (event) => {
    const clickedLatLng = event.latLng;
    const nearestPoint = getNearestHeatmapPoint(clickedLatLng);

    const threshold = 0.005; // Menyesuaikan jarak threshold untuk klik
    if (nearestPoint && getDistance(clickedLatLng, nearestPoint.location) < threshold) {
      getStreetName(nearestPoint.location, (streetName) => {
        if (type=='laka') {
          infowindowHeatRawanIrsms.setContent(`
            <div>
            <table>
              <tbody>
                <tr>
                  <td>Korban</td>
                  <td>:</td>
                  <td>${nearestPoint.weight}</rd>
                </tr>
                <tr>
                  <td>Tingkat Kecelakaan</td>
                  <td>:</td>
                  <td>${nearestPoint.tingkat_kecelakaan}</td>
                </tr>
                <tr>
                  <td>Kelas Jalan</td>
                  <td>:</td>
                  <td>${nearestPoint.kelas_jalan}</td>
                </tr>
                <tr>
                  <td>Nama Jalan</td>
                  <td>:</td>
                  <td>${streetName}</td>
                </tr>
              <tbody>
            </table>
          </div>
          `);
        }else {
          infowindowHeatRawanIrsms.setContent(`
            <div>
            <table>
              <tbody>
                <tr>
                  <td>Total</td>
                  <td>:</td>
                  <td>${nearestPoint.weight}</rd>
                </tr>
                <tr>
                  <td>Nama Kejadian</td>
                  <td>:</td>
                  <td>${nearestPoint.event_name}</td>
                </tr>
                <tr>
                  <td>Nama Jalan</td>
                  <td>:</td>
                  <td>${streetName}</td>
                </tr>
              <tbody>
            </table>
          </div>
          `);
        }
        infowindowHeatRawanIrsms.setPosition(nearestPoint.location);
        infowindowHeatRawanIrsms.open(map);
      });
    } else {
      infowindowHeatRawanIrsms.close();
    }
  });
}

// Fungsi untuk mendapatkan titik heatmap terdekat dari lokasi klik
function getNearestHeatmapPoint(clickedLatLng) {
  let nearestPoint = null;
  let minDistance = Number.MAX_VALUE;

  heatmapDataRawanIrsms.forEach((point) => {
    const distance = getDistance(clickedLatLng, point.location);
    if (distance < minDistance) {
      minDistance = distance;
      nearestPoint = point;
    }
  });

  return nearestPoint;
}

// Fungsi untuk menghitung jarak antara dua titik lat/lng
function getDistance(latLng1, latLng2) {
  const latDiff = latLng1.lat() - latLng2.lat();
  const lngDiff = latLng1.lng() - latLng2.lng();
  return Math.sqrt(latDiff * latDiff + lngDiff * lngDiff);
}

// Fungsi untuk mendapatkan nama jalan menggunakan Geocoder
function getStreetName(latLng, callback) {
  geocoder = new google.maps.Geocoder(); // Tambahkan Geocoder
  geocoder.geocode({ location: latLng }, (results, status) => {
    if (status === "OK" && results[0]) {
      let streetName = "-";
      for (let i = 0; i < results[0].address_components.length; i++) {
        const component = results[0].address_components[i];
        if (component.types.includes("route")) {
          streetName = component.long_name;
          break;
        }
      }
      callback(streetName);
    } else {
      callback("-");
    }
  });
}

let markers_info_kejadian
let selectedInfoKejadian
let selectedBtnInfoKejadian
function marker_info_kejadian(url,id,key,btnKey) {
  clearMarkerInfoKejadian()
  let row = $(`.btnkejadian-${btnKey}`);
  if (row.hasClass("active")) {
    selectedInfoKejadian = null;
    selectedBtnInfoKejadian = null;
    markers_info_kejadian = null;
    row.removeClass("active");
  } else {
    getDataSmNew(
      url
    ).then((data) => {
      let dt = data.data[id-1]
      const infowindow = new google.maps.InfoWindow({
        content: `
        <table>
          <tbody>
            <tr>
              <td>Nama Jalan</td>
              <td>:</td>
              <td>${dt.nama_jalan}</td>
            </tr>
            <tr>
              <td>Total Kejadian</td>
              <td>:</td>
              <td>${dt.total}</td>
            </tr>
            <tr>
              <td>Deskripsi</td>
              <td>:</td>
              <td>${dt.deskripsi}</td>
            </tr>
            <tr>
              <td>Rekomendasi</td>
              <td>:</td>
              <td>${dt.rekomendasi}</td>
            </tr>
          </tbody>
        </table>`,
      });
  
      markers_info_kejadian = new google.maps.Marker({
        position: { lat : parseFloat(dt.lat), lng : parseFloat(dt.lng)},
        map,
      });
      
    
      markers_info_kejadian.addListener("click", () => {
        infowindow.open({
          anchor: markers_info_kejadian,
          map,
        });
      });
    })

    selectedInfoKejadian = key;
    selectedBtnInfoKejadian = btnKey;
    $("#info_kejadian tr").removeClass("active");
    row.addClass("active");
  }
  
}

function clearMarkerInfoKejadian() {
  if (markers_info_kejadian) {
    markers_info_kejadian.setMap(null);
  }
}

let vcr_stream_in = [];
let vcr_stream_out = [];
let directionsDisplayAnalisaWaktu;
let selectedBtnAnalisaWaktu;
function rute_analisa_waktu(start_coordinates, end_coordinates, btnkey, type) {
  clear_rute_analisa_waktu()
  let btnAnalisa = $(`.btn${type}-${btnkey}`);
  if (btnAnalisa.hasClass("active")) {
      selectedBtnAnalisaWaktu = null
      btnAnalisa.removeClass("active");
  } else {
    let startLatLng = start_coordinates.split(',').map(coord => parseFloat(coord.trim()));
    let endLatLng = end_coordinates.split(',').map(coord => parseFloat(coord.trim()));

    // Pastikan Google Maps sudah terinisialisasi
    if (typeof google === "undefined" || !google.maps) {
        console.error("Google Maps API is not loaded.");
        return;
    }

    // Inisialisasi Directions Renderer dan Directions Service
    directionsDisplayAnalisaWaktu = new google.maps.DirectionsRenderer({
        draggable: true,
        polylineOptions: {
            strokeColor: "blue",
            strokeWeight: 10
        }
    });

    let directionsServiceAnalisaWaktu = new google.maps.DirectionsService();

    // Set rute pada peta
    directionsDisplayAnalisaWaktu.setMap(map);

    let requestObjectVital = {
        origin: new google.maps.LatLng(startLatLng[0], startLatLng[1]),
        destination: new google.maps.LatLng(endLatLng[0], endLatLng[1]),
        travelMode: google.maps.TravelMode.DRIVING
    };

    // Panggil Google Maps Directions API
    directionsServiceAnalisaWaktu.route(requestObjectVital, function(result, status) {
        if (status === google.maps.DirectionsStatus.OK) {
            directionsDisplayAnalisaWaktu.setDirections(result);
        } else {
            console.error("Directions request failed due to " + status);
        }
    });
    
    $(`.btnvital-${selectedBtnAnalisaWaktu}`).removeClass("active");
    $(`.btnpublic-${selectedBtnAnalisaWaktu}`).removeClass("active");
    selectedBtnAnalisaWaktu = btnkey
    btnAnalisa.addClass("active");
  }
}

function active_rute_analisa_waktu(btnkey, type) {
  let row = $(`.btn${type}-${btnkey}`);
  if (row.hasClass("active")) {
      selectedBtnAnalisaWaktu = null
      row.removeClass("active");
  } else {
    selectedBtnAnalisaWaktu = btnkey
    $(`#t_list_${btnkey}_analisa_waktu`).removeClass("active");
    row.addClass("active");
  }
}

function clear_rute_analisa_waktu() {
  if (directionsDisplayAnalisaWaktu) {
      directionsDisplayAnalisaWaktu.setMap(null);
      directionsDisplayAnalisaWaktu = null;
  }
}

let swiperIN;
let swiperOUT;
let soundCongestionAlert;
let alertInterval;
let isPlaying = false; // Tambahkan flag untuk menghindari double play
let isAlertTriggered = false; // Tambahkan variabel global

function checkPeningkatanAlert(start_date, end_date, nama_cctv = null) {
  let filters_prediksi = {
    start_date: start_date,
    end_date: end_date,
  };
  
	// Menggabungkan objek dengan validasi
	if (selectedPolda?.polda) {
		filters_prediksi = {
			...filters_prediksi,
			polda: selectedPolda.polda,
			polda_id: selectedPolda.polda_id,
		};
	} else if (usr_polda || paramsPolda) {
		filters_prediksi = {
			...filters_prediksi,
			polda: usr_polda ? usr_polda : paramsPolda,
			polda_id: usr_polda_id ? usr_polda_id : paramsPoldaId,
		};
	}
	if (selectedPolda?.polres) {
		filters_prediksi = {
			...filters_prediksi,
			polres: selectedPolda.polres,
			polres_id: selectedPolda.polres_id,
		};
	} else if (usr_polres || paramsPolres) {
		filters_prediksi = {
			...filters_prediksi,
			polres: usr_polres ? usr_polres : paramsPolres,
			polres_id: usr_polres_id ? usr_polres_id : paramsPolresId,
		};
	}

  if (nama_cctv) {
    filters_prediksi_prov = { ...filters_prediksi, nama_cctv };
    // filters_prediksi_kota = { ...filters_prediksi_kota, nama_cctv };
    // filters_prediksi_dalam_kota = { ...filters_prediksi_dalam_kota, nama_cctv };
  }

  let filter_prediksi_prov = filter_obj(filters_prediksi);
  getDataSmNew(
    `${global_url_api}smart_dashboard/main_dashboard/traffic/congestion_alert?` +
      filter_prediksi_prov + "&category=BATAS PROVINSI"
  ).then((data) => {
    if (data.data) {
      const congestionAlert = data.data.congestion_alert;
      if (congestionAlert) {
        if (!nama_cctv && !isAlertTriggered) {
          isAlertTriggered = true; // Cegah suara berbunyi berulang
          playAlertPrediksi();
        }
        $("#button-provinsi").addClass("btn-alert");
        $("#button-provinsi").find(".alert-icon").removeClass("d-none");
      } else {
        $("#button-provinsi").removeClass("btn-alert");
        $("#button-provinsi").find(".alert-icon").addClass("d-none");
      }
    }
  });

  let filter_prediksi_kota = filter_obj(filters_prediksi);
  getDataSmNew(
    `${global_url_api}smart_dashboard/main_dashboard/traffic/congestion_alert?` +
      filter_prediksi_kota + "&category=BATAS KOTA"
  ).then((data) => {
    if (data.data) {
      const congestionAlert = data.data.congestion_alert;
      if (congestionAlert) {
        if (!nama_cctv && !isAlertTriggered) {
          isAlertTriggered = true; // Cegah suara berbunyi berulang
          playAlertPrediksi();
        }
        $("#button-kota").addClass("btn-alert");
        $("#button-kota").find(".alert-icon").removeClass("d-none");
      } else {
        $("#button-kota").removeClass("btn-alert");
        $("#button-kota").find(".alert-icon").addClass("d-none");
      }
    }
  });

  let filter_prediksi_dalam_kota = filter_obj(filters_prediksi);
  getDataSmNew(
    `${global_url_api}smart_dashboard/main_dashboard/traffic/congestion_alert?` +
      filter_prediksi_dalam_kota + "&category=DALAM KOTA"
  ).then((data) => {
    if (data.data) {
      const congestionAlert = data.data.congestion_alert;
      if (congestionAlert) {
        if (!nama_cctv && !isAlertTriggered) {
          isAlertTriggered = true; // Cegah suara berbunyi berulang
          playAlertPrediksi();
        }
        $("#button-dalam-kota").addClass("btn-alert");
        $("#button-dalam-kota").find(".alert-icon").removeClass("d-none");
      } else {
        $("#button-dalam-kota").removeClass("btn-alert");
        $("#button-dalam-kota").find(".alert-icon").addClass("d-none");
      }
    }
  });
}

function playAlertPrediksi() {
  var audio = document.getElementById("alert_congestion");

  if (!isPlaying) {
    isPlaying = true;

    audio.load();
    audio.play().catch((error) => {
      console.warn("Gagal memutar audio:", error);
    });

    // Hentikan audio setelah 9 detik
    setTimeout(() => {
      audio.pause();
      audio.currentTime = 0;
      isPlaying = false; // Reset flag setelah audio selesai
    }, 9000);
  }
}

function filter_polres(valPolda, valPolres = null) {
  let filPolda;
  if (valPolda) {
    filPolda = `polda_id=${valPolda}`;
  }

  if (valPolres) {
    filPolda += `&polres=${valPolres}`;
  }

  // Reset data and clear dropdown options
  dataPolres = [];
  let select = document.getElementById("filter-polres");
  select.innerHTML = '<option value="">Pilih Polres</option>'; // Reset options
  get_polres(filPolda).then((data) => {
    dataPolres = data; // Store data for lookup

    let groupedPolres = {}; // Group Polres to avoid duplicates
    data.forEach(row => {
      groupedPolres[row.polres_id] = row; // Store object instead of just ID
    });

    Object.values(groupedPolres).forEach(row => {
      let option = document.createElement("option");
      option.value = row.polres_id;
      option.textContent = row.polres;
      select.appendChild(option);
    });
  }).catch((error) => {
      console.error("Error mengambil data Polres:", error);
  });
}

function filter_range(detail = '') 
{
    var start_filter
    var end_filter
    if (g_start_filter != '') {
      start_filter = g_start_filter
    }else {
      start_filter = moment().subtract(0, 'days');
    }
  
    if (g_end_filter != '') {
      end_filter = g_end_filter
    }else {
      end_filter = moment();
    }

    g_start_filter = start_filter
    g_end_filter = end_filter
    function cb_filter(start_filter, end_filter) {
        $('#reportrange span').html(start_filter.format('MMMM D, YYYY') + ' - ' + end_filter.format('MMMM D, YYYY'));
    }
  
    $('#reportrange').daterangepicker({
        startDate: start_filter,
        endDate: end_filter,
        ranges: {
            'Hari ini': [moment(), moment()],
            'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '7 hari terakhir': [moment().subtract(6, 'days'), moment()],
            '30 hari terakhir': [moment().subtract(29, 'days'), moment()],
            'Bulan ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb_filter);
  
      cb_filter(start_filter, end_filter);
      $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
        g_start_filter = picker.startDate
        g_end_filter = picker.endDate
        if (detail != '') {
            $('#title-konten-tengah').html('SMART CITY YOGYAKARTA');
            $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Dalam Kota')
            $('#konten-kiri').html('');
            cleartimeout()
            dash_traffic_dalam_kota_detail(detail.replace("_"," "),{start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
        }else {
          switch ($('#reportrange').attr('key')) {
            case 'batas_provinsi_road_safety':
              $('#konten-kiri').html('');
              $("#bottom-content").html('');
              $('#konten-kanan').html('');
              cleartimeout()
              dash_road_safety_perbatasan_provinsi({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;
            case 'batas_kota_road_safety':
              $('#konten-kiri').html('');
              $("#bottom-content").html('');
              $('#konten-kanan').html('');
              cleartimeout()
              dash_road_safety_perbatasan_kota({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;
            case 'dalam_kota_road_safety':
              $('#konten-kiri').html('');
              $("#bottom-content").html('');
              $('#konten-kanan').html('');
              cleartimeout()
              dash_road_safety_dalam_kota({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;
        }
        }
    });
}

function date_time_filter(filter={}) {
    var today = new Date();
    var today2 = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    var yyyy = today.getFullYear();
    let c_date = yyyy + '-' + mm + '-' + dd
    let stamp_start = today.setTime(today.getTime() - 10*60*1000)
    let s_start = String(stamp_start).slice(0, -3);
    let stamp_end = today2.setTime(today2.getTime())
    let s_end = String(stamp_end).slice(0, -3);
    let start_date
    let end_date
    if (filter.start_date != undefined) {
      if (filter.start_date != '') {
        start_date =filter.start_date
      }else{
        start_date = c_date
      }
    }else{
      start_date = c_date
    }
  
    if (filter.end_date != undefined) {
      if (filter.end_date != '') {
        end_date =filter.end_date
      }else{
        end_date = c_date
      }
    }else{
      end_date = c_date
    }
  
    return res ={
      'date_now' : c_date,
      'stamp_start' : s_start,
      'stamp_end' : s_end,
      'start_date' : start_date,
      'end_date' : end_date
    }
}
  
function cleartimeout() {
    clearTimeout(t_out_road_safety_batas_kota)
    clearTimeout(t_out_road_safety_batas_kota_cctv)
    clearTimeout(t_out_road_safety_batas_provinsi)
    clearTimeout(t_out_road_safety_batas_provinsi_cctv)
}

function filter_obj(filter = {}) {
  let fltr = '';
  if (Object.keys(filter).length !== 0) {
      Object.keys(filter).forEach((key, index) => {
          fltr += `${index === 0 ? '' : '&'}${key}=${filter[key]}`;
      });
  }
  return fltr;
}

function autoScroll() {
    const scrollPanes = document.querySelectorAll(".scroll-pane"); // Ambil semua elemen dengan class yang sama
      scrollPanes.forEach((scrollPane) => {
          let step = 30;
          let isScrolling = true;
          let userScrollTimeout;
  
          function smoothScroll() {
              if (!isScrolling) return;
  
              if (scrollPane.scrollTop + scrollPane.clientHeight >= scrollPane.scrollHeight) {
                  scrollPane.scrollTop = 0;
              } else {
                  scrollPane.scrollTop += step;
              }
  
              requestAnimationFrame(smoothScroll);
          }
  
          requestAnimationFrame(smoothScroll);
  
          scrollPane.addEventListener("mouseenter", () => {
              isScrolling = false;
          });
  
          scrollPane.addEventListener("mouseleave", () => {
              isScrolling = true;
              requestAnimationFrame(smoothScroll);
          });
  
          scrollPane.addEventListener("scroll", () => {
              isScrolling = false;
              clearTimeout(userScrollTimeout);
              userScrollTimeout = setTimeout(() => {
                  isScrolling = true;
                  requestAnimationFrame(smoothScroll);
              }, 3000);
          });
    });
}

function triggerPolres(selectElement) {
  changePolres = true;
  let polresId = selectElement.value; // Get selected value
  if (polresId) {
    let selectedData = dataPolres.find(row => row.polres_id === polresId); // Find full object
    if (selectedData) {
      selectedPolda = {
        ...selectedPolda,
        polres: selectedData.polres,
        polres_id: selectedData.polres_id,
      };
    }
  } else {
    selectedPolda = {
      ...selectedPolda,
      polres: '',
      polres_id: '',
    };
  }

  menu_sm_road_safety_policy(buttonTextActive.replace(/\s+/g, '_').toLowerCase());
}