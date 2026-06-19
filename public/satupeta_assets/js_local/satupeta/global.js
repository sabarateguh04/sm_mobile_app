
async function postDataApi(url = "", data = {}) {
	// Default options are marked with *
	const response = await fetch(url, {
		method: "POST", // *GET, POST, PUT, DELETE, etc.
		mode: "cors", // no-cors, *cors, same-origin
		cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
		credentials: "same-origin", // include, *same-origin, omit
		headers: {
			"Content-Type": "application/json",
			Accept: "application/json",
			"token" : "5b3dac76aaee24d14185cbc3d010fd20"
			// Authorization: "Bearer " + indicarKey.indicarToken,
			// 'Content-Type': 'application/x-www-form-urlencoded',
		},
		redirect: "follow", // manual, *follow, error
		referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
		body: JSON.stringify(data), // body data type must match "Content-Type" header
	});
	return response.json(); // parses JSON response into native JavaScript objects
}

async function getDataApi(url = "", data = {}, token = "") {
	// Default options are marked with *
	const response = await fetch(url, {
		method: "GET", // *GET, POST, PUT, DELETE, etc.
		mode: "cors", // no-cors, *cors, same-origin
		cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
		credentials: "same-origin", // include, *same-origin, omit
		headers: {
			"Content-Type": "application/json",
			// 'Content-Type': 'application/x-www-form-urlencoded',
			"token" : token
		},  
		redirect: "follow", // manual, *follow, error
		referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
	});
	return response.json(); // parses JSON response into native JavaScript objects
}

