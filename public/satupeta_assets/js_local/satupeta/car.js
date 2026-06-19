
const createHTMLMapMarker = ({ OverlayView = google.maps.OverlayView,  ...args }) => {
	class HTMLMapMarker extends OverlayView {
	  constructor() {
		super();
		this.latlng = args.latlng;
		this.html = args.html;
		this.setMap(args.map);
	  }
	
	  createDiv() {
		this.div = document.createElement('div');
		this.div.style.position = 'absolute';
		if (this.html) {
		  this.div.innerHTML = this.html;
		}
		google.maps.event.addDomListener(this.div, 'click', event => {
		  google.maps.event.trigger(this, 'click');
		});
	  }
	
	  appendDivToOverlay() {
		const panes = this.getPanes();
		panes.overlayLayer.appendChild(this.div);
	  }
	
	  positionDiv() {
		const point = this.getProjection().fromLatLngToDivPixel(this.latlng);
		if (point) {
		  this.div.style.left = `${point.x}px`;
		  this.div.style.top = `${point.y}px`;
		}
	  }

	  setPosition(position){
		this.latlng_ = position;
		// Position the overlay 
		var point = this.getProjection().fromLatLngToDivPixel(this.latlng_);
		if (point) {
		  this.div.style.left = point.x + 'px';
		  this.div.style.top = point.y + 'px';
		}
	  }
	
	  draw() {
		if (!this.div) {
		  this.createDiv();
		  this.appendDivToOverlay();
		}
		this.positionDiv();
	  }
	
	  remove() {
		if (this.div) {
		  this.div.parentNode.removeChild(this.div);
		  this.div = null;
		}
	  }
	
	  getPosition() {
		return this.latlng;
	  }
	
	  getDraggable() {
		return false;
	  }
	}
	
	  return new HTMLMapMarker();
	};

var indicarKey = [];
var kend_indicar = {
	mobil: [],
	route: [],
};

var marker_tes = [];

$(document).ready(function () {

	getData("./Api_indicar/get_token").then((data) => {
		indicarKey = data;
	});
});

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

async function postData(url = "", data = {}) {
	// Default options are marked with *
	const response = await fetch(url, {
		method: "POST", // *GET, POST, PUT, DELETE, etc.
		mode: "cors", // no-cors, *cors, same-origin
		cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
		credentials: "same-origin", // include, *same-origin, omit
		headers: {
			"Content-Type": "application/json",
			Accept: "application/json",
			Authorization: "Bearer " + indicarKey.indicarToken,
			// 'Content-Type': 'application/x-www-form-urlencoded',
		},
		redirect: "follow", // manual, *follow, error
		referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
		body: JSON.stringify(data), // body data type must match "Content-Type" header
	});
	return response.json(); // parses JSON response into native JavaScript objects
}

async function getData(url = "", data = {}) {
	// Default options are marked with *
	const response = await fetch(url, {
		method: "GET", // *GET, POST, PUT, DELETE, etc.
		mode: "cors", // no-cors, *cors, same-origin
		cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
		credentials: "same-origin", // include, *same-origin, omit
		headers: {
			"Content-Type": "application/json",
			// 'Content-Type': 'application/x-www-form-urlencoded',
		},
		redirect: "follow", // manual, *follow, error
		referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
	});
	return response.json(); // parses JSON response into native JavaScript objects
}

function updateMarker(marker, latitude, longitude, color, angel, vehiclename,vehicleid) {
	var newLatLng = new google.maps.LatLng(latitude, longitude);
	if (typeof newLatLng !== 'undefined') {
		marker.setPosition(newLatLng);
	}

    var icon = marker.getIcon();
	// $(`img[src="${icon.url}"]`).attr('data-car-name',vehiclename)
	// console.log(icon);
    icon.rotation = parseInt(angel);
	// $(`img[vehicle-id="${vehicleid}"]`).css({
    //     'transform': 'rotate('+ angel +'deg)'
    //   });
    marker.setIcon(icon);
	// marker.setRotationAngle(angel);
	// createLabel(marker, vehiclename);
	//  alert(newLatLng);
}

