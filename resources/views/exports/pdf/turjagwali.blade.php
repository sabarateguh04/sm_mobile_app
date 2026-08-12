<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { size: A4 {{ count($data) == 1 ? 'portrait' : 'landscape' }}; margin: {{ count($data) == 1 ? '1.5cm' : '1cm' }}; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #334155; line-height: 1.5; background-color: #ffffff; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
        .header h1 { font-size: 16px; color: #0f172a; margin: 0 0 4px 0; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { font-size: 9px; color: #64748b; margin: 0; }
        
        /* Single Row Stacked Layout */
        .card { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #ffffff; }
        .grid-table { width: 100%; border-collapse: collapse; }
        .grid-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .grid-table tr:last-child td { border-bottom: none; }
        .label { font-size: 8px; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .value { font-size: 10px; color: #1e293b; font-weight: normal; }
        .value-highlight { font-size: 10px; color: #b45309; font-weight: bold; text-transform: uppercase; }
        
        .image-section { margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 15px; page-break-inside: avoid; }
        .image-title { font-size: 9px; font-weight: bold; color: #475569; text-transform: uppercase; margin-bottom: 10px; border-left: 3px solid #2563eb; padding-left: 8px; }
        .image-container { text-align: left; }
        .image-container img { max-width: 100%; max-height: 360px; border-radius: 6px; border: 1px solid #cbd5e1; padding: 4px; background: #f8fafc; margin-right: 10px; margin-bottom: 10px; }
 
        /* Bulk Table Layout */
        .bulk-table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; word-wrap: break-word; }
        .bulk-table th, .bulk-table td { padding: 7px 6px; text-align: left; font-size: 8px; border: 1px solid #cbd5e1; vertical-align: middle; overflow-wrap: break-word; word-break: break-all; }
        .bulk-table th { background-color: #0f172a; font-weight: bold; text-transform: uppercase; color: #ffffff; text-align: center; }
        .bulk-table tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

@php
    if (!function_exists('format_polda_name')) {
        function format_polda_name($val) {
            $val = trim($val);
            if (empty($val) || $val === '—' || $val === '-') return '—';
            $val = strtoupper($val);
            if (!\Illuminate\Support\Str::startsWith($val, 'POLDA')) {
                $val = 'POLDA ' . $val;
            }
            return $val;
        }
    }

    if (!function_exists('format_polres_name')) {
        function format_polres_name($val) {
            $val = trim($val);
            if (empty($val) || $val === '—' || $val === '-') return '—';
            $val = strtoupper($val);
            if (!preg_match('/^(POLRES|POLRESTA|POLRESTABES|POLSEK)/i', $val)) {
                $val = str_ireplace(['KABUPATEN ', 'KAB. '], '', $val);
                $val = 'POLRES ' . $val;
            }
            return $val;
        }
    }
@endphp
    @if(count($data) == 1)
        @php
            $item = $data[0];
            $images = [];
            if (!empty($item->fotogiat)) {
                foreach(explode(',', $item->fotogiat) as $foto) {
                    $f = trim($foto);
                    if ($f === '') continue;
                    if (\Illuminate\Support\Str::startsWith($f, 'http')) {
                        $images[] = $f;
                    } else {
                        $clean = ltrim($f, '/');
                        if (file_exists(public_path($clean))) {
                            $images[] = public_path($clean);
                        } elseif (file_exists(public_path('storage/' . $clean))) {
                            $images[] = public_path('storage/' . $clean);
                        } elseif (file_exists(storage_path('app/public/' . $clean))) {
                            $images[] = storage_path('app/public/' . $clean);
                        } else {
                            $images[] = asset($f);
                        }
                    }
                }
            }
        @endphp
        <div class="card">
            <table class="grid-table">
                <tbody>
                    <tr>
                        <td>
                            <div class="label">Polda</div>
                            <div class="value-highlight">{{ format_polda_name($item->daerah_nama) }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Polres</div>
                            <div class="value">{{ format_polres_name($item->wilayah_nama) }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Tanggal</div>
                            <div class="value">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Kategori Kegiatan</div>
                            <div class="value">{{ $item->katgiat ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Lokasi</div>
                            <div class="value">{{ $item->lokasi ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Peristiwa</div>
                            <div class="value">{{ $item->peristiwa ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Faktor</div>
                            <div class="value">{{ $item->faktor ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Penyebab</div>
                            <div class="value">{{ $item->penyebab ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Petugas Lapangan</div>
                            <div class="value">{{ $item->petugaslap ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Piket / Operator</div>
                            <div class="value">{{ $item->usm_nama ?: '—' }}</div>
                        </td>
                    </tr>
                    @if(!empty($item->ketcb))
                    <tr>
                        <td>
                            <div class="label">Keterangan CB</div>
                            <div class="value" style="white-space: pre-line;">{{ $item->ketcb }}</div>
                        </td>
                    </tr>
                    @endif
                    @if(!empty($item->koordinat))
                    <tr>
                        <td>
                            <div class="label">Koordinat GPS</div>
                            <div class="value">{{ $item->koordinat }}</div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>

            @if(count($images) > 0)
                <div class="image-section">
                    <div class="image-title">Foto Kegiatan</div>
                    <div class="image-container">
                        @foreach($images as $img)
                            <img src="{{ $img }}" alt="Foto Giat">
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @else
        <table class="bulk-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 12%;">Polda</th>
                    <th style="width: 12%;">Polres</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 12%;">Kategori</th>
                    <th style="width: 15%;">Lokasi</th>
                    <th style="width: 15%;">Peristiwa</th>
                    <th>Petugas Lapangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><span style="color: #d97706; font-weight: bold;">{{ format_polda_name($item->daerah_nama) }}</span></td>
                        <td>{{ format_polres_name($item->wilayah_nama) }}</td>
                        <td>{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</td>
                        <td>{{ $item->katgiat ?: '—' }}</td>
                        <td>{{ $item->lokasi ?: '—' }}</td>
                        <td>{{ $item->peristiwa ?: '—' }}</td>
                        <td>{{ $item->petugaslap ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
