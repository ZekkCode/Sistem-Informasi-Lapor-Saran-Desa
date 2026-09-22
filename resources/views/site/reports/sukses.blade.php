@extends('layouts.site')

@section('title', 'Laporan Sudah Diterima')

@section('content')
    <section class="site-shell py-10 sm:py-14 lg:py-16">
        <div
            class="receipt-layout"
            x-data="{
                copyState: 'idle',
                async copyCode() {
                    const code = this.$refs.code.textContent.trim();

                    try {
                        await navigator.clipboard.writeText(code);
                    } catch (error) {
                        const field = document.createElement('textarea');
                        field.value = code;
                        field.setAttribute('readonly', '');
                        field.style.position = 'fixed';
                        field.style.opacity = '0';
                        document.body.appendChild(field);
                        field.select();
                        document.execCommand('copy');
                        field.remove();
                    }

                    this.copyState = 'copied';
                    window.setTimeout(() => this.copyState = 'idle', 1800);
                },
            }"
        >
            <div data-aos="fade-up">
                <p class="receipt-status">
                    <span class="receipt-status__mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Pengiriman selesai
                </p>

                <h1 class="public-page-title mt-5 text-padelegan-900">Laporan sudah kami terima.</h1>
                <p class="reading-measure mt-4 text-base leading-7 text-padelegan-900/65">Simpan nomor ini untuk membuka perkembangan laporan.</p>

                <div class="receipt-ticket">
                    <div class="receipt-ticket__inner">
                        <div>
                            <span class="text-xs font-medium text-padelegan-900/50">Nomor laporan</span>
                            <strong x-ref="code" class="receipt-code text-padelegan-900">{{ $report->report_code }}</strong>
                        </div>
                        <button type="button" class="action action--secondary" @click="copyCode" x-text="copyState === 'copied' ? 'Sudah disalin' : 'Salin nomor'">Salin nomor</button>
                    </div>
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('reports.track', ['code' => $report->report_code]) }}" class="action action--primary">Lihat perkembangan</a>
                    <a href="{{ route('reports.create') }}" class="action action--quiet">Buat laporan lain</a>
                </div>
            </div>

            <aside class="receipt-aside" aria-labelledby="next-title" data-aos="fade-up" data-aos-delay="100">
                <p class="public-kicker">Langkah petugas</p>
                <h2 id="next-title" class="mt-2 text-xl font-semibold tracking-[-0.025em] text-padelegan-900">Petugas menindaklanjuti laporan</h2>
                <ul class="receipt-next" role="list">
                    <li>
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6h16M4 12h10M4 18h7" stroke-linecap="round"/></svg>
                        <p class="text-sm leading-6 text-padelegan-900/65"><strong class="block font-semibold text-padelegan-900">Petugas memeriksa dan memberi kabar</strong>Petugas memeriksa bukti, lalu mencatat status serta tindakan berikutnya.</p>
                    </li>
                    <li>
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 21s7-3.5 7-9V5.5L12 3 5 5.5V12c0 5.5 7 9 7 9Z"/><path d="m9.5 12 1.7 1.7 3.7-4"/></svg>
                        <p class="text-sm leading-6 text-padelegan-900/65"><strong class="block font-semibold text-padelegan-900">Petugas melindungi identitas</strong>Halaman publik tidak menampilkan nama dan WhatsApp.</p>
                    </li>
                </ul>
            </aside>
        </div>
    </section>
@endsection
