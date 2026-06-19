@extends('layouts.satupeta')

@section('content')
<!-- polyfill.io removed - domain compromised, no longer needed for modern browsers -->

<div class="modal fade" id="vipModal" tabindex="-1" aria-labelledby="vipModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="vipModalLabel">VIP</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered table-responsive">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Obyek Pengawalan</th>
              <th>Nama Pejabat</th>
              <th>Dari</th>
              <th>Ke</th>
              <th>Perwira Wasdal</th>
              <th>Anggota 1</th>
              <th>Anggota 2</th>
              <th>Anggota 3</th>
            </tr>
          </thead>
          <tbody id="tbl_detail_vip"></tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal-detail" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl  modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Modal Detail</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered " id="dt-detail" style="width:100%;">
          <thead>
            <tr>
              <td>Jenis Kegiatan</td>
              <td>Kategori Kegiatan</td>
              <td>Nama Jalan</td>
              <td>Penanggung Jawab</td>
              <td>Jumlah Personil</td>
              <td>Tanggal</td>
              <td>Sasaran Giat</td>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- <button type="button" class="btn btn-primary" id="liveToastBtn">Show live toast</button> -->

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
  <div id="loadingToast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        Sedang mengambil data.
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>


<div class="" id='custom-content'>
  <div class="button-logout">
    <a href="javascript:void(0);" onclick="logout_sso('{{ $id_unique }}')"><i class="fa fa-sign-out"></i></a>
  </div>
  <div class="filter_data" id="menu-filter" onclick="fltr()">
    <span>Filter&nbsp;&nbsp;<i class="fa fa-search"></i></span>
  </div>
  <div class="heat_data d-none" id="menu-trfflow" onclick="trfflow()">
    <span>Arus Lalin &nbsp;&nbsp;&nbsp;</span>
    <div id="trff"></div>
  </div>
  <div class="content-filter d-none">
    <div class="card">
      <div class="card-header">
        <span>Filter Date Range</span>
        <span style="font-size: 10px;font-weight: 600;right: 15px;position: absolute; cursor:pointer;" onclick="fltr()">X</span>
      </div>
      <div class="card-body">
        <form action="javascript:void(0);" method="post" id="filter_data">
          <div class="row">
            <div class="col-md-6" style="width:200px">
              <div class="form-group">
                <label for="f_date_from" class="">Start Date</label>
                <input type="date" class="form-control" id="f_date_from">
              </div>
            </div>
            <div class="col-md-6" style="width:200px">
              <div class="form-group">
                <label for="f_date_from" class="">To Date</label>
                <input type="date" class="form-control" id="f_date_to">
              </div>
            </div>
            <input type="hidden" id="user_lat" value="{{ $user_lat }}">
            <input type="hidden" id="user_lng" value="{{ $user_lng }}">
            <input type="hidden" id="polda_id" value="{{ $polda_id }}">
            <input type="hidden" id="polres_id" value="{{ $polres_id }}">
            <input type="hidden" id="prov" value="{{ $provinsi }}">
            <input type="hidden" id="kot" value="{{ $kota }}">
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="jml_data_group" id="jml_data_group">
  </div>

  <div class="weather-panel position-absolute" id="weather-panel" style="top:12px;right:12px;z-index:3;background:rgba(0,0,0,.65);color:#fff;padding:10px 14px;border-radius:8px;font-size:12px;min-width:160px;">
    <div class="fw-bold mb-1">Cuaca</div>
    <div class="mb-2">
      <div id="hari"></div>
      <div class="d-flex align-items-center gap-2">
        <span id="icon"></span>
        <span><span id="cuaca"></span> · <span id="suhunow"></span></span>
      </div>
    </div>
    <div class="mb-2">
      <div id="hari2"></div>
      <div class="d-flex align-items-center gap-2">
        <span id="icon2"></span>
        <span><span id="cuaca2"></span> · <span id="suhunow2"></span></span>
      </div>
    </div>
    <div>
      <div id="hari3"></div>
      <div class="d-flex align-items-center gap-2">
        <span id="icon3"></span>
        <span><span id="cuaca3"></span> · <span id="suhunow3"></span></span>
      </div>
    </div>
  </div>

  <!-- <div class="" style="position: absolute;bottom: 3px;z-index: 1;left:400px;">
    <div class="runtext-container">
      <div class="main-runtext">
        <marquee direction="" onmouseover="this.stop();" onmouseout="this.start();">

          <div class="holder">

            <div class="text-container">
              &nbsp; &nbsp; <img src="my/satupeta/siaga-1.svg"> &nbsp; <a data-fancybox-group="gallery" class="fancybox" href="images/runtext/Electric_Lighting_Act.jpg" title="THE ELECTRIC LIGHTING ACT: section 35">KAPOLRI : Seluruh Jajaran Harus Bersikap Humanis</a> &nbsp; <img src="my/satupeta/siaga-1.svg">
            </div>

          </div>

        </marquee>
      </div>
    </div>
  </div> -->

  <div id="panel-direction-vip" class="d-none"></div>
  <div class="floating-panel" id="menu-panel">
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist" style="cursor:pointer;">
      <!-- <li class="nav-item" role="presentation">
        <a class="nav-link icons text-white" title="E-Search" style="padding: 0.2rem 0.2rem!important;" onclick="esearch()" id="pills-eSearch-tab" data-bs-toggle="pill" data-bs-target="#pills-eSearch" role="tab" aria-controls="pills-eSearch" aria-selected="false"><img src="{{ satupeta_asset('satupeta/search.svg') }}" alt="" style="width:60px;"></a>
      </li> -->
      <!-- <li class="nav-item" role="presentation">
        <a class="nav-link icons text-white" title="Kamseltibcar" style="padding: 0.2rem 0.2rem!important;" onclick="" id="pills-kamseltibcar-tab" data-bs-toggle="pill" data-bs-target="#pills-kamseltibcar" role="tab" aria-controls="pills-kamseltibcar" aria-selected="false"><img src="{{ satupeta_asset('satupeta/kamseltibcar.png') }}" alt="" style="width:60px;"></a>
      </li> -->
      <!-- <li class="nav-item" role="presentation">
        <a class="nav-link icons text-white" title="Pengaduan" style="padding: 0.2rem 0.2rem!important;" onclick="pengaduan()" id="pills-pengaduan-tab" data-bs-toggle="pill" data-bs-target="#pills-pengaduan" role="tab" aria-controls="pills-pengaduan" aria-selected="false"><img src="{{ satupeta_asset('satupeta/location1.png') }}" alt="" style="width:60px;"></a>
      </li> -->
      <li class="nav-item" role="presentation">
        <a class="nav-link icons text-white" title="Petugas" style="padding: 0.2rem 0.2rem!important;" id="pills-petugas-tab" data-bs-toggle="pill" onclick="get_list_petugas()" data-bs-target="#pills-petugas" role="tab" aria-controls="pills-person" aria-selected="false"><img src="{{ satupeta_asset('satupeta/stakeholder.png') }}" alt="" style="width:60px;"></a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link icons text-white" title="Kendaraan" style="padding: 0.2rem 0.2rem!important;" id="pills-car-tab" data-bs-toggle="pill" onclick="get_list_indicar()" data-bs-target="#pills-car" role="tab" aria-controls="pills-car" aria-selected="false"><img src="{{ satupeta_asset('satupeta/car1.png') }}" alt="" style="width:60px;"></a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link icons text-white" title="Lokasi CCTV" style="padding: 0.2rem 0.2rem!important;" onclick="cctv()" id="pills-cctv-tab" data-bs-toggle="pill" data-bs-target="#pills-cctv" role="tab" aria-controls="pills-cctv" aria-selected="false"><img src="{{ satupeta_asset('satupeta/cctv1.png') }}" alt="" style="width:60px;"></a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link icons text-white" title="Mapping" style="padding: 0.2rem 0.2rem!important;" onclick="get_kategori_lokasi()" id="pills-kml-tab" data-bs-toggle="pill" data-bs-target="#pills-kml" role="tab" aria-controls="pills-kml" aria-selected="false"><img src="{{ satupeta_asset('satupeta/layer.png') }}" alt="" style="width:60px;"></a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link icons text-white" title="More" style="padding: 0.2rem 0.2rem!important;" id="pills-more-tab" data-bs-toggle="pill" data-bs-target="#pills-more" role="tab" aria-controls="pills-more" aria-selected="false"><img src="{{ satupeta_asset('satupeta/more.png') }}" alt="" style="width:60px;"></a>
      </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
      <div class="tab-pane panel2 fade" id="pills-cctv" role="tabpanel" aria-labelledby="pills-cctv-tab" style="width:500px;">
        <div class="d-flex">
          <div class="col-md-12 col-sm-12 pl-0" style="overflow:auto;height:320px;">
            <div class="ms-2 row mx-0 border-bottom" style="text-align:left!important;">
              <div class="col-3">
                <input type="checkbox" class="form-check-input" id="select_all_cctv" onchange="check_cctv()"><span class="ms-1">All</span>
              </div>
              <div class="col mb-2">
                <input type="text" class="form-control" placeholder="Cari CCTV" id="search-cctv" onkeyup="search_cctv()">
              </div>
            </div>
            <ul class="text-start list-group list_cctv" id="list_cctv"></ul>
          </div>
        </div>
      </div>
      <div class="tab-pane panel2 fade" id="pills-eSearch" role="tabpanel" aria-labelledby="pills-eSearch-tab">
        <div class="card">
          <div class="card-header">
            <span>E-Search</span>
          </div>
          <div class="card-body text-start">
            <form action="javascript:void(0);" method="post" id="">
              <div class="form-group">
                <label for="" class="">Kategori Pencarian</label>
                <select name="" id="" class="form-control">
                </select>
              </div>
              <div class="d-grid mt-2">
                <button type="submit" class="btn btn-primary">Cari</button>
              </div>
            </form>
          </div>
        </div>
        <!-- <div class="card">
            <div class="card-header"><h5>Icon Kendaraan</h5></div>
            <div class="card-body text-left">
              <div class="row">
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-polisi.png') }}" alt=""> Polisi</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-damkar.png') }}" alt=""> Damkar</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-dishub.png') }}" alt=""> Dishub</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-satpolpp.png') }}" alt=""> Satpol PP</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-ambulan.png') }}" alt=""> Dinkes</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-bpbd.png') }}" alt=""> BPBD</div>
              </div>
            </div>
          </div> -->
      </div>
      <div class="tab-pane panel2 fade" id="pills-car" role="tabpanel" aria-labelledby="pills-car-tab">
        <div class="card">
          <div class="card-header">
            <span>Cari Kendaraan</span>
          </div>
          <div class="card-body text-start">
            <form action="javascript:void(0);" method="post" id="filter_data_kendaraan">
              <div class="form-group">
                <label for="" class="">Nama Kendaraan</label>
                <select name="" id="list_kendaraan" class="form-control">
                </select>
              </div>
              <div class="d-grid mt-2">
                <button type="submit" class="btn btn-primary">Cari</button>
              </div>
            </form>
          </div>
        </div>
        <!-- <div class="card">
            <div class="card-header"><h5>Icon Kendaraan</h5></div>
            <div class="card-body text-left">
              <div class="row">
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-polisi.png') }}" alt=""> Polisi</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-damkar.png') }}" alt=""> Damkar</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-dishub.png') }}" alt=""> Dishub</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-satpolpp.png') }}" alt=""> Satpol PP</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-ambulan.png') }}" alt=""> Dinkes</div>
                <div class="col-4 mb-5"><img src="{{ satupeta_asset('satupeta/car-bpbd.png') }}" alt=""> BPBD</div>
              </div>
            </div>
          </div> -->
      </div>
      <div class="tab-pane panel2 fade" id="pills-petugas" role="tabpanel" aria-labelledby="pills-petugas-tab">
        <div class="card">
          <div class="card-header">
            <span>Cari Petugas</span>
          </div>
          <div class="card-body text-start">
            <form action="javascript:void(0);" method="post" id="filter_data_petugas">
              <div class="form-group">
                <label for="" class="">Nama Petugas</label>
                <select name="" id="list_petugas" class="form-control">
                </select>
              </div>
              <div class="d-grid mt-2">
                <button type="submit" class="btn btn-primary">Cari</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="tab-pane panel2 fade" id="pills-kml" role="tabpanel" aria-labelledby="pills-kml-tab">
        <div class="d-flex">
          <div class="col-md-4 col-sm-4 pr-1">
            <div class="nav flex-column nav-pills" id="v-kategori-kml" role="tablist" aria-orientation="vertical">
            </div>
          </div>
          <div class="col-md-8 col-sm-8 pl-0">
            <div class="tab-content" id="v-pills-tabContent">
              <div class="tab-pane fade" id="v-layers" role="tabpanel" aria-labelledby="v-layers-tab">
                <div class="text-start list_data list_kml">
                  <div class="mb-1">
                    <label for="" class="form-label fw-bold">Base Map</label>
                    <select class="form-select" name="basemap" id="basemap" onchange="change_basemap()" aria-label="Base Map">
                      <option value="default">Default</option>
                      <option value="silver">Silver</option>
                      <option value="night" selected>Night</option>
                      <option value="retro">Retro</option>
                    </select>
                  </div>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input me-1 check-kml" name="hidding_map" id="hidding_map" onchange="hidding_map_features()" value="hidding" checked>
                    <label class="form-check-label" for="hideLabel">
                      Hide Label
                    </label>
                  </div>
                  <div class="form-check">
                    <input type="checkbox" class="form-check-input me-1 check-kml" name="traffic_layer" onchange="check_traffic_layer()" value="traffic">
                    <label class="form-check-label" for="trafficLayer">
                      Traffic Layer
                    </label>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="v-poly" role="tabpanel" aria-labelledby="v-poly-tab">
                <div class="text-start list_data list_kml">
                  <div class="mb-2">
                    <label for="kategoriWilayah" class="form-label fw-bold">Kategori Wilayah</label>
                    <select name="optwil" id="optwil" class="form-select" onchange="get_wilayah()">
                      <option value="">-- Pilih Kategori Wilayah --</option>
                      <option value="prov">Provinsi</option>
                      <option value="kota">Kota / Kabupaten</option>
                      <!-- <option value="kec">Kecamatan</option>
                      <option value="kel">Kelurahan</option> -->
                    </select>
                  </div>

                  <div class="mb-2" id="wil"></div>
                  <div id="data-wil"></div>

                </div>
              </div>
              <div class="tab-pane fade" id="v-kml" role="tabpanel" aria-labelledby="v-kml-tab">
                <div class="ml-2 row mx-0" style="text-align:left!important;">
                  <div class="col-3">
                    <input type="checkbox" class="form-check-input" id="select_all_kml" onchange="check_kml()"><span class="me-1">All</span>
                  </div>
                  <!-- <form action="javascript:void(0);" id="search-cctv"> -->
                  <div class="col">
                    <input type="text" class="form-control" placeholder="Cari" id="search-kml" onkeyup="search_kml()">
                  </div>
                  <!-- </form> -->
                </div>
                <ul class="list-group mt-2 text-start pt-2 border-top list_data list_kml" id="list_kml">
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="tab-pane panel2 fade" id="pills-pengaduan" role="tabpanel" aria-labelledby="pills-pengaduan-tab">
        <div class="d-flex">
          <div class="col-md-12 col-sm-12 pl-0" style="overflow:auto;height:320px;">
            <div class="ms-2 row mx-0 border-bottom" style="text-align:left!important;">
              <div class="col-3">
                <input type="checkbox" class="form-check-input" id="select_all_pengaduan" onchange="check_pengaduan()"><span class="ms-1">All</span>
              </div>
              <div class="col mb-2">
                <input type="text" class="form-control" placeholder="Cari Pengaduan" id="search-pengaduan" onkeyup="search_pengaduan()">
              </div>
            </div>
            <ul class="text-start list-group list_pengaduan" id="list_pengaduan"></ul>
          </div>
        </div>
      </div>
      <div class="tab-pane panel2 fade" id="pills-kamseltibcar" role="tabpanel" aria-labelledby="pills-kamseltibcar-tab">
        <div class="d-flex">
          <div class="col-md-12 col-sm-12 pl-0 text-start" style="overflow:auto;height:400px;">
            <div class="text-center fw-bold mb-2 border-bottom">
              Kinerja Road Safety
            </div>
            <div class="mb-2">
              <label for="kategoriWilayah" class="form-label">Kategori Wilayah</label>
              <select name="optwil2" id="optwil2" class="form-select" onchange="get_wilayah2()">
                <option value="">-- Pilih Kategori Wilayah --</option>
                <option value="prov">Provinsi</option>
                <option value="kota">Kota / Kabupaten</option>
                <!-- <option value="kec">Kecamatan</option>
                    <option value="kel">Kelurahan</option> -->
              </select>
            </div>

            <div class="mb-2" id="wil2"></div>
            <div id="data-wil2"></div>
          </div>
        </div>
      </div>
      <div class="tab-pane fade" id="pills-more" role="tabpanel" aria-labelledby="pills-more-tab">
        <div id="vip" class="panel2 d-none" style="right:60px; z-index:10;">
          <div class="d-flex">
            <div class="col-3">
              <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                <a class="nav-link list d-flex align-items-center border-bottom border-end" id="v-vip-tab" onclick="vip()" data-bs-toggle="pill" href="javascript:void(0);" data-bs-target="#v-vip" role="tab" aria-controls="v-vip" aria-selected="false">
                  <img class="" src="{{ satupeta_asset('satupeta/vvip.png') }}" alt="image" width="30">
                  <div class="wrapper ml-3">
                    <p class="mb-0">
                      Ren VIP</p>
                  </div>
                </a>
              </div>
            </div>
            <div class="col-9">
              <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade" id="v-vip" role="tabpanel" aria-labelledby="v-vip-tab">
                  <div class="ml-2 row mx-0" style="text-align:left!important;">
                    <div class="col-3">
                      <button id="hapusVip" class="btn btn-primary">Clear</button>
                    </div>
                    <div class="col">
                      <input type="text" class="form-control" placeholder="Cari..." id="search-vip" onkeyup="search_vip()">
                    </div>
                  </div>
                  <!-- <div class="mt-2 text-left pt-2 border-top" id="list_vip">
                  </div> -->
                  <ul class="list-group mt-2 pt-2 border-top text-start list_vip" id="list_vip"></ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div id="rawan" class="panel4 d-none" style="right:60px; z-index:10;">
          <ul class="list-group" id="list_kategori_rawan">
          </ul>
        </div>
        <div id="fasum" class="panel4 d-none" style="right:60px; z-index:10; height: 380px; overflow:auto;">
          <div>
            <input type="text" class="form-control" placeholder="Cari..." id="search-fasum" onkeyup="search_fasum()">
          </div>
          <ul class="list-group list_kategori_fasum" id="list_kategori_fasum">
          </ul>
        </div>
        <div id="rengiat" class="panel4 d-none" style="right:60px; z-index:10;">
          <ul class="list-group">
            <li class="list-group-item d-flex align-items-center" onclick="rengiat('PAM Road Savety')">
              <!-- <img class="avatar avatar-md brround" src="{{ satupeta_asset('satupeta/rawan-laka.png') }}" alt="image" style="background-color:white!important;"> -->
              <div class="text-wrap ms-3">
                <p class="mb-0">
                  PAM Road Safety</p>
              </div>
              <div id="rgprs"></div>
            </li>
            <li class="list-group-item d-flex align-items-center" onclick="rengiat('Road Savety Campaign')">
              <!-- <img class="avatar avatar-md brround" src="{{ satupeta_asset('satupeta/rawan-longsor.png') }}" alt="image" style="background-color:white!important;"> -->
              <div class="text-wrap ms-3">
                <p class="mb-0">
                  Road Safety Campaign</p>
              </div>
              <div id="rgrsc"></div>
            </li>
            <li class="list-group-item d-flex align-items-center" onclick="rengiat('Sosialisasi')">
              <!-- <img class="avatar avatar-md brround" src="{{ satupeta_asset('satupeta/rawan-banjir.png') }}" alt="image" style="background-color:white!important;"> -->
              <div class="text-wrap ms-3">
                <p class="mb-0">
                  Sosialisasi</p>
              </div>
              <div id="rgs"></div>
            </li>
            <li class="list-group-item d-flex align-items-center" onclick="rengiat('Publikasi')">
              <!-- <img class="avatar avatar-md brround" src="{{ satupeta_asset('satupeta/rawan-pohontumbang.png') }}" alt="image" style="background-color:white!important;"> -->
              <div class="text-wrap ms-3">
                <p class="mb-0">
                  Publikasi</p>
              </div>
              <div id="rgp"></div>
            </li>
            <li class="list-group-item d-flex align-items-center" onclick="rengiat('Survey')">
              <!-- <img class="avatar avatar-md brround" src="{{ satupeta_asset('satupeta/rawan-tindakpidana.png') }}" alt="image" style="background-color:white!important;"> -->
              <div class="text-wrap ms-3">
                <p class="mb-0">
                  Road Safety Survay</p>
              </div>
              <div id="rgrss"></div>
            </li>
            <a href="javascript:void(0);" onclick="clearAllMarkerRengiat()" class="btn btn-danger">Clear All Rengiat</a>
          </ul>
        </div>
        <div id="mgmt_route" class="panel2 d-none" style="right:60px; height:380px; overflow:auto; z-index:10;">
          <div class="ml-2 row mx-0" style="text-align:left!important;">
            <div class="col-3">
              <button id="hapusMgmtRoute" class="btn btn-primary">Clear</button>
            </div>
            <div class="col">
              <input type="text" class="form-control" placeholder="Cari Route..." id="search_mgmt_route" onkeyup="search_mgmt_route()">
            </div>
          </div>
          <ul class="list-group mt-2 pt-2 border-top text-start list_mgmt_route" id="list_mgmt_route">
          </ul>
        </div>
        <div class="panel3">
          <div class="button_wrap">
            <a class="button_aLeft"><span>VVIP</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('vvip')"><img src="{{ satupeta_asset('satupeta/vvip.png') }}" alt=""></a>
            <div id="cvvip"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Management Route</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('mgmt_route')"><img src="{{ satupeta_asset('satupeta/location2.png') }}" alt=""></a>
            <div id="mr"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Blackspot</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('black_spot')"><img src="{{ satupeta_asset('satupeta/blackspot2.png') }}" alt=""></a>
            <div id="cbs"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Trouble Spot</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('trouble_spot')"><img src="{{ satupeta_asset('satupeta/troublespot2.png') }}" alt=""></a>
            <div id="cts"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Rawan</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('rawan')"><img src="{{ satupeta_asset('satupeta/ambanggangguan1.png') }}" alt=""></a>
            <div id="cr"></div>
          </div>
          <!-- <div class="button_wrap">
            <a class="button_aLeft"><span>Ambang Gangguan</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('ambang_gangguan')"><img src="{{ satupeta_asset('satupeta/ambanggangguan.png') }}" alt=""></a>
            <div id="cag"></div>
          </div> -->
          <div class="button_wrap">
            <a class="button_aLeft"><span>Rengiat Masyarakat</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('rengiat_masyarakat')"><img src="{{ satupeta_asset('satupeta/giatmasyarakat.png') }}" alt=""></a>
            <div id="cgm"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Ren Giat</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('rengiat')"><img src="{{ satupeta_asset('satupeta/rengiat.png') }}" alt=""></a>
            <div id="rg"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Ren Ops Pol</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('renops_pol')"><img src="{{ satupeta_asset('satupeta/renopspol.png') }}" alt=""></a>
            <div id="ro"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Fasilitas Umum</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('fasum')"><img src="{{ satupeta_asset('satupeta/fasum.png') }}" alt=""></a>
            <div id="fsm"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Objek Vital</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('objek_vital')"><img src="{{ satupeta_asset('satupeta/objek-vital2.png') }}" alt=""></a>
            <div id="ov"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Posko</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('posko')"><img src="{{ satupeta_asset('satupeta/posko.png') }}" alt=""></a>
            <div id="pko"></div>
          </div>
          <!-- <div class="button_wrap">
            <a class="button_aLeft"><span>Traffic Counting</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('cctv','traffic_counting')"><img src="{{ satupeta_asset('satupeta/trafficcounting.png') }}" alt=""></a>
            <div id="ctco"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Traffic Category</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('cctv','traffic_category')"><img src="{{ satupeta_asset('satupeta/trafficcategory.png') }}" alt=""></a>
            <div id="ctca"></div>
          </div>
          <div class="button_wrap">
            <a class="button_aLeft"><span>Average Speed</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('cctv','average_speed')"><img src="{{ satupeta_asset('satupeta/avgspeed.png') }}" alt=""></a>
            <div id="cas"></div>
          </div> -->
          <!-- <div class="button_wrap">
            <a class="button_aLeft"><span>Length Ocupantion</span></a>
            <a class="button_bLeft slidebttn" onclick="show_titik('cctv','length_ocupantion')"><img src="{{ satupeta_asset('satupeta/lengthocc.png') }}" alt=""></a>
            <div id="clo"></div>
          </div> -->
          <!-- <div class="button_wrap">
            <a class="button_aLeft"><span>Face Recognation</span></a>
            <a class="button_bLeft slidebttn"><img src="{{ satupeta_asset('satupeta/facerecog.png') }}" alt=""></a>
            <div id="cfr"></div>
          </div> -->
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>
</div>
</div>
<div id="mapid"></div>

