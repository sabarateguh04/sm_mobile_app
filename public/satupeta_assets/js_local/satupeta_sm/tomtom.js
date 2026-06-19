let TTSD = {}
let TTSS = {}
let TTI = {}
let infoWindowTSS
let infoWindowTSD
let infoWindowTom

function get_list_tomtom() {
    if ($('#cstm-content').hasClass('d-none')) {
		$('#cstm-content').removeClass('d-none')
		$('#cstm-content').append(`
		<div class="accordion">
			<div class="accordion-item">
				<h2 class="accordion-header" id="panelsStayOpen-headingOne">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
					DATA TRAFFIC STATISTICS
				</button>
				</h2>
				<div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
				<div class="accordion-body">
                    <ul class="list-group mt-2" id="" style="height:200px!important; overflow:auto;">
                    <li class="list-group-item d-flex align-items-center list-group-item-action" onclick="tomtom_traffic_incident()" style="cursor:pointer;">
                        <div class="text-wrap ms-3">
                            <span id="tti" class='${TTI.type != undefined ? 'check-in-peta' : ''}'></span>
                            <span class="mb-0">Traffic Incident</span>
                        </div>
                    </li>
                    ${geojson_speed_file ? 
                        `<li class="list-group-item d-flex align-items-center list-group-item-action" onclick="tomtom_traffic_stats_speed()" style="cursor:pointer;">
                            <div class="text-wrap ms-3">
                                <span id="ttss" class='${TTSS.type != undefined ? 'check-in-peta' : ''}'></span>
                                <span class="mb-0">Traffic Average Speed</span>
                            </div>
                        </li>`
                        :''}
                    ${geojson_density_file ? 
                        `<li class="list-group-item d-flex align-items-center list-group-item-action" onclick="tomtom_traffic_stats_density()" style="cursor:pointer;">
                            <div class="text-wrap ms-3">
                                <span id="ttsd" class='${TTSD.type != undefined ? 'check-in-peta' : ''}'></span>
                                <span class="mb-0">Traffic Density</span>
                            </div>
                        </li>`
                        :''
                    }
					</ul>
				</div>
				</div>
			</div>
		</div>
		`)
	}else{
		$('#cstm-content').html('')
		$('#cstm-content').addClass('d-none')
	}
}

function tomtom_traffic_incident() {
    hide_traffic_map = false
    trafficLayer.setMap(null);
    if (TTSD.type != undefined) {
        $("#ttsd").removeClass("check-in-peta");
        $("#legend-ttsd").addClass("d-none");
        map.data.forEach(function(feature) {
            map.data.remove(feature);
        });
        TTSD = {}
    }
    if (TTSS.type != undefined) {
        $("#ttss").removeClass("check-in-peta");
        $("#legend-ttss").addClass("d-none");
        map.data.forEach(function(feature) {
            map.data.remove(feature);
        });
        TTSS = {}
    }
      if ($("#tti").hasClass("check-in-peta")) {
        // $('#mgmt_route').addClass('d-none');
        $("#tti").removeClass("check-in-peta");
        $('#jml_data_item_tom').remove()
        for (let i = 0; i < geotom.length; i++) {
          map.data.remove(geotom[i])
        }
        TTI = {}
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
            TTI = jsonData
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
            if (infoWindowTSS) infoWindowTSS.close();
            if (infoWindowTSD) infoWindowTSD.close();
            infoWindowTom = new google.maps.InfoWindow();

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
        $("#tti").addClass("check-in-peta");
      }
}

