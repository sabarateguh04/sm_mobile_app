<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Tiket IT Support #{{ $data->id_itsupport }}</title>
    <style>
        @page { size: A4 portrait; margin: 1.5cm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #334155; line-height: 1.5; background-color: #ffffff; }
        
        /* Header styling */
        .header { margin-bottom: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 12px; }
        .header-title { font-size: 16px; font-weight: bold; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .header-meta { font-size: 9px; color: #64748b; margin-top: 4px; }
        
        /* Card container */
        .card { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #ffffff; }
        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table th { width: 28%; padding: 10px 12px; background-color: #f8fafc; border-bottom: 1px solid #f1f5f9; font-weight: bold; color: #475569; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        .detail-table td { width: 72%; padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 10px; vertical-align: middle; }
        .detail-table tr:last-child th, .detail-table tr:last-child td { border-bottom: none; }
        
        /* Badges */
        .badge { display: inline-block; padding: 3px 8px; font-size: 8px; font-weight: bold; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-tinggi { background-color: #fee2e2; color: #991b1b; }
        .badge-sedang { background-color: #fef3c7; color: #92400e; }
        .badge-rendah { background-color: #dcfce7; color: #166534; }
        .badge-open { background-color: #fef3c7; color: #92400e; }
        .badge-progress { background-color: #dbeafe; color: #1e40af; }
        .badge-closed { background-color: #dcfce7; color: #166534; }
        
        /* Highlights */
        .highlight-blue { color: #2563eb; font-weight: bold; }
        .highlight-amber { color: #b45309; font-weight: bold; text-transform: uppercase; }

        /* Attachment Section */
        .attachment-section { margin-top: 25px; page-break-inside: avoid; }
        .attachment-title { font-size: 10px; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; border-left: 3px solid #2563eb; padding-left: 8px; }
        .attachment-grid { text-align: center; }
        .attachment-img { max-height: 360px; max-width: 100%; border-radius: 6px; border: 1px solid #cbd5e1; padding: 4px; background: #f8fafc; }
    </style>
</head>
<body>

    @php
        $fotoPath = null;
        if (!empty($data->fotokondisi)) {
            if (preg_match('#^https?://#i', $data->fotokondisi)) {
                $fotoPath = $data->fotokondisi;
            } else {
                $cleanPath = ltrim($data->fotokondisi, '/');
                if (str_starts_with($cleanPath, 'storage/')) {
                    $cleanPath = substr($cleanPath, 8);
                }
                if (file_exists(storage_path('app/public/' . $cleanPath))) {
                    $fotoPath = storage_path('app/public/' . $cleanPath);
                } elseif (file_exists(public_path($cleanPath))) {
                    $fotoPath = public_path($cleanPath);
                }
            }
        }
    @endphp

    <div class="header">
        <h1 class="header-title">Detail Tiket IT Support</h1>
        <div class="header-meta">ID Tiket: #{{ $data->id_itsupport }} | Tanggal Cetak: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y H:i') }} WIB</div>
    </div>

    <div class="card">
        <table class="detail-table">
            <tbody>
                <tr>
                    <th>Polda</th>
                    <td class="highlight-amber">{{ $data->petugas_daerah ? strtoupper($data->petugas_daerah) : '—' }}</td>
                </tr>
                <tr>
                    <th>Polres</th>
                    <td class="highlight-blue">{{ $data->petugas_wilayah ? strtoupper($data->petugas_wilayah) : '—' }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>{{ $data->tanggal ? \Carbon\Carbon::parse($data->tanggal)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>Petugas</th>
                    <td>{{ $data->petugas_nama ?: '—' }} (ID Piket: {{ $data->piket ?: '—' }})</td>
                </tr>
                <tr>
                    <th>Kategori Device</th>
                    <td>{{ $data->katdevice ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Nama Device</th>
                    <td>{{ $data->namedevice ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Owner / Pemilik</th>
                    <td>{{ $data->owner ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Lokasi Device</th>
                    <td>{{ $data->lokasidevice ?: '-' }}</td>
                </tr>
                <tr>
                    <th>Tingkat Gangguan</th>
                    <td>
                        @php
                            $tg = strtolower($data->tingkatgangguan ?? '');
                            $tgClass = str_contains($tg, 'tinggi') ? 'badge-tinggi' : (str_contains($tg, 'sedang') ? 'badge-sedang' : 'badge-rendah');
                        @endphp
                        <span class="badge {{ $tgClass }}">{{ $data->tingkatgangguan ?: '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Status Tiket</th>
                    <td>
                        @php
                            $st = strtolower($data->statusticket ?? '');
                            $stClass = (str_contains($st, 'close') || str_contains($st, 'selesai')) ? 'badge-closed' : ((str_contains($st, 'progress') || str_contains($st, 'proses')) ? 'badge-progress' : 'badge-open');
                        @endphp
                        <span class="badge {{ $stClass }}">{{ $data->statusticket ?: '-' }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td style="white-space: pre-wrap;">{{ $data->ketsingkat ?: '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if(!empty($fotoPath))
        <div class="attachment-section">
            <div class="attachment-title">Foto Kondisi</div>
            <div class="attachment-grid">
                <img class="attachment-img" src="{{ $fotoPath }}" alt="Foto Kondisi">
            </div>
        </div>
    @endif

</body>
</html>
