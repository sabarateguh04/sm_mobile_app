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
        .image-container img { max-width: 100%; max-height: 360px; border-radius: 6px; border: 1px solid #cbd5e1; padding: 4px; background: #f8fafc; }
 
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
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }}</p>
    </div>

    @if(count($data) == 1)
        @php
            $item = $data[0];
            $fotoPath = null;
            if (!empty($item->foto)) {
                $clean = ltrim($item->foto, '/');
                if (file_exists(public_path($clean))) {
                    $fotoPath = public_path($clean);
                } elseif (file_exists(public_path('storage/' . $clean))) {
                    $fotoPath = public_path('storage/' . $clean);
                } elseif (file_exists(storage_path('app/public/' . $clean))) {
                    $fotoPath = storage_path('app/public/' . $clean);
                }
            }
        @endphp
        <div class="card">
            <table class="grid-table">
                <tbody>
                    <tr>
                        <td>
                            <div class="label">Polda</div>
                            <div class="value-highlight">{{ $item->daerah ? strtoupper($item->daerah) : '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Polres</div>
                            <div class="value">{{ $item->wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->wilayah))) : '—' }}</div>
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
                            <div class="label">Petugas</div>
                            <div class="value">{{ $item->nama_petugas ?? $item->piket ?? '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Pemohon</div>
                            <div class="value">
                                {{ $item->pemohon ?: '—' }}
                                @if(!empty($item->telp_pemohon))
                                    <span style="color: #64748b; font-size: 11px; margin-left: 8px;">({{ $item->telp_pemohon }})</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Tujuan</div>
                            <div class="value">{{ $item->tujuan ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Kategori</div>
                            <div class="value">{{ $item->kategori_data ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Keterangan</div>
                            <div class="value" style="white-space: pre-line;">{{ $item->keterangan ?: '—' }}</div>
                        </td>
                    </tr>
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

            @if($fotoPath)
                <div class="image-section">
                    <div class="image-title">Foto Layanan Data</div>
                    <div class="image-container">
                        <img src="{{ $fotoPath }}" alt="Foto Layanan Data">
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
                    <th style="width: 15%;">Pemohon</th>
                    <th style="width: 15%;">Tujuan</th>
                    <th style="width: 12%;">Kategori</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><span style="color: #d97706; font-weight: bold;">{{ $item->daerah ? strtoupper($item->daerah) : '—' }}</span></td>
                        <td>{{ $item->wilayah ? strtoupper(trim(str_ireplace(['KABUPATEN ', 'KOTA ', 'KAB. '], '', $item->wilayah))) : '—' }}</td>
                        <td>{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</td>
                        <td>
                            {{ $item->pemohon ?: '—' }}
                            @if(!empty($item->telp_pemohon))
                                <br><span style="color: #64748b; font-size: 8px;">{{ $item->telp_pemohon }}</span>
                            @endif
                        </td>
                        <td>{{ $item->tujuan ?: '—' }}</td>
                        <td>{{ $item->kategori_data ?: '—' }}</td>
                        <td>{{ \Str::limit($item->keterangan ?: '—', 100) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
