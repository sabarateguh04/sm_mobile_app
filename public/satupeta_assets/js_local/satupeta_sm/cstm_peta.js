var base_map = 'default'
var hide_label_map = true
var hide_traffic_map = true

let f_date_from
let f_date_to


function get_cstm_peta(){
	if ($('#cstm-content').hasClass('d-none')) {
		$('#cstm-content').removeClass('d-none')
		$('#cstm-content').append(`
		<div class="accordion">
			<div class="accordion-item">
				<h2 class="accordion-header" id="panelsStayOpen-headingOne">
				<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
					MAP SETTING
				</button>
				</h2>
				<div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne">
				<div class="accordion-body">
                    <div class="mb-1">
                        <label for="" class="form-label fw-bold">Batas Wilayah</label>
                        <select name="optwil" id="optwil" class="form-select" onchange="get_wilayah()">
                            <option value="">--Pilih kategori--</option>
                            <option value="prov" >Provinsi</option>
                            <option value="kota" >Kota / Kabupaten</option>
                        </select>
                        <div class="mt-2" id="wil"></div>
                        <div class="mt-2" id="data-wil">
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input me-1 check-kml" name="traffic_layer" onchange="check_traffic_layer()" value="traffic" ${hide_traffic_map == true ? 'checked': ''}>
                        <label class="form-check-label" for="trafficLayer">
                        Traffic Layer
                        </label>
                    </div>
                    <label for="" class="form-label fw-bold mt-2">FILTER DATA</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="f_date_from" class="">Start Date</label>
                                <input type="date" value="${f_date_from != '' ? f_date_from: ''}" class="form-control" id="f_date_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="f_date_to" class="">To Date</label>
                                <input type="date" value="${f_date_to != '' ? f_date_to: ''}" class="form-control" id="f_date_to">
                            </div>
                        </div>
                    </div>
				</div>
				</div>
			</div>
		</div>
		`)
        $('#f_date_from').change(function(){
            f_date_from = `${$(this).val()}`
        })
        $('#f_date_to').change(function(){
            f_date_to = `${$(this).val()}`
        })
	}else{
		$('#cstm-content').html('')
		$('#cstm-content').addClass('d-none')
	}
	
}