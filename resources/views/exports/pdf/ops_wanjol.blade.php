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
        @endphp
        <div class="card">
            <table class="grid-table">
                <tbody>
                    <tr>
                        <td>
                            <div class="label">Polda</div>
                            <div class="value-highlight">{{ format_polda_name($item->daerah) }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Polres</div>
                            <div class="value">{{ format_polres_name($item->wilayah) }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Nama Posko</div>
                            <div class="value">{{ $item->posko_nama ?: '—' }}</div>
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
                            <div class="label">Lokasi Peristiwa</div>
                            <div class="value">{{ $item->lokasi ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Uraian Peristiwa</div>
                            <div class="value" style="white-space: pre-line;">{{ $item->uraian ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Status</div>
                            <div class="value">{{ $item->status ?: '—' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="label">Piket / Operator</div>
                            <div class="value">{{ $item->usm_nama ?: '—' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @else
        <table class="bulk-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 12%;">Polda</th>
                    <th style="width: 12%;">Polres</th>
                    <th style="width: 15%;">Posko</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 18%;">Lokasi</th>
                    <th>Uraian Peristiwa</th>
                    <th style="width: 12%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><span style="color: #d97706; font-weight: bold;">{{ format_polda_name($item->daerah) }}</span></td>
                        <td>{{ format_polres_name($item->wilayah) }}</td>
                        <td>{{ $item->posko_nama ?: '—' }}</td>
                        <td>{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') : '—' }}</td>
                        <td>{{ $item->lokasi ?: '—' }}</td>
                        <td>{{ \Str::limit($item->uraian ?: '—', 150) }}</td>
                        <td>{{ $item->status ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
