@php
    $record = $getRecord();
    $documents = $record?->document_urls ?? [];
@endphp

@if(count($documents) > 0)
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem;">
        @foreach($documents as $doc)
            <div class="p-3.5 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-xs flex flex-col justify-between transition-all hover:border-teal-500/50">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold {{ $doc['is_pdf'] ? 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300 border border-red-200 dark:border-red-800' : 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800' }}">
                            @if($doc['is_pdf'])
                                📄 Dokumen PDF
                            @else
                                🖼️ Bukti / Scan Gambar
                            @endif
                        </span>
                        <a
                            href="{{ $doc['url'] }}"
                            target="_blank"
                            class="text-xs font-medium text-teal-600 hover:text-teal-800 dark:text-teal-400 inline-flex items-center gap-1 hover:underline"
                        >
                            Buka Berkas &nearr;
                        </a>
                    </div>

                    @if($doc['is_image'])
                        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-800 aspect-video flex items-center justify-center">
                            <img
                                src="{{ $doc['url'] }}"
                                alt="{{ $doc['name'] }}"
                                class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            />
                        </div>
                    @else
                        <div class="rounded-xl border border-gray-200 dark:border-gray-800 aspect-video flex flex-col items-center justify-center text-center p-4 bg-gray-50 dark:bg-gray-800/50">
                            <svg class="w-10 h-10 text-red-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Format PDF</span>
                            <span class="text-[11px] text-gray-500 mt-0.5">Klik 'Buka Berkas' untuk membaca dokumen</span>
                        </div>
                    @endif
                </div>

                <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-800/60 flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        @if(!empty($doc['caption']))
                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate" title="{{ $doc['caption'] }}">
                                {{ $doc['caption'] }}
                            </p>
                            <p class="text-[11px] text-gray-500 truncate" title="{{ $doc['name'] }}">
                                {{ $doc['name'] }}
                            </p>
                        @else
                            <p class="text-xs text-gray-600 dark:text-gray-400 truncate" title="{{ $doc['name'] }}">
                                {{ $doc['name'] }}
                            </p>
                        @endif
                    </div>
                    <a
                        href="{{ $doc['url'] }}"
                        download="{{ $doc['name'] }}"
                        class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:hover:text-gray-200 inline-flex items-center gap-1 shrink-0"
                        title="Unduh Berkas"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Unduh
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center bg-gray-50 dark:bg-gray-900/30">
        <p class="text-sm text-gray-500">Tidak ada dokumen atau bukti berkas yang diunggah.</p>
    </div>
@endif