@endsection

@push('scripts')
@if (empty($use_osm_map))
<script src="https://cdn.jsdelivr.net/gh/googlemaps/v3-utility-library@07f15d84/markerclustererplus/src/markerclusterer.js"></script>
@if (config('satupeta.google_maps_key'))
<script async src="https://maps.googleapis.com/maps/api/js?key={{ config('satupeta.google_maps_key') }}&libraries=places,visualization,geometry&callback=initMap&v=weekly&loading=async"></script>
@else
<script>
    console.warn('API_KEY_GOOGLE belum di-set — set SATUPETA_MAP_PROVIDER=openstreetmap atau isi API_KEY_GOOGLE.');
    function initMap() {
        var el = document.getElementById('mapid');
        if (!el) return;
        el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;background:#1a1a2e;color:#fff;font-family:Roboto,sans-serif;text-align:center;padding:2rem;"><div><h4>Peta tidak dapat dimuat</h4><p>Set <code>SATUPETA_MAP_PROVIDER=openstreetmap</code> atau <code>API_KEY_GOOGLE</code> di <code>.env</code></p></div></div>';
    }
    document.addEventListener('DOMContentLoaded', initMap);
</script>
@endif
@else
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof initMap === 'function') initMap();
    });
</script>
@endif
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  var id_unique = '{{ $id_unique }}'
  var tber = '{{ $token_bearer }}'
  $(document).ready(function() {
    if (typeof cuaca === 'function') cuaca();
  });

  function cuaca(params) {
    fetch('https://api.openweathermap.org/data/2.5/forecast?lat=-7.5561&cnt=3&lon=110.8317&appid=2d081756be31827a520ced120ddab2ad')
      .then(res => res.json())
      .then(data => {
        var data_cuaca = []
        let cuaca_fullday = data.list;
        for (let index = 0; index < cuaca_fullday.length; index++) {
          data_cuaca.push({
            hari: moment.unix(cuaca_fullday[index].dt).format("DD MMMM, YYYY  HH:mm"),
            cuaca: cuaca_fullday[index].weather[0].main,
            suhu: converKelvintoCelcius(cuaca_fullday[index].main.temp) + " °C",
            icon: `https://openweathermap.org/img/wn/${cuaca_fullday[index].weather[0].icon}@2x.png`,
          })
        }
        if (data_cuaca[0]) {
          $('#hari').append(data_cuaca[0].hari);
          $('#icon').append(`<img style="width:30px; height:30px;" src="${data_cuaca[0].icon}"></img>`);
          $('#cuaca').append(data_cuaca[0].cuaca);
          $('#suhunow').append(data_cuaca[0].suhu);
        }
        if (data_cuaca[1]) {
          $('#hari2').append(data_cuaca[1].hari);
          $('#icon2').append(`<img style="width:30px; height:30px;" src="${data_cuaca[1].icon}"></img>`);
          $('#cuaca2').append(data_cuaca[1].cuaca);
          $('#suhunow2').append(data_cuaca[1].suhu);
        }
        if (data_cuaca[2]) {
          $('#hari3').append(data_cuaca[2].hari);
          $('#icon3').append(`<img style="width:30px; height:30px;" src="${data_cuaca[2].icon}"></img>`);
          $('#cuaca3').append(data_cuaca[2].cuaca);
          $('#suhunow3').append(data_cuaca[2].suhu);
        }
      })
      .catch(function() {});
  }

  function converKelvintoCelcius(params) {
    const rumus = params - 273.15;
    return rumus.toFixed(0);
  }

  function cek_sso(username='') {
    if (typeof getDataSmNew !== 'function') return;
    getDataSmNew(`${global_url_api}sso_check/checksession/${username}`,{},'5b3dac76aaee24d14185cbc3d010fd20').then(r => {
      if (r.status == false) {
        window.location.href = `${global_url_ci}Auth/logout`;
      }
    })
  }
</script>
@endpush
