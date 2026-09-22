@extends('layouts.panel-petugas')

@section('title', 'Data Master')
@section('page-title', 'Data master')
@section('eyebrow', 'Konfigurasi layanan')

@section('content')
    <p class="admin-intro">Kelola pilihan yang dipakai pada formulir warga. Data lama tidak dihapus; nonaktifkan item agar riwayat laporan tetap utuh.</p>

    <div x-data="{ tab: 'dusun' }">
        <nav class="admin-tabs" aria-label="Jenis data master">
            <button type="button" @click="tab = 'dusun'" :class="tab === 'dusun' ? 'is-active' : ''">Dusun ({{ $dusuns->count() }})</button>
            <button type="button" @click="tab = 'kategori'" :class="tab === 'kategori' ? 'is-active' : ''">Kategori ({{ $categories->count() }})</button>
            <button type="button" @click="tab = 'jenis'" :class="tab === 'jenis' ? 'is-active' : ''">Jenis masalah ({{ $subcategories->count() }})</button>
            <button type="button" @click="tab = 'qr'" :class="tab === 'qr' ? 'is-active' : ''">Sumber QR ({{ $qrSources->count() }})</button>
        </nav>

        <section x-show="tab === 'dusun'" x-cloak class="admin-manage-layout">
            <form action="{{ route('admin.master.dusuns.store') }}" method="POST" class="admin-panel admin-panel__body admin-form-grid" data-submit-once>
                @csrf
                <h2 class="admin-section-title field--full">Tambah dusun</h2>
                <div class="field"><label for="dusun-code" class="field__label">Kode</label><input id="dusun-code" name="code" class="control" maxlength="20" placeholder="BKL" required></div>
                <div class="field"><label for="dusun-name" class="field__label">Nama dusun</label><input id="dusun-name" name="name" class="control" maxlength="120" required></div>
                <div class="field field--full"><label for="dusun-description" class="field__label">Keterangan <span class="font-normal text-muted">(opsional)</span></label><textarea id="dusun-description" name="description" class="control min-h-24" maxlength="1000"></textarea></div>
                <input type="hidden" name="is_active" value="1">
                <div class="admin-form-actions"><button class="action action--primary" type="submit">Tambah dusun</button></div>
            </form>
            <div class="admin-edit-list">
                @foreach($dusuns as $dusun)
                    <details class="admin-edit-row">
                        <summary><span>{{ $dusun->name }} <span class="admin-edit-meta">{{ $dusun->code }} · {{ $dusun->reports_count }} laporan · {{ $dusun->is_active ? 'Aktif' : 'Nonaktif' }}</span></span></summary>
                        <form action="{{ route('admin.master.dusuns.update', $dusun) }}" method="POST" class="admin-form-grid" data-submit-once>
                            @csrf @method('PATCH')
                            <div class="field"><label class="field__label" for="dusun-code-{{ $dusun->id }}">Kode</label><input id="dusun-code-{{ $dusun->id }}" name="code" class="control" value="{{ $dusun->code }}" required></div>
                            <div class="field"><label class="field__label" for="dusun-name-{{ $dusun->id }}">Nama</label><input id="dusun-name-{{ $dusun->id }}" name="name" class="control" value="{{ $dusun->name }}" required></div>
                            <div class="field field--full"><label class="field__label" for="dusun-description-{{ $dusun->id }}">Keterangan</label><textarea id="dusun-description-{{ $dusun->id }}" name="description" class="control min-h-20">{{ $dusun->description }}</textarea></div>
                            <label class="field--full flex min-h-12 items-center gap-3 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="size-4 accent-padelegan-600" @checked($dusun->is_active)> Tampilkan di formulir warga</label>
                            <div class="admin-form-actions"><button type="submit" class="action action--primary action--sm">Simpan</button></div>
                        </form>
                    </details>
                @endforeach
            </div>
        </section>

        <section x-show="tab === 'kategori'" x-cloak class="admin-manage-layout">
            <form action="{{ route('admin.master.categories.store') }}" method="POST" class="admin-panel admin-panel__body admin-form-grid" data-submit-once>
                @csrf
                <h2 class="admin-section-title field--full">Tambah kategori</h2>
                <div class="field field--full"><label for="category-name" class="field__label">Nama kategori</label><input id="category-name" name="name" class="control" maxlength="100" required></div>
                <div class="field"><label for="category-order" class="field__label">Urutan</label><input id="category-order" name="sort_order" type="number" class="control" value="{{ $categories->count() + 1 }}" min="0" max="999" required></div>
                <input type="hidden" name="is_active" value="1">
                <div class="admin-form-actions"><button class="action action--primary" type="submit">Tambah kategori</button></div>
            </form>
            <div class="admin-edit-list">
                @foreach($categories as $category)
                    <details class="admin-edit-row">
                        <summary><span>{{ $category->name }} <span class="admin-edit-meta">{{ $category->subcategories_count }} jenis · urutan {{ $category->sort_order }} · {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span></span></summary>
                        <form action="{{ route('admin.master.categories.update', $category) }}" method="POST" class="admin-form-grid" data-submit-once>
                            @csrf @method('PATCH')
                            <div class="field"><label class="field__label" for="category-name-{{ $category->id }}">Nama</label><input id="category-name-{{ $category->id }}" name="name" class="control" value="{{ $category->name }}" required></div>
                            <div class="field"><label class="field__label" for="category-order-{{ $category->id }}">Urutan</label><input id="category-order-{{ $category->id }}" name="sort_order" type="number" class="control" value="{{ $category->sort_order }}" min="0" max="999" required></div>
                            <label class="field--full flex min-h-12 items-center gap-3 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="size-4 accent-padelegan-600" @checked($category->is_active)> Tampilkan di formulir warga</label>
                            <div class="admin-form-actions"><button type="submit" class="action action--primary action--sm">Simpan</button></div>
                        </form>
                    </details>
                @endforeach
            </div>
        </section>

        <section x-show="tab === 'jenis'" x-cloak class="admin-manage-layout">
            <form action="{{ route('admin.master.subcategories.store') }}" method="POST" class="admin-panel admin-panel__body admin-form-grid" data-submit-once>
                @csrf
                <h2 class="admin-section-title field--full">Tambah jenis masalah</h2>
                <div class="field field--full"><label for="subcategory-category" class="field__label">Kategori</label><select id="subcategory-category" name="category_id" class="control" required>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                <div class="field"><label for="subcategory-name" class="field__label">Nama jenis</label><input id="subcategory-name" name="name" class="control" maxlength="120" required></div>
                <div class="field"><label for="subcategory-order" class="field__label">Urutan</label><input id="subcategory-order" name="sort_order" type="number" class="control" value="1" min="0" max="999" required></div>
                <input type="hidden" name="is_active" value="1">
                <div class="admin-form-actions"><button class="action action--primary" type="submit">Tambah jenis</button></div>
            </form>
            <div class="admin-edit-list">
                @foreach($subcategories as $subcategory)
                    <details class="admin-edit-row">
                        <summary><span>{{ $subcategory->name }} <span class="admin-edit-meta">{{ $subcategory->category->name }} · {{ $subcategory->reports_count }} laporan · {{ $subcategory->is_active ? 'Aktif' : 'Nonaktif' }}</span></span></summary>
                        <form action="{{ route('admin.master.subcategories.update', $subcategory) }}" method="POST" class="admin-form-grid" data-submit-once>
                            @csrf @method('PATCH')
                            <div class="field field--full"><label class="field__label" for="subcategory-category-{{ $subcategory->id }}">Kategori</label><select id="subcategory-category-{{ $subcategory->id }}" name="category_id" class="control" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($subcategory->category_id === $category->id)>{{ $category->name }}</option>@endforeach</select></div>
                            <div class="field"><label class="field__label" for="subcategory-name-{{ $subcategory->id }}">Nama</label><input id="subcategory-name-{{ $subcategory->id }}" name="name" class="control" value="{{ $subcategory->name }}" required></div>
                            <div class="field"><label class="field__label" for="subcategory-order-{{ $subcategory->id }}">Urutan</label><input id="subcategory-order-{{ $subcategory->id }}" name="sort_order" type="number" class="control" value="{{ $subcategory->sort_order }}" min="0" max="999" required></div>
                            <label class="field--full flex min-h-12 items-center gap-3 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="size-4 accent-padelegan-600" @checked($subcategory->is_active)> Tampilkan di formulir warga</label>
                            <div class="admin-form-actions"><button type="submit" class="action action--primary action--sm">Simpan</button></div>
                        </form>
                    </details>
                @endforeach
            </div>
        </section>

        <section x-show="tab === 'qr'" x-cloak class="admin-manage-layout">
            <form action="{{ route('admin.master.qr-sources.store') }}" method="POST" class="admin-panel admin-panel__body admin-form-grid" data-submit-once>
                @csrf
                <h2 class="admin-section-title field--full">Tambah sumber QR</h2>
                <div class="field"><label for="qr-code" class="field__label">Kode</label><input id="qr-code" name="code" class="control" maxlength="50" placeholder="DSN01" required></div>
                <div class="field"><label for="qr-name" class="field__label">Nama sumber</label><input id="qr-name" name="name" class="control" maxlength="120" required></div>
                <div class="field"><label for="qr-dusun" class="field__label">Dusun</label><select id="qr-dusun" name="dusun_id" class="control"><option value="">Umum</option>@foreach($dusuns as $dusun)<option value="{{ $dusun->id }}">{{ $dusun->name }}</option>@endforeach</select></div>
                <div class="field"><label for="qr-placement" class="field__label">Lokasi pemasangan</label><input id="qr-placement" name="placement" class="control" maxlength="200"></div>
                <input type="hidden" name="is_active" value="1">
                <div class="admin-form-actions"><button class="action action--primary" type="submit">Tambah sumber</button></div>
            </form>
            <div class="admin-edit-list">
                @foreach($qrSources as $source)
                    <details class="admin-edit-row">
                        <summary><span>{{ $source->name }} <span class="admin-edit-meta">{{ $source->code }} · {{ $source->reports_count }} laporan · {{ $source->is_active ? 'Aktif' : 'Nonaktif' }}</span></span></summary>
                        <form action="{{ route('admin.master.qr-sources.update', $source) }}" method="POST" class="admin-form-grid" data-submit-once>
                            @csrf @method('PATCH')
                            <div class="field"><label class="field__label" for="qr-code-{{ $source->id }}">Kode</label><input id="qr-code-{{ $source->id }}" name="code" class="control" value="{{ $source->code }}" required></div>
                            <div class="field"><label class="field__label" for="qr-name-{{ $source->id }}">Nama</label><input id="qr-name-{{ $source->id }}" name="name" class="control" value="{{ $source->name }}" required></div>
                            <div class="field"><label class="field__label" for="qr-dusun-{{ $source->id }}">Dusun</label><select id="qr-dusun-{{ $source->id }}" name="dusun_id" class="control"><option value="">Umum</option>@foreach($dusuns as $dusun)<option value="{{ $dusun->id }}" @selected($source->dusun_id === $dusun->id)>{{ $dusun->name }}</option>@endforeach</select></div>
                            <div class="field"><label class="field__label" for="qr-placement-{{ $source->id }}">Lokasi</label><input id="qr-placement-{{ $source->id }}" name="placement" class="control" value="{{ $source->placement }}"></div>
                            <label class="field--full flex min-h-12 items-center gap-3 text-sm"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" class="size-4 accent-padelegan-600" @checked($source->is_active)> Izinkan sumber QR ini</label>
                            <div class="admin-form-actions"><button type="submit" class="action action--primary action--sm">Simpan</button></div>
                        </form>
                    </details>
                @endforeach
            </div>
        </section>
    </div>
@endsection