async function getDataSmNew(url = "", data = {}) {
    // let a = getRToken().then(async r=>{
    //     //Default options are marked with *
    //     if (r.status != true) {
    //         window.location.href = `${global_url_ci}Auth/logout`
    //     }else {
            try {
                const response = await fetch(url, {
                    method: "GET", // *GET, POST, PUT, DELETE, etc.
                    mode: "cors", // no-cors, *cors, same-origin
                    cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
                    credentials: "same-origin", // include, *same-origin, omit
                    headers: {
                        "Content-Type": "application/json",
                        "token": "5b3dac76aaee24d14185cbc3d010fd20",
                        // "second_bearer" : r.data.second_bearer
                        "secondbearer" : `${global_sbearer}`
                        // 'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    redirect: "follow", // manual, *follow, error
                    referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
                });
                const text = await response.text();
                if (!response.ok) {
                    return satupetaMockResponse(url);
                }
                try {
                    return JSON.parse(text);
                } catch {
                    console.warn('API non-JSON:', url, response.status);
                    return satupetaMockResponse(url);
                }
            } catch (error) {
                return satupetaMockResponse(url);
            }
    //     }
    // })
    // return a
}

function satupetaMockResponse(url = '') {
    const lat = parseFloat(window.SATUPETA_LAT || $('#user_lat').val() || -6.2);
    const lng = parseFloat(window.SATUPETA_LNG || $('#user_lng').val() || 106.816666);

    const mk = (i, extra = {}) => ({
        rowid: i + 1,
        id: i + 1,
        lat: lat + (i * 0.01),
        lng: lng + (i * 0.01),
        nama_jalan_s: `Jalan Contoh ${i + 1}`,
        namajalan: `Jalan Contoh ${i + 1}`,
        lokasi: `Lokasi ${i + 1}`,
        status: 'Aktif',
        ...extra,
    });

    if (url.includes('provinsiPolda')) {
        return {
            status: true,
            data: [{
                prov_id: 1,
                prov_name: 'Provinsi Demo',
                polda_id: '1',
                polda: 'POLDA DEMO',
                polygon: [[[
                    [lng - 0.25, lat - 0.25],
                    [lng + 0.25, lat - 0.25],
                    [lng + 0.25, lat + 0.25],
                    [lng - 0.25, lat + 0.25],
                    [lng - 0.25, lat - 0.25]
                ]]]
            }]
        };
    }

    if (url.includes('kotaPolres')) {
        return {
            status: true,
            data: [{
                city_id: 1,
                city_name: 'Kota Demo',
                polda_id: '1',
                polda: 'POLDA DEMO',
                polres_id: '1',
                polres: 'POLRES DEMO',
                prov_id: 1,
                polygon: [[[
                    [lng - 0.15, lat - 0.15],
                    [lng + 0.15, lat - 0.15],
                    [lng + 0.15, lat + 0.15],
                    [lng - 0.15, lat + 0.15],
                    [lng - 0.15, lat - 0.15]
                ]]]
            }]
        };
    }

    if (url.includes('sm_master_data/kerawanan')) {
        return {
            status: true,
            data: [{
                rowid: 1,
                kategori: 'Rawan Laka',
                icon: null,
                url_icon: './my/satupeta/rawan-laka.png'
            }]
        };
    }

    if (url.includes('data_collecting/wilayah_rawan')) {
        return { status: true, data: [mk(0, { kerawanan: 'Rawan Laka' }), mk(1, { kerawanan: 'Rawan Macet' })] };
    }

    if (url.includes('data_collecting/black_trouble_spot')) {
        return { status: true, data: [mk(0), mk(1), mk(2)] };
    }

    if (url.includes('data_collecting/rengiat') || url.includes('data_collecting/renops_pol') || url.includes('data_collecting/rengiat_masyarakat')) {
        return { status: true, data: [mk(0), mk(1)] };
    }

    if (url.includes('data_collecting/fasilitas_umum') || url.includes('data_collecting/objek_vital') || url.includes('opsdal/ops_kepolisian')) {
        return { status: true, data: [mk(0), mk(1)] };
    }

    if (url.includes('sm_master_data/kml_kategori')) {
        return { status: true, data: [{ rowid: 1, kategori: 'KML Demo' }] };
    }

    if (url.includes('sm_master_data/maps_kml')) {
        return { status: true, data: [{ rowid: 1, nama: 'KML Surakarta', file: `${global_url_ci}satupeta_assets/kml/surakarta.kml` }] };
    }

    if (url.includes('patroli_cctv/cctv')) {
        return { status: true, data: [mk(0, { rtsp_cctv: '', channel_id: '', jenis_cctv: 'demo' })] };
    }

    if (url.includes('sm_master_data/petugas')) {
        return { status: true, data: [] };
    }

    return { status: false, data: [], msg: 'Sumber data belum tersedia (fallback demo aktif).' };
}

async function getRToken() {
    try {
        const response = await fetch(global_url_ci+'Auth/r_token/'+tber+'/Y', {
            method: "GET", // *GET, POST, PUT, DELETE, etc.
            mode: "cors", // no-cors, *cors, same-origin
            cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
            credentials: "same-origin", // include, *same-origin, omit
            headers: {
                "Content-Type": "application/json",
                "token": "5b3dac76aaee24d14185cbc3d010fd20"
                // 'Content-Type': 'application/x-www-form-urlencoded',
            },
            redirect: "follow", // manual, *follow, error
            referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
        });
        return response.json(); // parses JSON response into native JavaScript objects
    } catch (error) {
        alert("error get refresh bearer")
    }
}

function getEmbedUrl(items='') {
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
	if (t_s != '' && t_s != undefined) {
		filter += `&tgl_start=${t_s}`
	}

	if (t_e != '' && t_e != undefined) {
		filter += `&tgl_end=${t_e}`
	}
    
    let url = global_url_api // server prod bali
    
    switch (items) {
        case 'black_spot':
            url += `data_collecting/black_trouble_spot?status=black spot&status_b_t_s=Aktif${filter}`
            break;
        case 'trouble_spot':
            url += `data_collecting/black_trouble_spot?status=trouble spot&status_b_t_s=Aktif${filter}`
            break;
        case 'rawan_laka':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=2${filter}`
            break;
        case 'rawan_macet':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=3${filter}`
            break;
        case 'rawan_tindak_pidana':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=5${filter}`
            break;
        case 'rawan_banjir':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=6${filter}`
            break;
        case 'rawan_longsor':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=7${filter}`
            break;
        case 'rawan_pohon_tumbang':
            url += `data_collecting/wilayah_rawan?jenis_kerawanan_id=9${filter}`
            break;
        case 'cctv':
            url += `patroli_cctv/cctv?${filter}`
            break;
		case 'fasum':
			url += `data_collecting/fasilitas_umum?${filter}`
			break;
		case 'objek_vital':
			url += `data_collecting/objek_vital?${filter}`
			break;
		case 'rengiat':
			url += `data_collecting/rengiat?${filter}`
			break;
        case 'rengiat_masyarakat':
            url += `data_collecting/rengiat_masyarakat?${filter}`
            break;
		case 'renops_pol':
			url += `data_collecting/renops_pol?${filter}`
			break;
        case 'posko':
            url += `opsdal/ops_kepolisian?jenis_kegiatan_id=27${filter}`
            break;
        case 'kml_kategori':
            url += `sm_master_data/kml_kategori?${filter}`
            break;
        case 'kml_maps':
            url += `sm_master_data/maps_kml?${filter}`
            break;
        default:
            break;
    }

    return url
}

var provinsi = []
var kota = []
var kecamatan = []
var kelurahan = []
var polda = []
var polres = []
var token_sm = (typeof SATUPETA_API_TOKEN !== 'undefined' && SATUPETA_API_TOKEN)
	? SATUPETA_API_TOKEN
	: "5b3dac76aaee24d14185cbc3d010fd20"

function get_provinsi(params) {
    provinsi = []
    return new Promise(function (resolve, reject) {
        showToast()
        getDataSmNew(`${global_url_api}provinsiPolda?${params}`, {}, token_sm).then((data) => {
            // getDataApi(`http://localhost:8082/api/v.0.1/provinsiPolda?${params}`, {}, token_sm).then((data) => {
            data.data.forEach((e) => {
                provinsi.push({ "prov_id" : e.prov_id, "prov_name" : e.prov_name, "polda_id" : e.polda_id, "polda" : e.polda ,"polygon" : e.polygon})
            })
            closeToast()
            resolve(provinsi)
        })
    })
}

