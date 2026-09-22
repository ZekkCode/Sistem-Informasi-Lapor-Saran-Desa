<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Laporan Desa Padelegan</title>
    <style>
        body{margin:32px;color:#17261f;font:13px/1.45 Arial,sans-serif} header{display:flex;justify-content:space-between;gap:24px;border-bottom:2px solid #17362d;padding-bottom:16px} h1{margin:0;font-size:22px} p{margin:4px 0;color:#52635b} table{width:100%;margin-top:20px;border-collapse:collapse;font-size:10px} th,td{border:1px solid #ccd5cf;padding:7px;text-align:left;vertical-align:top} th{background:#eef2ef} .actions{margin:0 0 20px;text-align:right}.actions button{border:0;background:#175f46;color:white;padding:10px 14px;font-weight:700}@media print{body{margin:0}.actions{display:none}@page{size:landscape;margin:12mm}}
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Cetak rekap</button></div>
    <header><div><h1>Rekap Laporan Desa Padelegan</h1><p>Daftar operasional laporan warga</p></div><p>Dibuat {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB<br>{{ $reports->count() }} laporan</p></header>
    <table>
        <caption style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)">Rekap laporan warga Desa Padelegan</caption>
        <thead><tr><th scope="col">Nomor</th><th scope="col">Tanggal</th><th scope="col">Judul</th><th scope="col">Pelapor</th><th scope="col">Dusun</th><th scope="col">Kategori</th><th scope="col">Verifikasi</th><th scope="col">Penanganan</th><th scope="col">Prioritas</th></tr></thead>
        <tbody>
            @forelse($reports as $report)
                <tr><td>{{ $report->report_code }}</td><td>{{ $report->submitted_at->format('d/m/Y H:i') }}</td><td>{{ $report->title }}</td><td>{{ $report->reporter_name }}<br>{{ $report->reporter_phone }}</td><td>{{ $report->dusun->name }}</td><td>{{ $report->subcategory->category->name }}<br>{{ $report->subcategory->name }}</td><td>{{ $report->verification_status->label() }}</td><td>{{ $report->status->label() }}</td><td>{{ $report->admin_priority?->label() ?? $report->citizen_priority->label() }}</td></tr>
            @empty<tr><td colspan="9">Tidak ada laporan untuk filter ini.</td></tr>@endforelse
        </tbody>
    </table>
</body>
</html>
