var map; //variable map
var label = '';
let g_start_filter = '';
let g_end_filter = '';
var layers = [];
var geojsonFeatures = [];
let labelOverlays = [];
let heatmap_lalin
let heatmapDataLalin = [];
let heatmap_enforcement_lalin
let heatmapDataEnforcementLalin = [];
let heatmap_enforcement_perda
let heatmapDataEnforcementPerda = [];

/**
 * createHeatmapCircles - Replacement for deprecated google.maps.visualization.HeatmapLayer
 * Uses google.maps.Circle overlays to create a heatmap-like visualization.
 * Returns an object with setMap(null|map) for compatibility with existing code.
 *
 * @param {google.maps.Map} mapObj - The Google Map instance
 * @param {Array<{location: google.maps.LatLng, weight: number}>} data - Heatmap data points
 * @param {number} baseRadiusMeters - Base radius in meters (default 300)
 * @returns {{setMap: function, circles: google.maps.Circle[]}}
 */
function createHeatmapCircles(mapObj, data, baseRadiusMeters = 300) {
  const circles = [];
  if (!data || data.length === 0) {
    return { setMap: function() {}, circles: [] };
  }

  let maxWeight = 0;
  data.forEach(item => {
    if (item.weight > maxWeight) maxWeight = item.weight;
  });
  if (maxWeight === 0) maxWeight = 1;

  data.forEach(item => {
    const t = item.weight / maxWeight; // normalized 0..1

    // Color gradient: blue(cold) -> cyan -> green -> yellow -> red(hot)
    let r, g, b;
    if (t < 0.25) {
      r = 0; g = Math.round(t * 4 * 255); b = 255;
    } else if (t < 0.5) {
      r = 0; g = 255; b = Math.round((1 - (t - 0.25) * 4) * 255);
    } else if (t < 0.75) {
      r = Math.round((t - 0.5) * 4 * 255); g = 255; b = 0;
    } else {
      r = 255; g = Math.round((1 - (t - 0.75) * 4) * 255); b = 0;
    }

    const circle = new google.maps.Circle({
      center: item.location,
      radius: baseRadiusMeters * (0.5 + t * 1),
      map: mapObj,
      fillColor: `rgb(${r},${g},${b})`,
      fillOpacity: 0.25 + t * 0.35,
      strokeColor: `rgba(${r},${g},${b},0.3)`,
      strokeOpacity: 0.3,
      strokeWeight: 1,
    });
    circles.push(circle);
  });

  return {
    setMap: function(m) {
      circles.forEach(c => c.setMap(m));
    },
    circles: circles
  };
}

let t_out_traffic_all_cctv = null
let t_out_traffic_dalam_kota = null
let t_out_mobile_officer = null
let t_out_traffic_crowd_counting = null
let t_out_traffic_crowd_counting_cctv_multiple = null;
let t_out_traffic_batas_kota = null
let t_out_traffic_batas_kota_cctv = null
let t_out_traffic_batas_provinsi = null
let t_out_traffic_batas_provinsi_cctv = null
let t_out_enforcement_lalin = null
let t_out_enforcement_perda = null
let t_out_traffic_dalam_kota_detail = null

let trafficLayer
let zoom;
var geojsonFiles = [];

// document.addEventListener('DOMContentLoaded', function() {
//   geojsonFiles = [
//     { "url": `${global_url_ci}my/geo/diy/yogyakarta.geojson`, "color": "gray" },
//     { "url": `${global_url_ci}my/geo/diy/bantul.geojson`, "color": "gray" },
// 		{ "url":`${global_url_ci}my/geo/diy/gunung_kidul.geojson`,"color":"gray"},
// 		{ "url":`${global_url_ci}my/geo/diy/kulonprogo.geojson`,"color":"gray"},
// 		{ "url":`${global_url_ci}my/geo/diy/sleman.geojson`,"color":"gray"}
// ];
// });

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


let paramsUrl = new URLSearchParams(window.location.search);
let paramsPolda = paramsUrl.get("polda") != "" ? paramsUrl.get("polda") : null;
let paramsPolres = paramsUrl.get("polres") != "" ? paramsUrl.get("polres") : null;
let paramsPoldaId = paramsUrl.get("polda_id") != "0" ? paramsUrl.get("polda_id") : null;
let paramsPolresId = paramsUrl.get("polres_id") != "0" ? paramsUrl.get("polres_id") : null;

var user_lat = parseFloat($('#user_lat').val());
var user_lng = parseFloat($('#user_lng').val());