function get_kota(params) {
    kota = []
    return new Promise(function (resolve, reject) {
        let url_api;
        if (params != 'prov_id=') {
            url_api = `${global_url_api}kotaPolres?${params}`
            // url_api = `http://localhost:8082/api/v.0.1/kotaPolres?${params}`
        }else {
            url_api = `${global_url_api}kotaPolres`
            // url_api = `http://localhost:8082/api/v.0.1/kotaPolres`
        }
        showToast()
        getDataSmNew(url_api, {}, token_sm).then((data) => {
            data.data.forEach((e) => {
                kota.push({ "city_id" : e.city_id, "city_name" : e.city_name, "polda_id" : e.polda_id, "polda" : e.polda , "polres_id" : e.polres_id, "polres" : e.polres, "polygon" : e.polygon, "prov_id" : e.prov_id})
            })
            closeToast()
            resolve(kota)
        })
    })
}

function get_kecamatan(params) {
    kecamatan = []
    return new Promise(function (resolve, reject) {
        let url_api;
        if (params != 'city_id=') {
            url_api = `http://dev.estrada.co.id:8082/api/v.0.1/kecamatan?${params}`
        }else {
            url_api = `http://dev.estrada.co.id:8082/api/v.0.1/kecamatan`
        }
        getDataApi(url_api, {}, token_sm).then((data) => {
            data.data.forEach((e) => {
                kecamatan.push({ "dis_id" : e.dis_id, "dis_name" : e.dis_name, "polygon" : e.polygon})
            })
            resolve(kecamatan)
        })
    })
}

function get_kelurahan(params) {
    kelurahan = []
    return new Promise(function (resolve, reject) {
        let url_api;
        if (params != 'dis_id=') {
            url_api = `http://dev.estrada.co.id:8082/api/v.0.1/kelurahan?${params}`
        }else {
            url_api = `http://dev.estrada.co.id:8082/api/v.0.1/kelurahan`
        }
        getDataApi(url_api, {}, token_sm).then((data) => {
            data.data.forEach((e) => {
                kelurahan.push({ "subdis_id" : e.subdis_id, "subdis_name" : e.subdis_name, "polygon" : e.polygon})
            })
            resolve(kelurahan)
        })
    })
}

