<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Usulan Warga Desa Padelegan</title>
    <style>
        body{margin:32px;color:#17261f;font:13px/1.45 Arial,sans-serif} header{display:flex;justify-content:space-between;gap:24px;border-bottom:2px solid #17362d;padding-bottom:16px} h1{margin:0;font-size:22px} p{margin:4px 0;color:#52635b} table{width:100%;margin-top:20px;border-collapse:collapse;font-size:10px} th,td{border:1px solid #ccd5cf;padding:7px;text-align:left;vertical-align:top} th{background:#eef2ef} .actions{margin:0 0 20px;text-align:right}.actions button{border:0;background:#175f46;color:white;padding:10px 14px;font-weight:700}@media print{body{margin:0}.actions{display:none}@page{size:landscape;margin:12mm}}
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Cetak rekap</button></div>
    <header><div><h1>Rekap Usulan Warga Desa Padelegan</h1><p>Daftar operasional usulan dan aspirasi warga</p></div><p>Dibuat {{ $generatedAt->translatedFormat('d F Y, H:i') }} WIB<br>{{ $daftarUsulan->count() }} usulan</p></header>
    <table>
        <caption style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)">Rekap usulan warga Desa Padelegan</caption>
        <thead><tr><th scope="col">Nomor</th><th scope="col">Tanggal</th><th scope="col">Judul</th><th scope="col">Pengusul</th><th scope="col">Dusun</th><th scope="col">Jenis</th><th scope="col">Status</th><th scope="col">Publik</th></tr></thead>
        <tbody>
            @forelse($daftarUsulan as $usulan)
                <tr><td>{{ $usulan->kode }}</td><td>{{ $usulan->dikirim_pada->format('d/m/Y H:i') }}</td><td>{{ $usulan->judul }}</td><td>{{ $usulan->nama_pengusul ?: 'Anonim' }}<br>{{ $usulan->telepon_pengusul ?: '-' }}</td><td>{{ $usulan->dusun->name }}</td><td>{{ $usulan->jenis->label() }}</td><td>{{ $usulan->status->label() }}</td><td>{{ $usulan->tampil_publik ? 'Ditampilkan' : 'Tersembunyi' }}</td></tr>
            @empty<tr><td colspan="8">Tidak ada usulan untuk filter ini.</td></tr>@endforelse
        </tbody>
    </table>
</body>
</html>
