@php
    $record = $getRecord();
    $location = $record?->locations()->latest('id')->first();
@endphp

@if($location && $location->latitude && $location->longitude)
    <div
        x-data="{
            lat: {{ (float) $location->latitude }},
            lng: {{ (float) $location->longitude }},
            acc: {{ (float) ($location->accuracy_meters ?? 0) }},
            map: null,
            init() {
                let container = this.$refs.viewMapContainer;
                if (!container) return;

                let mount = () => {
                    if (!window.L || !container) return;
                    if (this.map) {
                        this.map.invalidateSize();
                        return;
                    }
                    if (container._leaflet_id) {
                        container._leaflet_id = null;
                    }
                    try {
                        this.map = L.map(container, {
                            zoomControl: true,
                            attributionControl: true,
                            dragging: false,
                            scrollWheelZoom: true,
                            touchZoom: true,
                            doubleClickZoom: true
                        }).setView([this.lat, this.lng], 16);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(this.map);

                        L.marker([this.lat, this.lng], { draggable: false })
                            .addTo(this.map)
                            .bindPopup('<b>Titik Kunjungan AM</b><br>{{ addslashes($record->businessCustomer?->name ?? 'Customer') }}')
                            .openPopup();

                        if (this.acc > 0) {
                            L.circle([this.lat, this.lng], {
                                radius: this.acc,
                                color: '#0284c7',
                                fillColor: '#38bdf8',
                                fillOpacity: 0.25,
                                weight: 2
                            }).addTo(this.map);
                        }

                        if (window.ResizeObserver) {
                            const ro = new ResizeObserver(() => {
                                if (this.map) this.map.invalidateSize();
                            });
                            ro.observe(container);
                        }

                        setTimeout(() => {
                            if (this.map) this.map.invalidateSize();
                        }, 350);
                    } catch (e) {
                        console.error('Error mounting Leaflet in view modal:', e);
                    }
                };

                if (window.L) {
                    setTimeout(mount, 100);
                } else {
                    let interval = setInterval(() => {
                        if (window.L) {
                            clearInterval(interval);
                            mount();
                        }
                    }, 50);
                    setTimeout(() => clearInterval(interval), 5000);
                }
            }
        }"
        class="space-y-3"
    >
        <!-- Status Header with Google Maps Link -->
        <div class="flex items-center justify-between p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-950/30">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white shrink-0" style="background-color: #059669;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-emerald-800 dark:text-emerald-300">GPS Terverifikasi Saat Kunjungan</span>
                    <span class="block text-[11px] text-emerald-600 dark:text-emerald-400">
                        Tercatat: {{ $location->captured_at ? $location->captured_at->format('d M Y, H:i') : ($record->created_at ? $record->created_at->format('d M Y, H:i') : '-') }} WIB
                    </span>
                </div>
            </div>

            <a
                href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-teal-700 dark:text-teal-300 bg-white dark:bg-gray-800 border border-teal-200 dark:border-teal-700 hover:bg-teal-50 shadow-xs transition"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                Buka di Google Maps
            </a>
        </div>

        <!-- Read-only Leaflet Map -->
        <div wire:ignore class="relative rounded-xl overflow-hidden border border-gray-300 dark:border-gray-700 shadow-xs" style="min-height: 280px;">
            <div x-ref="viewMapContainer" style="width: 100%; height: 280px; min-height: 280px; z-index: 1; background: #e5e7eb;"></div>
        </div>

        <!-- Metrics Row -->
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem;">
            <div class="p-2.5 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 text-center">
                <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Latitude</span>
                <span class="block mt-0.5 text-xs font-bold text-gray-900 dark:text-gray-100">{{ number_format($location->latitude, 6) }}</span>
            </div>
            <div class="p-2.5 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 text-center">
                <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Longitude</span>
                <span class="block mt-0.5 text-xs font-bold text-gray-900 dark:text-gray-100">{{ number_format($location->longitude, 6) }}</span>
            </div>
            <div class="p-2.5 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 text-center">
                <span class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Akurasi</span>
                <span class="block mt-0.5 text-xs font-bold text-gray-900 dark:text-gray-100">{{ $location->accuracy_meters ? round($location->accuracy_meters) . ' m' : '-' }}</span>
            </div>
        </div>
    </div>
@else
    <div class="p-6 text-center rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
        <svg class="w-8 h-8 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tidak ada koordinat GPS tercatat</p>
        <p class="text-xs text-gray-500">Kunjungan ini disimpan tanpa deteksi lokasi GPS aktif (misal Non-Visit atau GPS dinonaktifkan).</p>
    </div>
@endif
