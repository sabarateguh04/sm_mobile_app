<?php

/**
 * Master referensi dropdown (SA: master/referensi, operator: form & filter).
 *
 * key => [table, id_column, name_column, label, group]
 *
 * @see App\Support\ReferensiOptions
 * @see App\Http\Controllers\Master\ReferensiController
 */

return [

    // ── Giat Rutin ──────────────────────────────────────────────────────────
    'jenisops'        => ['t_jenisops',       'id_jenisops',       'namejenisops',       'Jenis Operasi',              'Giat Rutin'],
    'giat'            => ['t_giat',           'id_tgiat',          'namegiat',           'Jenis Kegiatan',             'Giat Rutin'],

    // ── Quick Respons ────────────────────────────────────────────────────────
    'jeniscontigency' => ['t_jeniscontigency','id_jeniscontigency','namecontigency',     'Jenis Contigency',           'Quick Respons'],
    'faktor'          => ['t_faktor',         'id_faktor',         'namefaktor',         'Faktor Gangguan',            'Quick Respons'],
    'peristiwa'       => ['t_peristiwa',      'id_peristiwa',      'nameperistiwa',      'Peristiwa',                  'Quick Respons'],
    'sarpras'         => ['t_sarpras',        'id_sarpras',        'namesarpras',        'Sarpras',                    'Quick Respons'],

    // ── Traffic & Lalin ──────────────────────────────────────────────────────
    'durasi'          => ['t_durasi',         'id_durasi',         'namedurasi',         'Durasi Perlambatan',         'Traffic & Lalin'],
    'situasilalin'    => ['t_situasilalin',   'id_situasilalin',   'namesituasilalin',   'Situasi Lalu Lintas',        'Traffic & Lalin'],
    'situasicuaca'    => ['t_situasicuaca',   'id_situasicuaca',   'namesituasicuaca',   'Situasi Cuaca',              'Traffic & Lalin'],
    'gerbangkota'     => ['t_gerbangkota',    'id_gerbangkota',    'namegerbang',        'Gerbang Kota',               'Traffic & Lalin'],
    'araharus'        => ['t_araharus',       'id_araharus',       'namearah',           'Arah Arus',                  'Traffic & Lalin'],

    // ── Layanan Masyarakat ───────────────────────────────────────────────────
    'saluran'         => ['t_saluran',        'id_saluran',        'namesaluran',        'Saluran Layanan',            'Layanan Masyarakat'],
    'katlayanan'      => ['t_katlayanan',     'id_katlayanan',     'namekatlayanan',     'Kategori Layanan',           'Layanan Masyarakat'],
    'temalayanan'     => ['t_temalayanan',    'id_temalayanan',    'nametema',           'Tema Layanan (per Kategori)', 'Layanan Masyarakat', 'kategori', 'katlayanan'],
    'katlayanandata'  => ['t_katlayanandata', 'id_katlayanandata', 'namekatlayanandata', 'Kategori Layanan Data',      'Layanan Masyarakat'],

    // ── Data Jalan & Terminal ────────────────────────────────────────────────
    'jenisjalan'      => ['t_jenisjalan',     'id_jenisjalan',     'namajenis',          'Jenis Jalan',                'Data Jalan & Terminal'],
    'prasjal'         => ['t_prasjal',        'id_prasjal',        'nameprasjal',        'Prasarana Jalan',            'Data Jalan & Terminal'],
    'levelgangguan'   => ['levelgangguan',    'id_levelgangguan',  'namelevelgangguan',  'Level Gangguan',             'Data Jalan & Terminal'],
    'penyebab'        => ['t_penyebab',       'id_penyebab',       'namepenyebab',       'Penyebab Gangguan',          'Data Jalan & Terminal', 'id_faktor', 'faktor'],
    'jenisterminal'   => ['t_jenisterminal',  'id_jenisterminal',  'namejenisterminal',  'Jenis Terminal',             'Data Jalan & Terminal'],
    'fasilitas'       => ['t_fasilitaspublic','id_fasilitaspublic','namefasilitas',      'Fasilitas Publik',           'Data Jalan & Terminal'],

    // ── Wisata & Ibadah ──────────────────────────────────────────────────────
    'katwisata'       => ['t_katwisata',      'id_katwisata',      'namekatwisata',      'Kategori Wisata',            'Wisata & Ibadah'],
    'kattempatibadah' => ['t_kattempatibadah','id_kattempatibadah','namekategori',       'Kategori Tempat Ibadah',     'Wisata & Ibadah'],
    'katfaskes'       => ['t_katfaskes',      'id_katfaskes',      'namekatfaskes',      'Kategori Faskes',            'Wisata & Ibadah'],

    // ── TIK & Perangkat ──────────────────────────────────────────────────────
    'katperangkat'    => ['t_katperangkat',   'id_katperangkat',   'namekatperangkat',   'Kategori Perangkat IT',      'TIK & Perangkat'],
    'ownercctv'       => ['t_ownercctv',      'id_ownercctv',      'nameowner',          'Owner CCTV',                 'TIK & Perangkat'],

    // ── Media ────────────────────────────────────────────────────────────────
    'kategorikonten'  => ['t_kategorikonten', 'id_kategorikonten', 'namekategori',       'Kategori Konten',            'Media'],
    'metodemedia'     => ['t_metodemedia',    'id_metodemedia',    'namemetode',         'Metode Media',               'Media'],
    'mediapublishing' => ['t_mediapublishing','id_mediapublishing','namemedia',          'Saluran Media Publishing',   'Media'],
    'sentimen'        => ['t_sentimen',       'id_sentimen',       'namesentimen',       'Sentimen Analytic',          'Media'],

];
