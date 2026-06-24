<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { size: A4 landscape; margin: 1cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #333; }
        h1 { font-size: 14px; margin-bottom: 10px; text-align: center; }
        p { text-align: center; margin-top: 0; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; word-wrap: break-word; }
        th, td { border: 1px solid #555; padding: 4px; text-align: left; overflow-wrap: break-word; word-break: break-all; }
        th { background-color: #eee; font-weight: bold; text-align: center; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Tanggal cetak: {{ date('d/m/Y H:i') }}</p>
    @if(count($data) == 1)
        @php
            $first = is_object($data[0]) ? (array) $data[0] : $data[0];
            $images = [];
        @endphp
        <table style="width: 100%; font-size: 10px;">
            <tbody>
                @foreach($first as $key => $val)
                    @php
                        $label = ucwords(str_replace('_', ' ', $key));
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
                                        } else {
                                            $imgSrcs[] = url($v);
                                        }
                                    }
                                }
                            }
                        }
                        
                        if ($isImg && count($imgSrcs) > 0) {
                            $images = array_merge($images, $imgSrcs);
                            continue; // Jangan tampilkan teks URL gambar di tabel
                        }

                        $finalVal = $val === '' || $val === null ? '-' : $val;
                        if (in_array(strtolower($key), ['tanggal', 'tgl', 'date', 'created_at', 'updated_at', 'waktu_kejadian', 'waktu', 'tanggal_kejadian']) && strtotime($finalVal)) {
                            $finalVal = \Carbon\Carbon::parse($finalVal)->locale('id')->translatedFormat('d F Y');
                        }
                    @endphp
                    <tr>
                        <th style="width: 30%; text-align: left; background-color: #f0f0f0;">{{ $label }}</th>
                        <td style="width: 70%;">{{ $finalVal }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($images) > 0)
            <div style="margin-top: 20px; text-align: center;">
                <h3 style="font-size: 12px; margin-bottom: 10px; text-align: left;">Lampiran Foto</h3>
                @foreach($images as $img)
                    <div style="margin-bottom: 15px;">
                        <img src="{{ $img }}" style="max-height: 400px; max-width: 100%; object-fit: contain; border: 1px solid #ddd; padding: 4px;" />
                    </div>
                @endforeach
            </div>
        @endif
    @elseif(count($data) > 1)
        @php
            $first = is_object($data[0]) ? (array) $data[0] : $data[0];
            $headers = array_keys($first);
            $headers = array_map(fn($h) => ucwords(str_replace('_', ' ', $h)), $headers);
        @endphp
        <table>
            <thead>
                <tr>
                    @foreach($headers as $h)
                        <th>{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        @php $arr = is_object($row) ? (array) $row : $row; @endphp
                        @foreach(array_keys($first) as $key)
                            @php 
                                $val = $arr[$key] ?? '-';
                                $isImg = false;
                                $imgSrc = '';
                                if (is_string($val)) {
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
                                            $imgSrc = file_exists(public_path($cleanPath)) ? public_path($cleanPath) : url($checkVal);
                                        }
                                    }
                                }
                                
                                $finalVal = $val === '' || $val === null ? '-' : $val;
                                if (!$isImg && in_array(strtolower($key), ['tanggal', 'tgl', 'date', 'created_at', 'updated_at', 'waktu_kejadian', 'waktu', 'tanggal_kejadian']) && strtotime($finalVal)) {
                                    $finalVal = \Carbon\Carbon::parse($finalVal)->locale('id')->translatedFormat('d F Y');
                                }
                            @endphp
                            <td>
                                @if($isImg)
                                    <img src="{{ $imgSrc }}" style="max-height: 50px; max-width: 50px; object-fit: cover;" />
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
        <p>Tidak ada data.</p>
    @endif
</body>
</html>