function get_polda(params) {
    polda = []
    return new Promise(function (resolve, reject) {
        // showToast()
        getDataSmNew(`${global_url_api}sm_hrm/polda?${params}`, {}, token_sm).then((data) => {
            data.data.forEach((e) => {
                polda.push({ "rowid" : e.rowid, "polda_id" : e.polda_id, "polda" : e.polda, "prov_id" : e.prov_id})
            })
            // closeToast()
            resolve(polda)
        })
    })
}

function get_polres(params) {
    polres = []
    return new Promise(function (resolve, reject) {
        // showToast()
        getDataSmNew(`${global_url_api}sm_hrm/polres?${params}`, {}, token_sm).then((data) => {
            data.data.forEach((e) => {
                polres.push({ "rowid" : e.rowid, "polres_id" : e.polres_id, "polres" : e.polres,  "polda_id" : e.polda_id, "prov_id" : e.prov_id, "kota_id" : e.kota_id})
            })
            // closeToast()
            resolve(polres)
        })
    })
}

function prom_get_data(url,item="") {
	let f_date_from = $('#f_date_from').val()
	let f_date_to = $('#f_date_to').val()
	let polda_id = $('#polda_id').val()
  	let polres_id = $('#polres_id').val()
	return new Promise(function (resolve, reject) {
		$.ajax({
			type: "POST",
			url: url+item,
			dataType: "json",
			data: {from : f_date_from, to : f_date_to, polda_id : polda_id, polres_id : polres_id},
			success: function (r) {
				resolve(r);
			},
		});
	});
}

function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
      if (!startTimestamp) startTimestamp = timestamp;
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      obj.innerHTML = Math.floor(progress * (end - start) + start);
      if (progress < 1) {
        window.requestAnimationFrame(step);
      }
    };
    window.requestAnimationFrame(step);
  }

function bar_chart(id="",data={},action="render") { 
    var options = {
        series: data.series,
        chart: {
            type: 'bar',
            height: 200
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                dataLabels: {
                    position: 'top',
                },
            },
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val;
            },
            offsetY: -22,
            style: {
                fontSize: '12px',
                colors: ["#304758"]
            }
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: data.categories,
        },
        yaxis: {
            title: {
                text: data.sidetext
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val
                }
            }
        }
    };

    if (action == "render") {
        chart = new ApexCharts(document.querySelector(id), options);
        chart.render();
    }else if(action == "update"){
        chart.updateSeries(data.series);
        chart.updateOptions({
            xaxis: {
                categories: data.categories,
            },
            yaxis: {
                title: {
                    text: data.sidetext
                }
            },
        });
    }
    
    return chart;
}

function pie_chart(id="",data={},action="render") {
    var options = {
        series: data.data,
        chart: {
        width: 380,
        type: 'pie',
      },
      labels: data.name,
      responsive: [{
        breakpoint: 480,
        options: {
          chart: {
            width: 200
          },
          legend: {
            position: 'bottom'
          }
        }
      }]
      };

      if (action == "render") {
            chart = new ApexCharts(document.querySelector(id), options);
            chart.render();
        }else if(action == "update"){
            chart.updateSeries(data.series);
            chart.updateOptions({
                xaxis: {
                    categories: data.categories,
                },
                yaxis: {
                    title: {
                        text: data.sidetext
                    }
                },
            });
        }
        
        return chart;
    }

function line_chart(id="",data={},action="render") { 
    console.log(id);
    console.log(data);
    var options = {
        series: [{
          name: "Desktops",
          data: [10, 41, 35, 51, 49, 62, 69, 91, 148]
      }],
        chart: {
        height: 350,
        type: 'line',
        zoom: {
          enabled: false
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'straight'
      },
      title: {
        text: 'Product Trends by Month',
        align: 'left'
      },
      grid: {
        row: {
          colors: ['#f3f3f3', 'transparent'], // takes an array which will be repeated on columns
          opacity: 0.5
        },
      },
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
      }
    };
    if (action == "render") {
        chart = new ApexCharts(document.querySelector(id), options);
        chart.render();
    }else if(action == "update"){
        chart.updateSeries(data.series);
        chart.updateOptions({
            xaxis: {
                categories: data.categories,
            }
        });
    }
    
    return chart;
}