function tomtom_traffic_stats_speed() {
    hide_traffic_map = false
    trafficLayer.setMap(null);
    if (TTSD.type != undefined) {
        $("#ttsd").removeClass("check-in-peta");
        $("#legend-ttsd").addClass("d-none");
        map.data.forEach(function(feature) {
            map.data.remove(feature);
        });
        TTSD = {}
    }
    if (geotom != undefined) {
        $("#tti").removeClass("check-in-peta");
        $('#jml_data_item_tom').remove()
            for (let i = 0; i < geotom.length; i++) {
              map.data.remove(geotom[i])
        }
    }
    if ($("#ttss").hasClass("check-in-peta")) {
        // $('#mgmt_route').addClass('d-none');
        $("#ttss").removeClass("check-in-peta");
        $("#legend-ttss").addClass("d-none");
        map.data.forEach(function(feature) {
            map.data.remove(feature);
        });
        TTSS = {}
    } else {
        if (infoWindowTom) infoWindowTom.close();
        if (infoWindowTSD) infoWindowTSD.close();

        infoWindowTSS = new google.maps.InfoWindow();
        fetch(`./my/tomtom/${geojson_speed_file}`)
        .then(response => response.json())
        .then(jsonData => {
            TTSS = jsonData
            map.data.loadGeoJson(`./my/tomtom/${geojson_speed_file}`);

            map.data.setStyle(function(feature) {
                if (feature.getProperty("segmentTimeResults")) {
                    const avgspeed = feature.getProperty("segmentTimeResults")[0].averageSpeed;
                    const color = getColorByAvgspeed(avgspeed);
                    return {
                        strokeColor: color,
                        strokeWeight: 2
                    };
                }
            });

            map.data.addListener('click', function(event) {
                if (event.feature.getProperty("segmentTimeResults") && event.feature.getProperty("segmentTimeResults").length > 0) {
                    // Mendapatkan data `probeCount` dari fitur yang diklik
                    const averageSpeed = event.feature.getProperty("segmentTimeResults")[0].averageSpeed;
                    const streetName = event.feature.getProperty('streetName'); // contoh data lain jika ada
                
                    // Konten untuk InfoWindow
                    const contentString = `
                        <div>
                            <h5>${streetName}</h5>
                            <p><strong>Kecepatan rata-rata :</strong> ${averageSpeed} km/h</p>
                        </div>
                    `;
                
                    // Menampilkan InfoWindow pada lokasi yang diklik
                    infoWindowTSS.setContent(contentString);
                    infoWindowTSS.setPosition(event.latLng);
                    infoWindowTSS.open(map);
                }
            });
        })
        .catch(error => {
            console.error('Error fetching JSON:', error);
        });
        $("#ttss").addClass("check-in-peta");
        $("#legend-ttss").removeClass("d-none");

    }
}

function tomtom_traffic_stats_density() {
    hide_traffic_map = false
    trafficLayer.setMap(null);
    if (TTSS.type != undefined) {
        $("#ttss").removeClass("check-in-peta");
        $("#legend-ttss").addClass("d-none");
        map.data.forEach(function(feature) {
            map.data.remove(feature);
        });
        TTSS = {}
    }
    if (geotom != undefined) {
        $("#tti").removeClass("check-in-peta");
        $('#jml_data_item_tom').remove()
            for (let i = 0; i < geotom.length; i++) {
              map.data.remove(geotom[i])
        }
    }
    if ($("#ttsd").hasClass("check-in-peta")) {
        // $('#mgmt_route').addClass('d-none');
        $("#ttsd").removeClass("check-in-peta");
        $("#legend-ttsd").addClass("d-none");
        map.data.forEach(function(feature) {
            map.data.remove(feature);
        });
        TTSD = {}
    } else {
        if (infoWindowTom) infoWindowTom.close();
        if (infoWindowTSS) infoWindowTSS.close();

        infoWindowTSD = new google.maps.InfoWindow();
        fetch(`./my/tomtom/${geojson_density_file}`)
        .then(response => response.json())
        .then(jsonData => {
            TTSD = jsonData
            map.data.loadGeoJson(`./my/tomtom/${geojson_density_file}`);

            map.data.setStyle(function(feature) {
                let dt = feature
                if (dt.getProperty("segmentProbeCounts")) {
                    const probecount = dt.getProperty("segmentProbeCounts")[0].probeCount;
                    const color = getColorByProbecount(probecount);
                    return {
                        strokeColor: color,
                        strokeWeight: 2
                    };
                }
            });

            map.data.addListener('click', function(event) {
                if (event.feature.getProperty("segmentProbeCounts") && event.feature.getProperty("segmentProbeCounts").length > 0) {
                    // Mendapatkan data `probeCount` dari fitur yang diklik
                    const probeCount = event.feature.getProperty("segmentProbeCounts")[0].probeCount;
                    const streetName = event.feature.getProperty('streetName'); // contoh data lain jika ada
                
                    // Konten untuk InfoWindow
                    const contentString = `
                        <div>
                            <h5>${streetName}</h5>
                            <p><strong>Jumlah:</strong> ${probeCount}</p>
                        </div>
                    `;
                
                    // Menampilkan InfoWindow pada lokasi yang diklik
                    infoWindowTSD.setContent(contentString);
                    infoWindowTSD.setPosition(event.latLng);
                    infoWindowTSD.open(map);
                }
            });
        })
        .catch(error => {
            console.error('Error fetching JSON:', error);
        });
        $("#ttsd").addClass("check-in-peta");
        $("#legend-ttsd").removeClass("d-none");

    }
}

