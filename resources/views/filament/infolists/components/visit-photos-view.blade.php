@php
    $record = $getRecord();
    $locationPhoto = $record?->photos()->where('photo_type', 'LocationPhoto')->latest('id')->first();
    $picPhoto = $record?->photos()->where('photo_type', 'PhotoWithPIC')->latest('id')->first();
@endphp

@if($locationPhoto || $picPhoto)
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
        <!-- Foto Lokasi -->
        <div class="p-3.5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        🏢 Foto Lokasi / Gedung
                    </span>
                    @if($locationPhoto)
                        <a
                            href="{{ asset('storage/' . $locationPhoto->file_url) }}"
                            target="_blank"
                            class="text-xs font-medium text-teal-600 hover:text-teal-800 dark:text-teal-400 inline-flex items-center gap-1"
                        >
                            Perbesar &nearr;
                        </a>
                    @endif
                </div>

                @if($locationPhoto)
                    <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-800 aspect-video flex items-center justify-center">
                        <img
                            src="{{ asset('storage/' . $locationPhoto->file_url) }}"
                            alt="Foto Lokasi Kunjungan"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                        />
                    </div>
                    <p class="mt-2 text-[11px] text-gray-500">
                        Diunggah: {{ $locationPhoto->uploaded_at ? $locationPhoto->uploaded_at->format('d M Y, H:i') : ($record->created_at ? $record->created_at->format('d M Y, H:i') : '-') }} WIB
                    </p>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 aspect-video flex flex-col items-center justify-center text-center p-4 bg-gray-50 dark:bg-gray-900/40">
                        <span class="text-xs text-gray-400">Tidak ada foto depan gedung</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Foto PIC -->
        <div class="p-3.5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        👥 Foto Bersama PIC
                    </span>
                    @if($picPhoto)
                        <a
                            href="{{ asset('storage/' . $picPhoto->file_url) }}"
                            target="_blank"
                            class="text-xs font-medium text-teal-600 hover:text-teal-800 dark:text-teal-400 inline-flex items-center gap-1"
                        >
                            Perbesar &nearr;
                        </a>
                    @endif
                </div>

                @if($picPhoto)
                    <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-800 aspect-video flex items-center justify-center">
                        <img
                            src="{{ asset('storage/' . $picPhoto->file_url) }}"
                            alt="Foto Bersama PIC"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                        />
                    </div>
                    <p class="mt-2 text-[11px] text-gray-500">
                        Diunggah: {{ $picPhoto->uploaded_at ? $picPhoto->uploaded_at->format('d M Y, H:i') : ($record->created_at ? $record->created_at->format('d M Y, H:i') : '-') }} WIB
                    </p>
                @else
                    <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 aspect-video flex flex-col items-center justify-center text-center p-4 bg-gray-50 dark:bg-gray-900/40">
                        <span class="text-xs text-gray-400">Tidak ada foto bersama PIC</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="p-6 text-center rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada foto dokumentasi</p>
        <p class="text-xs text-gray-500">Laporan kunjungan ini belum memiliki lampiran foto bukti kunjungan.</p>
    </div>
@endif