function capitalizeFirstLetter(str) {

    // converting first letter to uppercase
    const capitalized = str.charAt(0).toUpperCase() + str.slice(1);

    return capitalized;
}

function isoDateToDate(data) {
	let date = new Date(data);
	let year = date.getFullYear();
	let month = date.getMonth()+1;
	let dt = date.getDate();
	
	if (dt < 10) {
	  dt = '0' + dt;
	}
	if (month < 10) {
	  month = '0' + month;
	}

	return year+'-' + month + '-'+dt;
  }

  function isoDateToDateTime(data) {
	let date = new Date(data);
	let year = date.getFullYear();
	let month = date.getMonth()+1;
	let dt = date.getDate();
    const hours = date.getHours();
    const minutes = date.getMinutes();
	
	if (dt < 10) {
	  dt = '0' + dt;
	}
	if (month < 10) {
	  month = '0' + month;
	}

	return year+'-' + month + '-'+dt + ' ' + hours + ':' + minutes;
  }

function showToast() {
	var toastLiveExample = document.getElementById('loadingToast')
    var toast = new bootstrap.Toast(toastLiveExample)
    toast.show()
}

function closeToast() {
    var toastLiveExample = document.getElementById('loadingToast')
    var toast = new bootstrap.Toast(toastLiveExample)
    toast.hide()
}

function current_date() {
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
    var yyyy = today.getFullYear();

    return today = dd + '-' + mm + '-' + yyyy;

}

async function postDataSmNew(url = "", data = {}) {
	// let a = getRToken().then(async r=>{
	//     //Default options are marked with *
	//     if (r.status != true) {
	//         window.location.href = ${global_url_ci}Auth/logout
	//     }else {
	try {
		const response = await fetch(url, {
			method: "POST", // *GET, POST, PUT, DELETE, etc.
			mode: "cors", // no-cors, *cors, same-origin
			cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
			credentials: "same-origin", // include, *same-origin, omit
			headers: {
				"Content-Type": "application/json",
				token: "5b3dac76aaee24d14185cbc3d010fd20",
				// "second_bearer" : r.data.second_bearer
				secondbearer: `${global_sbearer}`,
				// 'Content-Type': 'application/x-www-form-urlencoded',
			},
			redirect: "follow", // manual, *follow, error
			referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
			body: JSON.stringify(data),
		});
		return response.json(); // parses JSON response into native JavaScript objects
	} catch (error) {
		// alert("error")
		console.log(error);
	}
	//     }
	// })
	// return a
}

