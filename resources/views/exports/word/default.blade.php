<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        h1 { font-size: 14px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background-color: #eee; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Tanggal cetak: {{ date('d/m/Y H:i') }}</p>
    @if(count($data) > 0)
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
                            <td>{{ $arr[$key] ?? '-' }}</td>
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