function get_allIndicar() {
	setTimeout(() => {
		setInterval(() => {
			realtime_indicar();
		}, 3000);

		postData(
			"https://indicar.id/platform/public/sysapi/vehicles/list",
			{}
		).then((data) => {
			$('#list_kendaraan').html('');
			$('#list_kendaraan').append(`<option value="">-- Pilih --</option>`);
			data.dataset.forEach((m) => {
				$('#list_kendaraan').append(`
					<option value="${m.nopol}">${m.vehiclename}</option>
				`)
			});

			setTimeout(() => {
				$('#list_kendaraan').select2()
			}, 1000);
		});
	}, 1000);
}

function get_IndicarNopol(nopol='') {
	if (nopol != '') {
		setTimeout(() => {
			setInterval(() => {
				realtime_indicarNopol(nopol);
			}, 3000);
		}, 1000);
	}else{
		setTimeout(() => {
			setInterval(() => {
				realtime_indicarNopol(lks_nopols);
			}, 3000);
		}, 1000);
	}
}


function realtime_indicar() {
	if (kend_indicar.mobil.length < 1) {
		getMakeMarkerIndicar()
			.then((x) => {
				x.mobil.forEach((m) => {
					// alert("ok 1");
					// var vo = $('img[src="'+kend_indicar.mobil[m.vehicleid].icon_url+'"]')[i]
					// $(vo).attr('id_kend',m.vehicleid)
					updateMarker(
						kend_indicar.mobil[m.vehicleid],
						parseFloat(m.latitude),
						parseFloat(m.longitude),
						"#795548",
						m.angel,
						m.vehiclename,
						m.vehicleid
					);
				});
			})
			.catch((err) => {});
	} else {
		var i = 0;
		postData(
			"https://indicar.id/platform/public/sysapi/vehicles/list",
			{}
		).then((data) => {
			data.dataset.forEach((m,i) => {
				// alert("ok 2");
				// var vo = $('img[src="'+kend_indicar.mobil[m.vehicleid].icon_url+'"]')[i]
				// $(vo).attr('id_kend',m.vehicleid)
				updateMarker(
					kend_indicar.mobil[m.vehicleid],
					parseFloat(m.latitude),
					parseFloat(m.longitude),
					"#795548",
					m.angle,
					m.vehiclename,
					m.vehicleid
				);
				// zoomObj = document.getElementById("objectZoom").value;
				// zoomObjSecond = document.getElementById("objectZoomSecond").value;
				// if (zoomObj == m.vehiclename) {
				// 	if (zoomObj != zoomObjSecond) {
				// 		mymap.setView([m.latitude, m.longitude], 18);
				// 	}
				// }
			});
		});
	}
}