async function initMap() {
  const { Map } = await google.maps.importLibrary("maps");
  zoom = parseInt(global_zoom_map);
  map = new Map(document.getElementById("mapid"), {
    center: { lat: parseFloat(global_lat_center), lng: parseFloat(global_lng_center) },
    zoom: zoom,
    zoomControl: false,
    mapTypeControl: false,
    scaleControl: false,
    streetViewControl: false,
    rotateControl: false,
    fullscreenControl: false,
    // styles : styles.default,
    mapId: "ab9a356c16a4fe09bea174f2"
  });

  const arn = styles.default.filter(function (x) {
    return x.featureType != 'poi' &&
           x.featureType != 'poi.park' &&
           x.featureType != 'transit' &&
           x.featureType != 'transit.station' &&
           x.featureType != 'transit.line'
  });

  const arm = [...arn, ...styles.hiding];
  map.setOptions({ styles: arm });
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
var urlParameter = {
  // u : u,
};

$(function () {
  $('.menu-custom').click(function () {
      $('.menu-custom').removeClass('menu-custom-active');

      $(this).addClass('menu-custom-active');
  });
  $('.menu-custom-dash').click(function () {
      $('.menu-custom-dash').removeClass('menu-custom-active-dash');

      $(this).addClass('menu-custom-active-dash');
  });
  // setInterval(() => {
  //     cek_sso(`${id_unique}`)
  //   }, 3000);
  const currentURL = window.location.href;
  const urlpath = new URL(currentURL);
  const path = urlpath.pathname;
  const pathSegments = path.split('/');
  const lastSegment = pathSegments[pathSegments.length - 1];

  setTimeout(() => {
    // $('#title-konten-tengah').html('DATA PELANGGARAN');
    // dash_pelanggaran()
    // $('#legend-1').html('Ringan');
    // $('#legend-2').html('Sedang');
    // $('#legend-3').html('Berat');
    switch (lastSegment) {
      case 'sm_traffic':
        map.setZoom(parseInt(global_zoom_map));
        map.setCenter({ lat: parseFloat(global_lat_center), lng: parseFloat(global_lng_center) });
        $("#wadah_list_cctv").addClass("d-none");
        $('#title-konten-tengah').html(`${global_app_name}`);
        $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Perbatasan Provinsi')

        if (paramsPolres || paramsPolresId) {
          $("#m_all_cctv").addClass("d-none");
          $("#m_perbatasan_provinsi").addClass("d-none");
          $("#m_dalam_kota").addClass("d-none");
          $("#menu_traffic").removeClass("w-75").addClass("w-25");
          $("#perbatasanKotaActive").attr("fill", "#0C8CE9");
          dash_traffic_perbatasan_kota()
        } else {
          $("#allCctvActive").attr("fill", "#0C8CE9");
          dash_traffic_all_cctv()
        }

        $("#custom-legend").html(`
        <div class="position-absolute ms-3 p-1 rounded" id="t-legend" style="z-index:1;width:auto;${paramsPolres == null ?'bottom:35%':'bottom:5%'};background: #000C1E4D;">
          <span class="d-flex flex-column align-items-start">
            <div>
              <img src="${global_url_ci}my/logo/cctv_objek_vital.png" width="25" alt="">
              <span class="text-dark" style="font-weight:600;">
                CCTV Objek Vital
                <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend_objek_vital'></span>
              </span>
            </div>
            ${paramsPolres == null ? `
              <div>
                <img src="${global_url_ci}my/logo/cctv_batas_provinsi.png" width="25" alt="">
                <span class="text-dark" style="font-weight:600;">
                  CCTV Batas Provinsi
                  <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend_batas_provinsi'></span>
                </span>
              </div>` : ''}
            <div>
              <img src="${global_url_ci}my/logo/cctv_batas_kota.png" width="25" alt="">
              <span class="text-dark" style="font-weight:600;">
                CCTV Batas Kota
                <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend_batas_kota'></span>
              </span>
            </div>
            <div>
              <img src="${global_url_ci}my/logo/cctv_dalam_kota.png" width="25" alt="">
              <span class="text-dark" style="font-weight:600;">
                CCTV Dalam Kota
                <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend_dalam_kota'></span>
              </span>
            </div>
          </span>
        </div>
          <div class="position-absolute bg-white me-3 rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right:0;${paramsPolres == null ?'bottom:35%':'bottom:5%'}">
            <h3><i class="fa fa-undo"></i></h3>
          </div>
        `);
        $("#konten-filter").html(`
          <div id="reportrange" key="all_cctv" class="text-white filter-custom" style="background-color: #000C1E">
              <i class="fa fa-calendar"></i>&nbsp;
              <span></span> <i class="fa fa-caret-down"></i>
          </div>
        `);

        filter_range()
        break;

      case 'sm_officer':
        clear_mobil()
        clear_petugas()
        $('#title-konten-tengah').html(`${global_app_name}`);
        $('#subtitle-konten-tengah').html('Smart Officer')
        dash_mobile_officer()
        $('#custom-legend').html(`
          <div class="position-absolute bg-white rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right: 17.7%;bottom: 5%;">
              <h3><i class="fa fa-undo"></i></h3>
          </div>
        `)
        $('#konten-filter').html(`
          <div id="reportrange" key="officer" class="bg-primary text-white filter-custom">
              <i class="fa fa-calendar"></i>&nbsp;
              <span></span> <i class="fa fa-caret-down"></i>
          </div>
        `)
        filter_range()
        break;

      case 'sm_security':
          $('#title-konten-tengah').html(`${global_app_name}`);
          $('#subtitle-konten-tengah').html('Pengamanan Object Vital');
          dash_traffic_crowd_counting()
          dash_cctv_objek_vital()
          $('#custom-legend').html(`
            <div class="position-absolute ms-3 p-1 rounded" id="t-legend" style="z-index:1;width:auto;bottom:5%;background: #000C1E4D;">
              <span class="d-flex align-items-center"><img src="${global_url_ci}/my/logo/cctv_objek_vital.png" width="35" alt=""> <span class="text-dark" style="font-weight:600;">CCTV Objek Vital <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend'></span></span></span>
            </div>
            <div class="position-absolute bg-white rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right: 17.7%;bottom: 5%;">
              <h3><i class="fa fa-undo"></i></h3>
            </div>
          `)
          $('#konten-filter').html(`
            ${(paramsPolres || paramsPolresId) ?  '' : `
              <div class="mb-2">
                  <select class="form-select" id="f-polres" onchange="filter_data_polres(this,'security')">
                    <option value=''>ALL POLRES</option>
                  </select>
              </div>`}
            <div id="reportrange" key="crowd_counting" class="bg-primary text-white filter-custom">
                <i class="fa fa-calendar"></i>&nbsp;
                <span></span> <i class="fa fa-caret-down"></i>
            </div>
          `)
          get_polres(`polda_id=${global_polda_id}`).then((e) => {
            e.forEach(data => {
              $('#f-polres').append(`
                <option value="${data.polres},${data.polres_id}">${data.polres}</option>
              `)
            });
          })
          filter_range()
          break;

      case 'sm_enforcement':
        $('#f-data-enforcement').html(`
          <input class="form-control" type="date" onchange="f_data_enforcement(event,'lalin')" value="${date_time_filter().date_now}" name="" style="width:15rem;">
        `)
        // $('#konten-kanan').addClass('d-none')
        $('#title-konten-tengah').html(`${global_app_name}`);
        $('#subtitle-konten-tengah').html('Smart Enforcement - Pelanggaran Lalin')
        dash_enforcement_lalin()
        $('#custom-legend').html(`
          <div class="position-absolute bg-white rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right: 17.7%;bottom: 5%;">
            <h3><i class="fa fa-undo"></i></h3>
          </div>
        `)
        $('#konten-filter').html(`
          ${paramsPolres == null ? `
            <div class="mb-2">
                <select class="form-select" id="f-polres" onchange="filter_data_polres(this,'enforcement_lalin')">
                  <option value=''>ALL POLRES</option>
                </select>
            </div>` : ''}
          <div id="reportrange" key="pel_lalin" class="bg-primary text-white filter-custom">
              <i class="fa fa-calendar"></i>&nbsp;
              <span></span> <i class="fa fa-caret-down"></i>
          </div>
        `)
        get_polres(`polda_id=${global_polda_id}`).then((e) => {
          e.forEach(data => {
            $('#f-polres').append(`
              <option value="${data.polres},${data.polres_id}">${data.polres}</option>
            `)
          });
        })
        filter_range()
        break;
      default:
        break;
    }
  }, 100);


});

function add_geo(geojsonFiles) {
  geojsonFiles.forEach(function (file, index) {
    fetch(file.url)
      .then(response =>  response.json())
      .then(data => {
        let layer = new google.maps.Data({ map: map });
        layer.setStyle({
          fillColor: file.color,
          fillOpacity: 0.1,
          strokeWeight: 1
        });

        layer.loadGeoJson(geojsonFiles[index].url, null, function (features) {
          // Show label for each feature after loading GeoJSON
          // features.forEach(function (feature) {
          //   let label = feature.getProperty('Kecamatan');
          //   let position = getFeatureCenter(feature);
          //   showLabel(label, position);
          // });
        });

        layer.addGeoJson(data);

        layers.push(layer);

        geojsonFeatures.push(data)
        // Membuat OverlayView untuk menampilkan label
        function LabelOverlay(map, position, text, labelId) {
          this.position = position;
          this.text = text;
          this.labelId = labelId;
          this.setMap(map);
        }

        LabelOverlay.prototype = new google.maps.OverlayView();

        LabelOverlay.prototype.onAdd = function () {
          var div = document.createElement('div');
          div.className = 'label-map';
          div.innerHTML = this.text;
          div.id = this.labelId;
          this.div = div;
          var panes = this.getPanes();
          panes.overlayLayer.appendChild(div);
        };

        LabelOverlay.prototype.draw = function () {
          if (!this.div) return; // Memeriksa apakah elemen div sudah dibuat
          var overlayProjection = this.getProjection();
          var pixel = overlayProjection.fromLatLngToDivPixel(this.position);
          var div = this.div;
          div.style.left = pixel.x + 'px';
          div.style.top = pixel.y + 'px';
        };

        LabelOverlay.prototype.remove = function () {
          if (this.div) {
            this.div.parentNode.removeChild(this.div);
            this.div = null;
          }
        };

        // Menambahkan label ke peta untuk setiap GeoJSON feature
        // for (var i = 0; i < data.length; i++) {
          // var feature = data;
          var centerCoordinates = new google.maps.LatLng(data.geometry.coordinates_center[0], data.geometry.coordinates_center[1]);
          var labelId = 'label' + (index + 1);
          var labelOverlay = new LabelOverlay(map, centerCoordinates, data.properties.NAME_1, labelId);
          labelOverlays.push(labelOverlay);
        // }
      })
      .catch(error => console.error('Error fetching GeoJSON:', error));
  });

  // // Menampilkan label saat peta di-zoom-in atau di-zoom-out
  // google.maps.event.addListener(map, 'zoom_changed', function () {
  //   // Iterasi melalui setiap GeoJSON feature dan menampilkan/menyembunyikan label berdasarkan batas GeoJSON
  //   for (var i = 0; i < geojsonFeatures.length; i++) {
  //     var feature = geojsonFeatures[i];
  //     var bounds = new google.maps.LatLngBounds(
  //       new google.maps.LatLng(feature.geometry.coordinates_center[0] - 0.5, feature.geometry.coordinates_center[1] - 0.5),
  //       new google.maps.LatLng(feature.geometry.coordinates_center[0] + 0.5, feature.geometry.coordinates_center[1] + 0.5)
  //     );
  //     var labelId = 'label' + (i + 1);
  //     var label = document.getElementById(labelId);
  //     if (label) { // Memeriksa apakah elemen label sudah dibuat
  //       if (bounds.contains(map.getCenter())) {
  //         label.style.display = 'block';
  //       } else {
  //         label.style.display = 'none';
  //       }
  //     }
  //   }
  //   // Memanggil draw untuk memperbarui posisi label
  //   for (var i = 0; i < labelOverlays.length; i++) {
  //     labelOverlays[i].draw();
  //   }
  // });
}

function hapus_geo() {
  for (let i = 0; i < labelOverlays.length; i++) {
    labelOverlays[i].remove()
  }

  labelOverlays.splice(0, labelOverlays.length);

  for (let j = 0; j < layers.length; j++) {
    layers[j].setMap(null)
  }
  layers = []
  geojsonFeatures= []

  if (heatmapDataLalin.length > 0) {
    if (heatmap_lalin && typeof heatmap_lalin.setMap === 'function') {
      heatmap_lalin.setMap(null);
    }
    heatmapDataLalin = []
    heatmap_lalin = null
  }

  if (heatmapDataEnforcementLalin.length > 0) {
    if (heatmap_enforcement_lalin && typeof heatmap_enforcement_lalin.setMap === 'function') {
      heatmap_enforcement_lalin.setMap(null);
    }
    heatmapDataEnforcementLalin = []
    heatmap_enforcement_lalin = null
  }

  if (heatmapDataEnforcementPerda.length > 0) {
    if (heatmap_enforcement_perda && typeof heatmap_enforcement_perda.setMap === 'function') {
      heatmap_enforcement_perda.setMap(null);
    }
    heatmapDataEnforcementPerda = []
    heatmap_enforcement_perda = null
  }
}

function logout_sso(username='') {
  window.location.href = `${global_url_ci}Auth/logout_sso/${username}`;
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
  clearTimeout(t_out_traffic_all_cctv)
  clearTimeout(t_out_traffic_batas_kota);
  clearTimeout(t_out_traffic_batas_kota_cctv)
  clearTimeout(t_out_traffic_batas_provinsi)
  clearTimeout(t_out_traffic_batas_provinsi_cctv)
  clearTimeout(t_out_traffic_dalam_kota)
  clearTimeout(t_out_traffic_crowd_counting);
	clearTimeout(t_out_traffic_crowd_counting_cctv_multiple);
  clearTimeout(t_out_enforcement_lalin)
  clearTimeout(t_out_enforcement_perda)
  clearTimeout(t_out_mobile_officer)
  clearTimeout(t_out_traffic_dalam_kota_detail)
}

function back_to() {
  const center = map.getCenter();
  map.setZoom(parseInt(global_zoom_map))
  map.setCenter({ lat: parseFloat(global_lat_center), lng: parseFloat(global_lng_center) });
}

function menu_sm_traffic(val) {
  $('#konten-kiri').html('');
  $('#title-konten-tengah').html();
  $('#subtitle-konten-tengah').html('')
  $('#konten-kanan').html('');
  $("#bottom-content").html('');

  hapus_geo()
  cleartimeout()

  switch (val) {

    case 'all_cctv':
      map.setZoom(parseInt(global_zoom_map));
			map.setCenter({ lat: parseFloat(global_lat_center), lng: parseFloat(global_lng_center) });
      $("#allCctvActive").attr("fill", "#0C8CE9");
      $("#perbatasanProvinsiActive").attr("fill", "#000C1E");
      $("#perbatasanKotaActive").attr("fill", "#000C1E");
      $("#dalamKotaActive").attr("fill", "#000C1E");
      $('#konten-kiri').removeClass('col-3').addClass('col-2')
      $('#konten-tengah').removeClass('col-10').removeClass('col-9').addClass('col-8')
      $('#konten-kanan').removeClass('d-none')
      $('#wadah_list_cctv').addClass('d-none')
      $("#bottom-content").removeClass("d-none");
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Perbatasan Provinsi')
      dash_traffic_all_cctv();
      $("#custom-legend").html(`
        <div class="position-absolute ms-3 p-1 rounded" id="t-legend" style="z-index:1;width:auto;${paramsPolres == null ?'bottom:35%':'bottom:5%'};background: #000C1E4D;">
          <span class="d-flex flex-column align-items-start">
            <div>
              <img src="${global_url_ci}my/logo/cctv_objek_vital.png" width="25" alt="">
              <span class="text-dark" style="font-weight:600;">
                CCTV Objek Vital
                <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend_objek_vital'></span>
              </span>
            </div>
            ${paramsPolres == null ? `
              <div>
                <img src="${global_url_ci}my/logo/cctv_batas_provinsi.png" width="25" alt="">
                <span class="text-dark" style="font-weight:600;">
                  CCTV Batas Provinsi
                  <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend_batas_provinsi'></span>
                </span>
              </div>` : ''}
            <div>
              <img src="${global_url_ci}my/logo/cctv_batas_kota.png" width="25" alt="">
              <span class="text-dark" style="font-weight:600;">
                CCTV Batas Kota
                <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend_batas_kota'></span>
              </span>
            </div>
            <div>
              <img src="${global_url_ci}my/logo/cctv_dalam_kota.png" width="25" alt="">
              <span class="text-dark" style="font-weight:600;">
                CCTV Dalam Kota
                <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend_dalam_kota'></span>
              </span>
            </div>
          </span>
        </div>
        <div class="position-absolute bg-white me-3 rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right:0;${paramsPolres == null ?'bottom:35%':'bottom:5%'};">
          <h3><i class="fa fa-undo"></i></h3>
        </div>
      `);
      $("#konten-filter").html(`
        <div id="reportrange" key="all_cctv" class="text-white filter-custom" style="background-color: #000C1E">
            <i class="fa fa-calendar"></i>&nbsp;
            <span></span> <i class="fa fa-caret-down"></i>
        </div>
      `);
      filter_range()
      break;

    case 'perbatasan_provinsi':
      map.setZoom(parseInt(global_zoom_map));
			map.setCenter({ lat: parseFloat(global_lat_center), lng: parseFloat(global_lng_center) });
      $("#allCctvActive").attr("fill", "#000C1E");
			$("#perbatasanProvinsiActive").attr("fill", "#0C8CE9");
			$("#perbatasanKotaActive").attr("fill", "#000C1E");
			$("#dalamKotaActive").attr("fill", "#000C1E");
      $('#konten-kiri').removeClass('col-3').addClass('col-2')
      $('#konten-tengah').removeClass('col-10').removeClass('col-9').addClass('col-8')
      $('#konten-kanan').removeClass('d-none')
      $("#wadah_list_cctv").removeClass("d-none");
      $("#bottom-content").addClass("d-none");
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Perbatasan Provinsi')
      dash_traffic_perbatasan_provinsi();
      $("#custom-legend").html(`
        <div class="position-absolute ms-3 p-1 rounded" id="t-legend" style="z-index:1;width:auto;bottom:5%;background: #000C1E4D;">
          <span class="d-flex align-items-center"><img src="${global_url_ci}my/logo/cctv_batas_provinsi.png" width="35" alt=""> <span class="text-dark" style="font-weight:600;">CCTV Batas Provinsi <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend'></span></span></span>
        </div>
        <div class="position-absolute bg-white me-3 rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right:0;bottom:5%;">
          <h3><i class="fa fa-undo"></i></h3>
        </div>
      `);
      $("#konten-filter").html(`
        <div div id="reportrange" key="batas_provinsi" class="text-white filter-custom" style="background-color: #000C1E">
            <i class="fa fa-calendar"></i>&nbsp;
            <span></span> <i class="fa fa-caret-down"></i>
        </div>
      `);
      filter_range()
      break;

    case 'perbatasan_kota':
      map.setZoom(parseInt(global_zoom_map));
			map.setCenter({ lat: parseFloat(global_lat_center), lng: parseFloat(global_lng_center) });
      g_start_filter = '';
      g_end_filter = '';
      $("#allCctvActive").attr("fill", "#000C1E");
			$("#perbatasanProvinsiActive").attr("fill", "#000C1E");
			$("#perbatasanKotaActive").attr("fill", "#0C8CE9");
			$("#dalamKotaActive").attr("fill", "#000C1E");
      $('#konten-kiri').removeClass('col-3').addClass('col-2')
      $('#konten-tengah').removeClass('col-10').removeClass('col-9').addClass('col-8')
      $('#konten-kanan').removeClass('d-none')
      $('#wadah_list_cctv').removeClass('d-none');
      $("#bottom-content").addClass("d-none");
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Perbatasan Kota')
      dash_traffic_perbatasan_kota();
      $("#custom-legend").html(`
        <div class="position-absolute ms-3 p-1 rounded" id="t-legend" style="z-index:1;width:auto;bottom:5%;background: #000C1E4D;">
          <span class="d-flex align-items-center"><img src="${global_url_ci}my/logo/cctv_batas_kota.png" width="35" alt=""> <span class="text-dark" style="font-weight:600;">CCTV Batas Kota <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend'></span></span></span>
        </div>
        <div class="position-absolute bg-white me-3 rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right:0;bottom:5%;">
          <h3><i class="fa fa-undo"></i></h3>
        </div>
      `);
      $("#konten-filter").html(`
        <div div id="reportrange" key="batas_kota" class="text-white filter-custom" style="background-color: #000C1E">
            <i class="fa fa-calendar"></i>&nbsp;
            <span></span> <i class="fa fa-caret-down"></i>
        </div>
        ${paramsPolres == null ? `<div class="mt-2">
            <select class="form-select" id="f-polres" onchange="filter_data_polres(this,'batas_kota')">
              <option value=''>ALL POLRES</option>
            </select>
           </div>` :
          ``}
      `);
      get_polres(`polda_id=${global_polda_id}&`+paramsPolres).then((e) => {
        e.forEach(data => {
          $('#f-polres').append(`
            <option value="${data.polres},${data.polres_id}">${data.polres}</option>
          `)
        });
        $('#f-polres').select2()
      })
      filter_range()
      break;

    case 'dalam_kota':
      map.setZoom(parseInt(global_zoom_map));
			map.setCenter({ lat: parseFloat(global_lat_center), lng: parseFloat(global_lng_center) });
      $("#allCctvActive").attr("fill", "#000C1E");
			$("#perbatasanProvinsiActive").attr("fill", "#000C1E");
			$("#perbatasanKotaActive").attr("fill", "#000C1E");
			$("#dalamKotaActive").attr("fill", "#0C8CE9");
      $('#konten-kiri').removeClass('col-3').addClass('col-2')
      $('#konten-tengah').removeClass('col-10').removeClass('col-9').addClass('col-8')
      $('#konten-kanan').removeClass('d-none');
      $("#bottom-content").addClass("d-none");
      $("#wadah_list_cctv").removeClass("d-none");
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Dalam Kota')
      dash_traffic_dalam_kota();
      $("#custom-legend").html(`
        <div class="position-absolute bg-white me-3 rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right:0;bottom:5%;">
          <h3><i class="fa fa-undo"></i></h3>
        </div>
      `);
      $("#konten-filter").html(`
        <div div id="reportrange" key="dalam_kota" class="text-white filter-custom" style="background-color: #000C1E">
            <i class="fa fa-calendar"></i>&nbsp;
            <span></span> <i class="fa fa-caret-down"></i>
        </div>
        ${paramsPolres == null ? `<div class="mt-2">
            <select class="form-select" id="f-polres" onchange="filter_data_polres(this,'dalam_kota')">
              <option value=''>ALL POLRES</option>
            </select>
           </div>` :
          ``}
      `);
      get_polres(`polda_id=&${global_polda_id}` + paramsPolres).then((e) => {
				e.forEach((data) => {
					$("#f-polres").append(`
            <option value="${data.polres},${data.polres_id}">${data.polres}</option>
          `);
				});
				$("#f-polres").select2();
			});
      filter_range()
      break;

    case 'crowd_counting':
      $('#title-konten-tengah').html('SMART TRAFFIC');
      $('#subtitle-konten-tengah').html('Crowd Counting')
      dash_traffic_crowd_counting();
      $('#custom-legend').html(`
        <div class="position-absolute ms-3 p-1 rounded" id="t-legend" style="z-index:1;width:auto;bottom:5%;background: #000C1E4D;">
          <span class="d-flex align-items-center"><img src="${global_url_ci}my/logo/cctv_objek_vital.png" width="35" alt=""> <span class="text-dark" style="font-weight:600;">CCTV Objek Vital <span class="text-dark" style="font-weight:600;" id='jml_cctv_legend'></span></span></span>
        </div>
      `)
      break;

    default:
      alert('js belum dibuat!')
      break;
  }
}

function menu_sm_enforcement(val) {
  $('#konten-kiri').html('');
  $('#title-konten-tengah').html();
  $('#subtitle-konten-tengah').html('')
  $('#konten-kanan').html('');

  hapus_geo()
  cleartimeout()

  switch (val) {

    case 'lalin':
      $('#f-data-enforcement').html(`
          <input class="form-control" type="date" onchange="f_data_enforcement(event,'lalin')" value="${date_time_filter().date_now}" name="" style="width:15rem;">
      `)
      // $('#konten-kanan').addClass('d-none')
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Enforcement - Pelanggaran Lalin')
      dash_enforcement_lalin()
      $('#custom-legend').html(`
        <div class="position-absolute bg-white rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right: 17.7%;bottom: 5%;">
          <h3><i class="fa fa-undo"></i></h3>
        </div>
      `)
      $('#konten-filter').html(`
        ${paramsPolres == null ? `
          <div class="mb-2">
              <select class="form-select" id="f-polres" onchange="filter_data_polres(this,'enforcement_lalin')">
                <option value=''>ALL POLRES</option>
              </select>
          </div>` : ''}
        <div id="reportrange" key="pel_lalin" class="bg-primary text-white filter-custom">
            <i class="fa fa-calendar"></i>&nbsp;
            <span></span> <i class="fa fa-caret-down"></i>
        </div>
      `)
      get_polres(`polda_id=${global_polda_id}`).then((e) => {
        e.forEach(data => {
          $('#f-polres').append(`
            <option value="${data.polres},${data.polres_id}">${data.polres}</option>
          `)
        });
      })
      filter_range()
      break;

    case 'perda':
      $('#f-data-enforcement').html(`
          <input class="form-control" type="date" onchange="f_data_enforcement(event,'perda')" value="${date_time_filter().date_now}" name="" style="width:15rem;">
      `)
      // $('#konten-kanan').addClass('d-none')
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Enforcement - Pelanggaran Perda')
      dash_enforcement_perda()
      $('#custom-legend').html(`
        <div class="position-absolute bg-white rounded" onclick="back_to()" style="z-index:1;cursor:pointer;padding:1px 5px;right: 17.7%;bottom: 5%;">
          <h3><i class="fa fa-undo"></i></h3>
        </div>
      `)
      $('#konten-filter').html(`
        ${paramsPolres == null ? `
          <div class="mb-2">
              <select class="form-select" id="f-polres" onchange="filter_data_polres(this,'enforcement_perda')">
                <option value=''>ALL POLRES</option>
              </select>
          </div>` : ''}
        <div id="reportrange" key="pel_perda" class="bg-primary text-white filter-custom">
            <i class="fa fa-calendar"></i>&nbsp;
            <span></span> <i class="fa fa-caret-down"></i>
        </div>
      `)
      get_polres(`polda_id=${global_polda_id}`).then((e) => {
        e.forEach(data => {
          $('#f-polres').append(`
            <option value="${data.polres},${data.polres_id}">${data.polres}</option>
          `)
        });
      })
      filter_range()
      break;
    default:
      alert('js belum dibuat!')
      break;
  }
}

function f_data_enforcement(e,category) {
  $('#konten-kiri').html('')
  cleartimeout()
  hapus_geo()
  switch (category) {
    case 'lalin':
      dash_enforcement_lalin({date : e.target.value})
      break;

    case 'perda':
      dash_enforcement_perda({date : e.target.value})
      break;

    default:
      break;
  }
}

function filter_range(detail = '') {
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
          $('#title-konten-tengah').html(`${global_app_name}`);
          $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Dalam Kota')
          $('#konten-kiri').html('');
          hapus_geo()
          cleartimeout()
          dash_traffic_dalam_kota_detail(detail.replace("_"," "),{start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
      }else {
        switch ($('#reportrange').attr('key')) {
          case 'all_cctv':
              $('#title-konten-tengah').html(`${global_app_name}`);
              $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Perbatasan Provinsi')
              $('#konten-kiri').html('');
              $("#bottom-content").html('');
              $('#konten-kanan').html('');
              hapus_geo()
              cleartimeout()
              dash_traffic_all_cctv({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;
          case 'batas_provinsi':
              $('#title-konten-tengah').html(`${global_app_name}`);
              $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Perbatasan Provinsi')
              $('#konten-kiri').html('');
              $('#konten-kanan').html('');
              hapus_geo()
              cleartimeout()
              dash_traffic_perbatasan_provinsi({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;
          case 'batas_kota':
              $('#title-konten-tengah').html(`${global_app_name}`);
              $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Perbatasan Kota')
              $('#konten-kiri').html('');
              $('#konten-kanan').html('');
              hapus_geo()
              cleartimeout()
              dash_traffic_perbatasan_kota({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;
          case 'dalam_kota':
              $('#title-konten-tengah').html(`${global_app_name}`);
              $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Dalam Kota')
              $('#konten-kiri').html('');
              hapus_geo()
              cleartimeout()
              dash_traffic_dalam_kota({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;
          case 'crowd_counting':
              $('#title-konten-tengah').html(`${global_app_name}`);
              $('#subtitle-konten-tengah').html('Pengamanan Object Vital');
              $('#konten-kanan').html('');
              hapus_geo()
              cleartimeout()
              selectedCCTVs.length > 0
								? dash_traffic_crowd_counting_cctv_multiple(selectedCCTVs, {
										start_date: picker.startDate.format("YYYY-MM-DD"),
										end_date: picker.endDate.format("YYYY-MM-DD"),
								  })
								: dash_traffic_crowd_counting({
										start_date: picker.startDate.format("YYYY-MM-DD"),
										end_date: picker.endDate.format("YYYY-MM-DD"),
								  });
            break;
          case 'pel_lalin':
              $('#title-konten-tengah').html(`${global_app_name}`);
              $('#subtitle-konten-tengah').html('Smart Enforcement - Pelanggaran Lalin')
              $('#konten-kiri').html('');
              hapus_geo()
              cleartimeout()
              dash_enforcement_lalin({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;
          case 'pel_perda':
              $('#title-konten-tengah').html(`${global_app_name}`);
              $('#subtitle-konten-tengah').html('Smart Enforcement - Pelanggaran Perda')
              $('#konten-kiri').html('');
              hapus_geo()
              cleartimeout()
              dash_enforcement_perda({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;

          case 'officer':
              $('#title-konten-tengah').html(`${global_app_name}`);
              $('#subtitle-konten-tengah').html('Smart Officer')
              $('#konten-kanan').html('');
              dash_mobile_officer_kanan({start_date : picker.startDate.format('YYYY-MM-DD'), end_date : picker.endDate.format('YYYY-MM-DD')})
            break;

          default:
            break;
      }
      }
    });


}

function filter_data_polres(val,cat) {
  switch (cat) {
    case 'dalam_kota':
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Perbatasan Kota')
      $('#konten-kiri').html('');
      $('#konten-kanan').html('');
      hapus_geo()
      cleartimeout()
      dash_traffic_dalam_kota({start_date: g_start_filter.format('YYYY-MM-DD'),end_date : g_end_filter.format('YYYY-MM-DD')})
      break;

    case 'batas_kota':
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Traffic - Lalu Lintas Perbatasan Kota')
      $('#konten-kiri').html('');
      $('#konten-kanan').html('');
      hapus_geo()
      cleartimeout()
      dash_traffic_perbatasan_kota({start_date: g_start_filter.format('YYYY-MM-DD'),end_date : g_end_filter.format('YYYY-MM-DD')})
      break;

    case 'security':
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Pengamanan Object Vital')
      $('#konten-kiri').html('');
      $('#konten-kanan').html('');
      hapus_geo()
      cleartimeout()

	    selectedCCTVs = [];
      dash_cctv_objek_vital();
      dash_traffic_crowd_counting({
				start_date: g_start_filter.format("YYYY-MM-DD"),
				end_date: g_end_filter.format("YYYY-MM-DD"),
			});
      break;

    case 'enforcement_perda':
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Enforcement - Pelanggaran Perda')
      $('#konten-kiri').html('');
      $('#konten-kanan').html('');
      hapus_geo()
      cleartimeout()
      dash_enforcement_perda({start_date: g_start_filter.format('YYYY-MM-DD'),end_date : g_end_filter.format('YYYY-MM-DD')})
      break;

    case 'enforcement_lalin':
      $('#title-konten-tengah').html(`${global_app_name}`);
      $('#subtitle-konten-tengah').html('Smart Enforcement - Pelanggaran Lalin')
      $('#konten-kiri').html('');
      $('#konten-kanan').html('');
      hapus_geo()
      cleartimeout()
      dash_enforcement_lalin({start_date: g_start_filter.format('YYYY-MM-DD'),end_date : g_end_filter.format('YYYY-MM-DD')})
      break;

    default:
      break;
  }
}

function filter_obj(filter={}) {
  let fltr = ''
  if (Object.keys(filter).length != 0) {
    for (let i = 0; i < Object.keys(filter).length; i++) {
      fltr += `&${Object.keys(filter)[i]}=${Object.values(filter)[i]}`;
    }
  }
  return fltr
}

// Function to toggle the left content (KENDARAAN MASUK)
function toggleKiri() {
	if (isKiriFullScreen) {
		// Return to the original layout
		kontenKiri.style.flex = "0 0 16.6667%"; // Bootstrap col-2
		kontenTengah.style.display = "block"; // Show the middle content
		kontenKanan.style.display = "block"; // Show the right content
	} else {
		// Expand the left content to full screen
		kontenKiri.style.flex = "1"; // Full width
		kontenTengah.style.display = "none"; // Hide the middle content
		kontenKanan.style.display = "none"; // Hide the right content
	}
	isKiriFullScreen = !isKiriFullScreen;
}

// Function to toggle the right content (KENDARAAN KELUAR)
function toggleKanan() {
	if (isKananFullScreen) {
		// Return to the original layout
		kontenKanan.style.flex = "0 0 16.6667%"; // Bootstrap col-2
		kontenTengah.style.display = "block"; // Show the middle content
		kontenKiri.style.display = "block"; // Show the left content
	} else {
		// Expand the right content to full screen
		kontenKanan.style.flex = "1"; // Full width
		kontenTengah.style.display = "none"; // Hide the middle content
		kontenKiri.style.display = "none"; // Hide the left content
	}
	isKananFullScreen = !isKananFullScreen;
}
