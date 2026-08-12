<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { size: A4 {{ count($data) == 1 ? 'portrait' : 'landscape' }}; margin: {{ count($data) == 1 ? '1.5cm' : '1cm' }}; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9px; color: #334155; line-height: 1.5; background-color: #ffffff; }
        
        /* Header styling */
        .header { margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
        .header-title { font-size: 16px; font-weight: bold; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .header-meta { font-size: 9px; color: #64748b; margin-top: 4px; }
        
        /* Individual Detail View (Single Card Stacked) */
        .card { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #ffffff; }
        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table td { padding: 10px 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .detail-table tr:last-child td { border-bottom: none; }
        .label { font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .value { font-size: 11px; color: #0f172a; font-weight: normal; }
        .value-highlight { font-size: 11px; color: #b45309; font-weight: bold; text-transform: uppercase; }
        
        /* Image Attachment Section */
        .attachment-section { margin-top: 25px; page-break-inside: avoid; }
        .attachment-title { font-size: 10px; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; border-left: 3px solid #2563eb; padding-left: 8px; }
        .attachment-grid { text-align: center; }
        .attachment-img { max-height: 360px; max-width: 100%; border-radius: 6px; border: 1px solid #cbd5e1; padding: 4px; background: #f8fafc; }

        /* Bulk Table View */
        .bulk-table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; word-wrap: break-word; }
        .bulk-table th { background-color: #0f172a; color: #ffffff; font-weight: bold; text-transform: uppercase; font-size: 8px; letter-spacing: 0.5px; padding: 8px 6px; border: 1px solid #1e293b; text-align: center; }
        .bulk-table td { padding: 7px 6px; border: 1px solid #e2e8f0; color: #334155; font-size: 8px; vertical-align: middle; overflow-wrap: break-word; word-break: break-all; }
        .bulk-table tr:nth-child(even) td { background-color: #f8fafc; }
        .bulk-img { max-height: 40px; max-width: 40px; border-radius: 4px; border: 1px solid #cbd5e1; object-fit: cover; }
        
        .no-data { text-align: center; padding: 30px; color: #64748b; font-size: 11px; border: 1px dashed #cbd5e1; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="header-title">{{ $title }}</h1>
        <div class="header-meta">Tanggal cetak: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y H:i') }} WIB</div>
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
            $first = is_object($data[0]) ? (array) $data[0] : $data[0];
            $images = [];
        @endphp
        <div class="card">
            <table class="detail-table">
                <tbody>
                    @foreach($first as $key => $val)
                        @php
                            $keyLower = strtolower($key);
                            // Skip internal database and system keys
                            if (
                                in_array($keyLower, ['id', 'sm_lv', 'piket', 'operator_edited_by', 'operator_edited_at', 'id_profileposko', 'created_at', 'updated_at', 'deleted_at']) ||
                                \Illuminate\Support\Str::startsWith($keyLower, 'id_') ||
                                \Illuminate\Support\Str::endsWith($keyLower, '_id')
                            ) {
                                continue;
                            }

                            $labelMap = [
                                'usm_nama' => 'Piket / Operator',
                                'daerah' => 'Polda',
                                'daerah_nama' => 'Polda',
                                'wilayah' => 'Polres',
                                'wilayah_nama' => 'Polres',
                                'ketcb' => 'Keterangan CB',
                                'katgiat' => 'Kategori Kegiatan',
                                'petugaslap' => 'Petugas Lapangan',
                            ];
                            $label = $labelMap[$keyLower] ?? ucwords(str_replace('_', ' ', $key));
                            $isImg = false;
                            $imgSrcs = [];
                            if (is_string($val) && $val !== '') {
                                foreach(explode(',', $val) as $v) {
                                    $v = trim($v);
                                    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $v) && !preg_match('/\s/', $v)) {
                                        $isImg = true;
                                        if (\Illuminate\Support\Str::startsWith($v, 'http')) {
                                            $imgSrcs[] = $v;
                                        } else {
                                            $cleanPath = ltrim($v, '/');
                                            if (file_exists(public_path($cleanPath))) {
                                                $imgSrcs[] = public_path($cleanPath);
                                            } elseif (file_exists(public_path('storage/' . $cleanPath))) {
                                                $imgSrcs[] = public_path('storage/' . $cleanPath);
                                            } elseif (file_exists(storage_path('app/public/' . $cleanPath))) {
                                                $imgSrcs[] = storage_path('app/public/' . $cleanPath);
                                            } else {
                                                $imgSrcs[] = url($v);
                                            }
                                        }
                                    }
                                }
                            }
                            
                            if ($isImg && count($imgSrcs) > 0) {
                                $images = array_merge($images, $imgSrcs);
                                continue;
                            }

                            $finalVal = $val === '' || $val === null ? '-' : $val;
                            if (in_array(strtolower($key), ['tanggal', 'tgl', 'date', 'created_at', 'updated_at', 'waktu_kejadian', 'waktu', 'tanggal_kejadian']) && strtotime($finalVal)) {
                                $finalVal = \Carbon\Carbon::parse($finalVal)->locale('id')->translatedFormat('d F Y');
                            }

                            // Clean up Polda / Polres values if needed
                            $valClass = 'value';
                            if (in_array(strtolower($key), ['daerah', 'polda', 'petugas_daerah', 'daerah_nama'])) {
                                $valClass = 'value-highlight';
                                $finalVal = format_polda_name($finalVal);
                            } elseif (in_array(strtolower($key), ['wilayah', 'polres', 'petugas_wilayah', 'wilayah_nama'])) {
                                $valClass = 'value';
                                $finalVal = format_polres_name($finalVal);
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="label">{{ $label }}</div>
                                <div class="{{ $valClass }}">{{ $finalVal }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(count($images) > 0)
            <div class="attachment-section">
                <div class="attachment-title">Lampiran Foto</div>
                <div class="attachment-grid">
                    @foreach($images as $img)
                        <div style="margin-bottom: 15px;">
                            <img class="attachment-img" src="{{ $img }}" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @elseif(count($data) > 1)
        @php
            $first = is_object($data[0]) ? (array) $data[0] : $data[0];
            $filteredKeys = [];
            foreach (array_keys($first) as $key) {
                $keyLower = strtolower($key);
                if (
                    in_array($keyLower, ['id', 'sm_lv', 'piket', 'operator_edited_by', 'operator_edited_at', 'id_profileposko', 'created_at', 'updated_at', 'deleted_at']) ||
                    \Illuminate\Support\Str::startsWith($keyLower, 'id_') ||
                    \Illuminate\Support\Str::endsWith($keyLower, '_id')
                ) {
                    continue;
                }
                $filteredKeys[] = $key;
            }
            
            $labelMap = [
                'usm_nama' => 'Piket / Operator',
                'daerah_nama' => 'Polda',
                'wilayah_nama' => 'Polres',
                'ketcb' => 'Keterangan CB',
                'katgiat' => 'Kategori Kegiatan',
                'petugaslap' => 'Petugas Lapangan',
            ];
        @endphp
        <table class="bulk-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    @foreach($filteredKeys as $key)
                        @php
                            $hLabel = $labelMap[strtolower($key)] ?? ucwords(str_replace('_', ' ', $key));
                        @endphp
                        <th>{{ $hLabel }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $idx => $row)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        @php $arr = is_object($row) ? (array) $row : $row; @endphp
                        @foreach($filteredKeys as $key)
                            @php 
                                $val = $arr[$key] ?? '-';
                                $isImg = false;
                                $imgSrc = '';
                                if (is_string($val) && $val !== '') {
                                    $checkVal = $val;
                                    if (str_contains($val, ',')) {
                                        $checkVal = explode(',', $val)[0];
                                    }
                                    if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $checkVal) && !preg_match('/\s/', $checkVal)) {
                                        $isImg = true;
                                        if (\Illuminate\Support\Str::startsWith($checkVal, 'http')) {
                                            $imgSrc = $checkVal;
                                        } else {
                                            $cleanPath = ltrim($checkVal, '/');
                                            if (file_exists(public_path($cleanPath))) {
                                                $imgSrc = public_path($cleanPath);
                                            } elseif (file_exists(public_path('storage/' . $cleanPath))) {
                                                $imgSrc = public_path('storage/' . $cleanPath);
                                            } elseif (file_exists(storage_path('app/public/' . $cleanPath))) {
                                                $imgSrc = storage_path('app/public/' . $cleanPath);
                                            } else {
                                                $imgSrc = url($checkVal);
                                            }
                                        }
                                    }
                                }
                                
                                $finalVal = $val === '' || $val === null ? '-' : $val;
                                if (!$isImg && in_array(strtolower($key), ['tanggal', 'tgl', 'date', 'created_at', 'updated_at', 'waktu_kejadian', 'waktu', 'tanggal_kejadian']) && strtotime($finalVal)) {
                                    $finalVal = \Carbon\Carbon::parse($finalVal)->locale('id')->translatedFormat('d F Y');
                                }

                                $tdClass = '';
                                if (in_array(strtolower($key), ['daerah', 'polda', 'petugas_daerah', 'daerah_nama'])) {
                                    $tdClass = 'class="value-highlight" style="color: #d97706; font-weight: bold;"';
                                    $finalVal = format_polda_name($finalVal);
                                } elseif (in_array(strtolower($key), ['wilayah', 'polres', 'petugas_wilayah', 'wilayah_nama'])) {
                                    $finalVal = format_polres_name($finalVal);
                                }
                            @endphp
                            <td {!! $tdClass !!}>
                                @if($isImg)
                                    <img class="bulk-img" src="{{ $imgSrc }}" />
                                @else
                                    {{ $finalVal }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">Tidak ada data untuk ditampilkan.</div>
    @endif
</body>
</html>