function getMakeMarkerIndicar() {
	return new Promise(function (resolve, reject) {
		var marker = [];
		var kend_arr = [];
		var kend_detail = [];
		postData(
			"https://www.indicar.id/platform/public/index.php/sysapi/vehicles/list",
			{}
		).then((data) => {
			data.dataset.forEach((e) => {
				let vehiclegroup = e.vehiclegroup.toLowerCase();
				if (vehiclegroup == "korlantas") {
					// var icn = "./my/satupeta/car-polisi.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#040484',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else if (vehiclegroup == "damkar") {
					// var icn = "./my/satupeta/car-damkar.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#FF0000',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else if (vehiclegroup == "dishub") {
					// var icn = "./my/satupeta/car-dishub.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#8CCCF4',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}	
				} else if (vehiclegroup == "satpolpp") {
					// var icn = "./my/satupeta/car-satpolpp.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#FCBC04',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else if (vehiclegroup == "dinkes") {
					// var icn = "./my/satupeta/car-ambulan.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#FFFFFF',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else if (vehiclegroup == "bpbd") {
					// var icn = "./my/satupeta/car-bpbd.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#FC6404',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else {
					// var icn = "./my/satupeta/car-top-view.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#000000',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				}



				// var icon_tes = {
				// 	path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
				// 	// url : './my/satupeta/POLISI.svg',
				// 	scale: 4,
				// 	strokeColor: '#00F',
				// 	rotation: parseInt(e.angle),
				// }

				// symbol = {  
				// 	path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
				// 	fillColor: '#FF0000',
				// 	fillOpacity: .5,
				// 	anchor: new google.maps.Point(45, 60),
				// 	strokeWeight: 1,
				// 	scale: .5,
				// 	rotation: parseInt(e.angle)  
				// }

                // var myIcon = {
                //     url: icn, // url
                //     scaledSize: new google.maps.Size(20, 20), // scaled size
				// 	// scale: 4,
				// 	rotation : parseInt(e.angle)
                //   };

                  marker = new google.maps.Marker({
                      position: { lat : parseFloat(e.latitude), lng : parseFloat(e.longitude)},
                      map,
                      icon : symbol
                    })

					// marker = createHTMLMapMarker({
					// 	latlng: new google.maps.LatLng(parseFloat(e.latitude), parseFloat(e.longitude)),
					// 	map: map,
					// 	html: `<img vehicle-id="${e.vehicleid}" src="${icn}">`
					// })
					// marker.set("vehicleName", e.vehiclename);
					// marker.set("icon_url", icn);
				

				// createLabel(marker, e.vehiclename);
				kend_arr.push(marker)
				kend_detail.push(e)
				kend_indicar.mobil[e.vehicleid] = marker;
				kend_indicar.route[e.vehicleid] = e.angle;
			});
			
			setInfoWindowMobil(kend_arr, kend_detail)
		});
		resolve(kend_indicar);
	});
}

function realtime_indicarNopol(nop) {
	if (nop != "") {
		get_nop = nop;
	} else {
		get_nop = "";
	}

	if (kend_indicar.mobil.length < 1) {
		getMakeMarkerIndicarNopol(get_nop)
			.then((x) => {
				x.mobil.forEach((m) => {
					// alert("ok 1");
					updateMarker(
						m,
						m.position.lat(),
						m.position.lng(),
						"#795548",
						m.rotation,
						m.vehiclename
					);
				});
			})
			.catch((err) => {});
	} else {
		var i = 0;
		postData(
			"https://www.indicar.id/platform/public/index.php/sysapi/vehicles/detailbynopol",
			{ nopols: get_nop }
		).then((data) => {
			data.dataset.forEach((m) => {
				updateMarker(
					kend_indicar.mobil[m.vehicleid],
					m.latitude,
					m.longitude,
					"#795548",
					m.angle,
					m.vehiclename
				);

				// map.setZoom(15);
				// map.setCenter({ lat : parseFloat(m.latitude), lng : parseFloat(m.longitude) });
				
				// zoomInOut = document.getElementById("zoomInOut").value;
				// zoomObj = document.getElementById("objectZoom").value;
				// zoomObjSecond = document.getElementById("objectZoomSecond").value;
				// if (zoomObj == m.vehiclename) {
				// 	// alert(zoomInOut);
				// 	if (zoomObj != zoomObjSecond) {
				// 		mymap.setView([m.latitude, m.longitude], 18);
				// 	}
				// }
				// if (zoomObj == "") {
				// 	mymap.setView([m.latitude, m.longitude], 18);
				// }
				// alert(zoomObj);
				//
			});
		});
	}
}

function getMakeMarkerIndicarNopol(nop) {
	return new Promise(function (resolve, reject) {
		var marker = {};
		var kend_arr = [];
		var kend_detail = [];
		postData(
			"https://www.indicar.id/platform/public/index.php/sysapi/vehicles/detailbynopol",
			{ nopols: nop }
		).then((data) => {
			data.dataset.forEach((e) => {
				let vehiclegroup = e.vehiclegroup.toLowerCase();
				if (vehiclegroup == "korlantas") {
					// var icn = "./my/satupeta/car-polisi.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#040484',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else if (vehiclegroup == "damkar") {
					// var icn = "./my/satupeta/car-damkar.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#FF0000',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else if (vehiclegroup == "dishub") {
					// var icn = "./my/satupeta/car-dishub.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#8CCCF4',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}	
				} else if (vehiclegroup == "satpolpp") {
					// var icn = "./my/satupeta/car-satpolpp.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#FCBC04',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else if (vehiclegroup == "dinkes") {
					// var icn = "./my/satupeta/car-ambulan.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#FFFFFF',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else if (vehiclegroup == "bpbd") {
					// var icn = "./my/satupeta/car-bpbd.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#FC6404',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				} else {
					// var icn = "./my/satupeta/car-top-view.png";
					symbol = {  
						path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
						fillColor: '#000000',
						fillOpacity: 1,
						anchor: new google.maps.Point(45, 60),
						strokeWeight: 1,
						scale: .5,
						rotation: parseInt(e.angle)  
					}
				}

				// symbol = {  
				// 	path: "M62.1,36.5c-0.9-1.2-3.6-1.5-3.6-1.5c0.1-3.5,0.5-6.9,0.7-8.2c1.9-7.3-1.7-11.8-1.7-11.8c-4.8-4.8-9.1-5-12.5-5   c-3.4,0-7.8,0.3-12.5,5c0,0-3.6,4.5-1.7,11.8c0.2,1.2,0.5,4.6,0.7,8.2c0,0-2.7,0.3-3.6,1.5c-0.9,1.2-0.9,1.9,0,1.9   c0.9,0,2.9-2.3,3.6-2.3V35c0,1,0.1,2,0.1,3c0,4.4,0,33.7,0,33.7s-0.3,6.1,5,7.8c1.2,0,4.6,0.4,8.4,0.5c3.8-0.1,7.3-0.5,8.4-0.5   c5.3-1.7,5-7.8,5-7.8s0-29.3,0-33.7c0-1,0-2,0.1-3v1.2c0.7,0,2.7,2.3,3.6,2.3C63,38.5,63,37.7,62.1,36.5z M34.7,66.5   c-0.3,3.3-2.3,4.1-2.3,4.1V37.4c0.8,1.2,2.3,6.8,2.3,6.8S34.9,63.2,34.7,66.5z M54.8,75.2c0,0-4.2,2.3-9.8,2.2   c-5.6,0.1-9.8-2.2-9.8-2.2v-2.8c4.9,2.2,9.8,2.2,9.8,2.2s4.9,0,9.8-2.2V75.2z M35.2,41.1l-1.7-10.2c0,0,4.5-3.2,11.5-3.2   s11.5,3.2,11.5,3.2l-1.7,10.2C51.4,39.2,38.6,39.2,35.2,41.1z M57.7,70.6c0,0-2.1-0.8-2.3-4.1c-0.3-3.3,0-22.4,0-22.4   s1.5-5.6,2.3-6.8V70.6z",
				// 	fillColor: '#FF0000',
				// 	fillOpacity: .5,
				// 	anchor: new google.maps.Point(45, 60),
				// 	strokeWeight: 1,
				// 	scale: .5,
				// 	rotation: parseInt(e.angle)  
				// }

				// var myIcon = {
                //     url: icn, // url
                //     scaledSize: new google.maps.Size(20, 20), // scaled size
				// 	// scale: 4,
				// 	rotation : parseInt(e.angle)
                //   };

				marker = new google.maps.Marker({
					position: { lat : parseFloat(e.latitude), lng : parseFloat(e.longitude)},
					map,
					icon : symbol
				  })

				// createLabel(marker, e.vehiclename);
				kend_arr.push(marker)
				kend_detail.push(e)
				kend_indicar.mobil[e.vehicleid] = marker;
				kend_indicar.route[e.vehicleid] = e.angle;
			});

			setInfoWindowMobil(kend_arr, kend_detail)
			resolve(kend_indicar);
		});
	});
}

function setInfoWindowMobil(marker = "", kend_detail = '') {
	const infowindow = new google.maps.InfoWindow(); // Only one InfoWindow
	infowindow.close(); // Close previously opened infowindow
	return new Promise(function (resolve, reject) {
		for (let i = 0; i < kend_detail.length; i++) {
			google.maps.event.addListener(marker[i], 'click', function(){
				map.setZoom(15);
				map.setCenter({ lat : parseFloat(kend_detail[i].latitude), lng : parseFloat(kend_detail[i].longitude) });
				infowindow.setContent(`
				<div class="mt-3">
				<table class="w-100">
				<tr>
				<td><b>Nama Kendaraan</b></td>
				<td>:</td>
				<td>${kend_detail[i].vehiclename}</td>
				</tr>
				<tr>
				<td><b>Nopol</b></td>
				<td>:</td>
				<td>${kend_detail[i].nopol}</td>
				</tr>
				</table>
				</div>
				<div class="row" style="margin-right:0 !important;">
				</div>
				</div>
				`);
				infowindow.open(map, marker[i]);
			});
		}

	});
	
}

let lks_nopols = GetURLParameter("nopol");
var urlParameter = {
	nopols: lks_nopols,
};

$(function () {
	// console.clear();
	if (urlParameter["nopols"] != undefined && urlParameter["nopols"] != "") {
		get_IndicarNopol();
	}
	if (urlParameter["nopols"] != undefined && urlParameter["nopols"] == "") {
		get_allIndicar();
	}

	// get_allIndicar();
	$('#filter_data_kendaraan').submit(function (x) { 
		x.preventDefault();
		let nopol = $('#list_kendaraan').val()
		get_IndicarNopol(nopol);
	});
});