function convert_timestamp_to_time(unix_timestamp) {
    let date = new Date(unix_timestamp * 1000); // Pastikan timestamp dalam milidetik
    let hours = String(date.getHours()).padStart(2, '0'); // Tambahkan leading zero
    let minutes = String(date.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
}

function line_chart_arus(data, id, bgwarna) {
	var options = {
		chart: {
			height: 200,
			type: "area",
			zoom: {
				enabled: true,
				type: "x", // 'x' for zooming on the x-axis
				autoScaleYaxis: false,
				mouseWheel: {
					enabled: true, // Ensure mouse scroll zoom is enabled
				},
			},
		},
		series: data.series,
		stroke: {
			curve: "smooth",
			colors: [bgwarna],
			width: 0.5,
		},
		dataLabels: {
			enabled: true,
			textAnchor: "top",
			offsetX: -4,
			offsetY: -10,
			style: {
				fontSize: "9px",
				fontWeight: 400,
				colors: ["#ffffff"],
			},
			background: {
				enabled: false,
			},
			formatter: function (value) {
				return value == 0 ? "" : value; // Tampilkan nilai kecuali 0
			},
		},
		markers: {
			size: 1,
			colors: bgwarna,
			strokeColors: bgwarna,
		},
		fill: {
			type: "solid",
		},
		legend: {
			show: true,
			labels: {
				colors: "#FFFFFF", // Set legend text color to white
			},
		},
		colors: ["#00FF00", "#FFFF00", "#FF0000"],
		xaxis: {
			categories: data.categories,
			labels: {
				style: {
					fontSize: "10px",
					fontWeight: 400,
					colors: "#ffffff",
				},
			},
		},
		yaxis: {
			labels: {
				style: {
					colors: "#fff", // Warna font y-axis
				},
			},
		},
	};

  chart = new ApexCharts(document.querySelector(id), options);
	chart.render();
  
  return chart;
}

function line_chart_pemkot(data,id,action="render",category='',bgwarna) {
    // Extracting jenis data for tooltip
    let tooltipContent = ''
    if (category=='grafik_kendaraan') {
      const jenisData = data.jenis.map(item => ({
        cat: item.cat,
        total: item.total,
        minibus: item.minibus,
        bus: item.bus,
        truck: item.truck,
        motor: item.motor
      }));
    
      // Tooltip content
      tooltipContent = jenisData.map(item => `
        <div class="card">
          <div class="card-header">
            <b>${item.cat}</b>
          </div>
          <div class="card-body">
            <p>Minibus : ${item.minibus}</p>
            <p>Bus : ${item.bus}</p>
            <p>Truck : ${item.truck}</p>
            <p>Motor : ${item.motor}</p>
            <p><b>TOTAL : ${item.total}</b></p>
          </div>
        </div>
    
      `);
      
    }else if (category=='grafik_people') {
      const jenisData = data.jenis.map(item => ({
        cat: item.cat,
        total: item.total,
        wanita: item.wanita,
        pria: item.pria
      }));
    
      // Tooltip content
      tooltipContent = jenisData.map(item => `
        <div class="card">
          <div class="card-header">
            <b>${item.cat}</b>
          </div>
          <div class="card-body">
            <p>Wanita : ${item.wanita}</p>
            <p>Pria : ${item.pria}</p>
            <p><b>TOTAL : ${item.total}</b></p>
          </div>
        </div>
    
      `);
    }
  
    const options = {
      series: [{
        name: "TRAFFIC",
        data: data.series
    }],
      chart: {
      height: 200,
      type: 'area',
      zoom: {
        enabled: true
      }
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      colors: ['#00000033'],
    },
    dataLabels: {
      enabled: true,
      textAnchor: 'top',
      offsetX: -4,
      offsetY: -10,
      style: {
        fontSize: '9px',
        fontWeight: 400,
        colors: ['#ffffff']
      },  
      background: {
        enabled: false,
      },
    },
    markers: {
      size: 2,
      colors: bgwarna,
      strokeColors: bgwarna
    },
    fill: {
        colors: [bgwarna], // Warna area chart (dapat menggunakan lebih dari satu warna)
        type: 'gradient', // Tipe pengisian (solid, gradient, pattern)
      },
    // grid: {
    //   row: {
    //     colors: [bgwarna, 'transparent'], // takes an array which will be repeated on columns
    //     opacity: 0.5
    //   },
    // },
    xaxis: {
      categories: data.categories,
      labels: {
        style: {
            fontSize: '9px',
            fontWeight: 400,
            colors: '#ffffff'
        }
      }
    },
    yaxis: {
        labels: {
          style: {
            colors: '#fff', // Warna font y-axis
          }
        }
    },
    tooltip: {
      custom: function({series, seriesIndex, dataPointIndex, w}) {
        return '<div>' + tooltipContent[dataPointIndex] + '</div>';
      }
    }
    };
  
    if (action == "render") {
      chart = new ApexCharts(document.querySelector(id), options);
      chart.render();
    }else if(action == "update"){
        chart.updateSeries([{
          data: data.series
        }],true)
        chart.updateOptions({
            xaxis: {
                categories: data.categories,
            },
            tooltip: {
              custom: function({series, seriesIndex, dataPointIndex, w}) {
                return '<div>' + tooltipContent[dataPointIndex] + '</div>';
              }
            }
        });
    }
    
    return chart;
  
}

function line_chart_vcr(data,id,action="render",bgwarna) {
    const jenisData = data.jenis.map(item => ({
      label: item.label,
      total: item.total,
      rekomendasi: item.rekomendasi,
    }));

    tooltipContent = jenisData.map(item => `
      <div class="card" style="background: #000C1E">
        <div class="card-body text-white">
          <div class="d-flex justify-content-between align-items-center">
            <span>Level Of Service : </span>
            <small>${item.label}</small>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span>VCR : </span>
            <small>${item.total}</small>
          </div>
          <div class="d-flex flex-column">
            <span>Rekomendasi : </span>
            <small class="text-wrap">${item.rekomendasi}</small>
          </div>
        </div>
      </div>
    `);
  
    const options = {
      series: [{
          name: "VCR",
          data: data.series
      }],
      chart: {
        height: 200,
        type: 'area',
        zoom: {
          enabled: true
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        colors: ['#00000033'],
      },
      dataLabels: {
        enabled: true,
        textAnchor: 'top',
        offsetX: -4,
        offsetY: -10,
        style: {
          fontSize: '9px',
          fontWeight: 400,
          colors: ['#ffffff']
        },  
        background: {
          enabled: false,
        },
      },
      markers: {
        size: 2,
        colors: bgwarna,
        strokeColors: bgwarna
      },
      fill: {
        colors: [bgwarna],
        type: 'gradient',
      },
      xaxis: {
        categories: data.categories,
        labels: {
          style: {
              fontSize: '9px',
              fontWeight: 400,
              colors: '#ffffff'
          }
        }
      },
      yaxis: {
          labels: {
            style: {
              colors: '#fff',
            }
          }
      },
      tooltip: {
          theme: 'dark',
          custom: function({ series, seriesIndex, dataPointIndex, w }) {
              return tooltipContent[dataPointIndex] || "<div style='color:white'>Tidak ada data</div>";
          }
      },
      grid: {
          show: false // Menghapus grid dari chart
      }
    };
  
    if (action == "render") {
      chart = new ApexCharts(document.querySelector(id), options);
      chart.render();
    }
    
    return chart;
}

function line_chart_forecast(data, id, action = "render", bgwarna) {
  let tooltipContent = '';
  const jenisData = data.jenis.map(item => ({
    cat: item.cat,
    total: item.total,
    minibus: item.minibus,
    bus: item.bus,
    truck: item.truck,
    motor: item.motor
  }));

  tooltipContent = jenisData.map(item => `
    <div class="card" style="background: #000C1E">
      <div class="card-header text-primary">
        <div class="d-flex justify-content-between">
          <p>${item.cat}</p>
          <p>${item.total}</p>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex">
            <img src="${global_url_ci}/my/satupeta/motor.png" width="30" alt="">
            <small class="mx-3">Sepeda Motor</small>
          </div>
          <small>${item.motor}</small>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex">
            <img src="${global_url_ci}/my/satupeta/mobil_penumpang.png" width="30" alt="">
            <small class="mx-3">Mobil Penumpang</small>
          </div>
          <small>${item.minibus}</small>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex">
            <img src="${global_url_ci}/my/satupeta/bus.png" width="30" alt="">
            <small class="mx-3">Bus</small>
          </div>
          <small>${item.bus}</small>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex">
            <img src="${global_url_ci}/my/satupeta/mobil_barang.png" width="30" alt="">
            <small class="mx-3">Mobil Barang</small>
          </div>
          <small>${item.truck}</small>
        </div>
      </div>
    </div>
  `);

  const realData = [];
  const forecastData = [];
  let lastRealValue = null;

  data.series.forEach(item => {
      if (item.parameter === "REAL") {
          realData.push(item.total);
          forecastData.push(item.total); // Tambahkan ke forecast untuk menyambung
          lastRealValue = item.total;
      } else if (item.parameter === "FORECAST") {
          // realData.push(lastRealValue); // Gunakan nilai terakhir REAL agar nyambung
          forecastData.push(item.total);
      }
  });
  
  const options = {
      series: [
          { name: "REAL DATA", data: realData },
          { name: "FORECAST", data: forecastData }
      ],
      chart: {
          height: 200,
          type: 'line',
          zoom: { enabled: true }
      },
      stroke: {
          curve: 'smooth',
          width: 3,
          dashArray: [0, 5],
          colors: [bgwarna, bgwarna]
      },
      markers: {
          size: 5,
          colors: ['#ffffff'],
          strokeColors: [bgwarna]
      },
      fill: {
          type: 'gradient',
          gradient: {
              shade: 'dark',
              type: 'vertical',
              gradientToColors: [bgwarna, bgwarna],
              stops: [0, 100]
          }
      },
      xaxis: {
          categories: data.categories,
          labels: {
              style: { fontSize: '9px', fontWeight: 400, colors: '#ffffff' }
          }
      },
      yaxis: {
          labels: { style: { colors: '#fff' } }
      },
      tooltip: {
          theme: 'dark',
          custom: function({ series, seriesIndex, dataPointIndex, w }) {
              return tooltipContent[dataPointIndex] || "<div style='color:white'>Tidak ada data</div>";
          }
      },
      legend: {show: false },
      grid: { show: false },
      annotations: {
          xaxis: [
              {
                  x: data.categories[data.series.findIndex(item => item.parameter === "FORECAST")-1 ],
                  x2: data.categories[data.categories.length - 1],
                  fillColor: "#00000088",
                  opacity: 0.8
              }
          ]
      }
  };

  if (action === "render") {
      chart = new ApexCharts(document.querySelector(id), options);
      chart.render();
  }

  return chart;
}

function conut_chart_statement(data, id, action="render") {
  var options = {
    series: data.map(item => item.total),
    chart: {
      width: '100%',
      type: 'donut',
      background: "transparent" // Pastikan background transparan atau warna gelap
    },
    labels: data.map(item => item.label),
    plotOptions: {
      pie: {
        startAngle: -90,
        endAngle: 270,
        donut: {
          size: "70%",
          labels: {
            show: true,
            name: {
              show: true,
              color: "#ffffff" // Warna putih untuk label dalam donut
            },
            value: {
              show: true,
              color: "#ffffff" // Warna putih untuk angka dalam donut
            },
            total: {
              showAlways: true,
              label: "Total",
              show: true,
              color: "#ffffff", // Warna putih untuk teks total
              formatter: function (w) {
                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
              },
            },
          },
        },
      },
    },
    stroke: {
      show: false,
    },
    dataLabels: {
      enabled: false,
      style: {
        colors: ["#ffffff"] // Warna putih untuk teks data labels
      }
    },
    fill: {
      type: "gradient",
    },
    legend: {
      position: "bottom",
      labels: {
        colors: "#ffffff" // Warna putih untuk teks legend
      },
      formatter: function (val, opts) {
        return val + " - " + opts.w.globals.series[opts.seriesIndex];
      },
    },
    responsive: [
      {
        breakpoint: 480,
        options: {
          chart: {
            width: 200,
          },
          legend: {
            position: "bottom",
          },
        },
      },
    ],
  };
  
  if (action == "render") {
    chart = new ApexCharts(document.querySelector(id), options);
    chart.render();
  }
  
  return chart;
  
}

function initMarkerDropAnimationCSS() {
  const style = document.createElement('style');
  style.innerHTML = `
		html,
		body {
			height: 100%;
			margin: 0;
			padding: 0;
		}

		/* set the default transition time */
		:root {
			--delay-time: .5s;
		}

		#map {
			height: 100%;
		}

		#mapContainer {
			height: 100%;
		}

		html,
		body {
			height: 100%;
			margin: 0;
			padding: 0;
		}

		@keyframes drop {
			0% {
				transform: translateY(-200px) scaleY(0.9);
				opacity: 0;
			}
			5% {
				opacity: 0.7;
			}
			50% {
				transform: translateY(0px) scaleY(1);
				opacity: 1;
			}
			65% {
				transform: translateY(-17px) scaleY(0.9);
				opacity: 1;
			}
			75% {
				transform: translateY(-22px) scaleY(0.9);
				opacity: 1;
			}
			100% {
				transform: translateY(0px) scaleY(1);
				opacity: 1;
			}
		}
		.marker-animation {
			animation: drop 0.3s linear forwards var(--delay-time);
		}
  `;

  document.head.appendChild(style);
};

function intersectionObserver() {
	window.intersectionObserver = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
					if (entry.isIntersecting) {
							entry.target.classList.add('marker-animation');
					}
			});
	}, {
			threshold: 0.5
	});
}