// Palet warna berdasarkan gambar yang diberikan
const ttsdcolors = [
    '#f8e5b1', // 0
    '#ebb181', // 2500
    '#e6896e', // 5000
    '#d86b69', // 7500
    '#cd566a', // 10000
    '#b54c6b', // 12500
    '#8b366e', // 15000
    '#6a206d', // 17500
    '#4d1867'  // 20000
];

const ttsscolors = [
    '#0eb00b', // 0
    '#37c008', // 10
    '#63d00c', // 20
    '#91e00f', // 30
    '#b0e70d', // 40
    '#cbec0c', // 50
    '#e5f00d', // 60
    '#f2e60e', // 70
    '#f2cf10', // 80
    '#f2b80b', // 90
    '#f29b0c', // 100
    '#f26d0c', // 110
    '#f2100e', // 120
]

// Fungsi untuk menghitung warna berdasarkan avgspeed
function getColorByAvgspeed(avgspeed) {
    if (avgspeed <= 0) return ttsscolors[0];
    if (avgspeed >= 120) return ttsscolors[ttsscolors.length - 1];

    const index = Math.floor(avgspeed / 10);
    const nextIndex = Math.min(index + 1, ttsscolors.length - 1);
    
    // Calculate the ratio for interpolation between two colors
    const ratio = (avgspeed % 10) / 10;
    return interpolateColor(ttsscolors[index], ttsscolors[nextIndex], ratio);
}

// Fungsi untuk menghitung warna berdasarkan probecount
function getColorByProbecount(probecount) {
    if (probecount <= 0) return ttsdcolors[0];
    if (probecount >= 20000) return ttsdcolors[ttsdcolors.length - 1];

    const index = Math.floor(probecount / 2500);
    const nextIndex = Math.min(index + 1, ttsdcolors.length - 1);
    
    // Proporsi antara dua warna
    const ratio = (probecount % 2500) / 2500;
    return interpolateColor(ttsdcolors[index], ttsdcolors[nextIndex], ratio);
}

// Fungsi interpolasi warna (menghitung warna antara dua warna)
function interpolateColor(color1, color2, ratio) {
    const hex = (color) => parseInt(color.slice(1), 16);
    const r = Math.round((1 - ratio) * ((hex(color1) >> 16) & 0xff) + ratio * ((hex(color2) >> 16) & 0xff));
    const g = Math.round((1 - ratio) * ((hex(color1) >> 8) & 0xff) + ratio * ((hex(color2) >> 8) & 0xff));
    const b = Math.round((1 - ratio) * (hex(color1) & 0xff) + ratio * (hex(color2) & 0xff));
    return `rgb(${r}, ${g}, ${b})`;
}