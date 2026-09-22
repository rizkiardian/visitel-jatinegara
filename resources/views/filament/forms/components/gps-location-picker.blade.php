<div
    x-data="gpsLocationPicker({
        lat: $wire.entangle('data.latitude'),
        lng: $wire.entangle('data.longitude'),
        accuracy: $wire.entangle('data.accuracy_meters')
    })"
    x-init="init()"
    class="space-y-4"
>
    <!-- Leaflet Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <style>
        .leaflet-pane { z-index: 10 !important; }
        .leaflet-top, .leaflet-bottom { z-index: 20 !important; }
        .leaflet-container { font-family: inherit; }
    </style>

    <!-- GPS Status Card -->
    <div
        class="flex items-center gap-4 p-4 rounded-2xl border transition-all duration-200"
        :style="
            status === 'success' ? 'background-color: #ecfdf5; border-color: #a7f3d0;' :
            status === 'error'   ? 'background-color: #fef2f2; border-color: #fecaca;' :
            status === 'loading' ? 'background-color: #fffbeb; border-color: #fde68a;' :
                                   'background-color: #f9fafb; border-color: #e5e7eb;'
        "
    >
        <!-- Status Icon -->
        <div
            class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-sm"
            :style="
                status === 'success' ? 'background-color: #059669; color: #ffffff;' :
                status === 'error'   ? 'background-color: #dc2626; color: #ffffff;' :
                status === 'loading' ? 'background-color: #d97706; color: #ffffff;' :
                                       'background-color: #0f766e; color: #ffffff;'
            "
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>

        <!-- Status Text -->
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider" style="color: #6b7280;">Status GPS</p>
            <p class="text-base font-bold" style="color: #111827;" x-text="statusTitle"></p>
            <p
                class="text-xs font-medium"
                :style="
                    status === 'success' ? 'color: #047857;' :
                    status === 'error'   ? 'color: #b91c1c;' :
                    status === 'loading' ? 'color: #b45309;' :
                                           'color: #6b7280;'
                "
                x-text="statusSubtitle"
            ></p>
        </div>

        <!-- Action Button -->
        <button
            type="button"
            @click="getLocation()"
            :disabled="status === 'loading'"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-medium text-sm border shadow-sm transition cursor-pointer"
            style="background-color: #ffffff; color: #0f766e; border-color: #99f6e4;"
        >
            <svg :class="{'animate-spin': status === 'loading'}" style="width: 16px; height: 16px; color: #0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span x-text="status === 'loading' ? 'Mencari...' : 'Perbarui Lokasi'"></span>
        </button>
    </div>

    <!-- Leaflet Map Container with wire:ignore so Livewire never morphs/destroys it -->
    <div wire:ignore class="relative rounded-2xl overflow-hidden border border-gray-300 dark:border-gray-700 shadow-sm" style="min-height: 320px; position: relative;">
        <div x-ref="mapContainer" style="width: 100%; height: 320px; min-height: 320px; z-index: 1; background: #e5e7eb;"></div>

        <!-- Help overlay pill -->
        <div style="position: absolute; bottom: 12px; left: 12px; z-index: 20; background: rgba(255, 255, 255, 0.95); padding: 4px 10px; border-radius: 8px; border: 1px solid #e5e7eb; font-size: 11px; color: #374151; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
            💡 Pin dapat digeser atau klik pada peta untuk menyesuaikan titik
        </div>
    </div>

    <!-- 3 Metrics Card (LATITUDE, LONGITUDE, AKURASI) -->
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem;">
        <div class="p-3 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 text-center shadow-xs">
            <span class="block text-[11px] font-semibold uppercase tracking-wider" style="color: #6b7280;">Latitude</span>
            <span class="block mt-0.5 text-sm font-bold text-gray-900 dark:text-gray-100" x-text="lat ? Number(lat).toFixed(6) : '-'"></span>
        </div>

        <div class="p-3 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 text-center shadow-xs">
            <span class="block text-[11px] font-semibold uppercase tracking-wider" style="color: #6b7280;">Longitude</span>
            <span class="block mt-0.5 text-sm font-bold text-gray-900 dark:text-gray-100" x-text="lng ? Number(lng).toFixed(6) : '-'"></span>
        </div>

        <div class="p-3 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 text-center shadow-xs">
            <span class="block text-[11px] font-semibold uppercase tracking-wider" style="color: #6b7280;">Akurasi</span>
            <span class="block mt-0.5 text-sm font-bold text-gray-900 dark:text-gray-100" x-text="accuracy ? accuracy + ' m' : '-'"></span>
        </div>
    </div>
</div>

<script>
(function() {
    function initGpsComponent() {
        if (!window.Alpine) return;

        window.Alpine.data('gpsLocationPicker', (config) => ({
            lat: config.lat,
            lng: config.lng,
            accuracy: config.accuracy,
            status: 'idle',
            statusTitle: 'Belum Terdeteksi',
            statusSubtitle: 'Klik tombol Perbarui Lokasi untuk merekam titik GPS AM',
            map: null,
            marker: null,
            circle: null,

            init() {
                if (this.lat && this.lng) {
                    this.status = 'success';
                    this.statusTitle = 'Lokasi Didapat';
                    this.statusSubtitle = 'Akurasi: ' + (this.accuracy ? this.accuracy + ' m' : 'Tersimpan');
                }

                this.ensureLeaflet(() => {
                    this.$nextTick(() => {
                        this.initMap();
                    });
                });
            },

            ensureLeaflet(callback) {
                if (window.L) {
                    callback();
                    return;
                }

                let check = setInterval(() => {
                    if (window.L) {
                        clearInterval(check);
                        callback();
                    }
                }, 50);

                setTimeout(() => clearInterval(check), 5000);
            },

            initMap() {
                if (!window.L || !this.$refs.mapContainer) return;

                const container = this.$refs.mapContainer;

                // If map is already initialized on this container, don't re-create L.map!
                if (this.map) {
                    this.refreshMarker();
                    this.map.invalidateSize();
                    return;
                }

                // If DOM node has orphaned leaflet id from previous render, clear it
                if (container._leaflet_id) {
                    container._leaflet_id = null;
                }

                const defaultLat = this.lat ? parseFloat(this.lat) : -6.220621;
                const defaultLng = this.lng ? parseFloat(this.lng) : 106.869420;
                const zoomLevel = this.lat ? 16 : 14;

                try {
                    this.map = L.map(container, {
                        zoomControl: true,
                        attributionControl: true
                    }).setView([defaultLat, defaultLng], zoomLevel);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(this.map);

                    this.updateMarker(defaultLat, defaultLng, this.accuracy);

                    this.map.on('click', (e) => {
                        this.lat = e.latlng.lat.toFixed(7);
                        this.lng = e.latlng.lng.toFixed(7);
                        this.status = 'success';
                        this.statusTitle = 'Titik Dipilih Manual';
                        this.statusSubtitle = 'Koordinat disesuaikan dari peta';
                        this.updateMarker(e.latlng.lat, e.latlng.lng, null);
                    });

                    setTimeout(() => {
                        if (this.map) this.map.invalidateSize();
                    }, 300);
                } catch (e) {
                    console.error('Leaflet init error:', e);
                }
            },

            refreshMarker() {
                if (this.lat && this.lng) {
                    this.updateMarker(parseFloat(this.lat), parseFloat(this.lng), this.accuracy);
                }
            },

            updateMarker(lat, lng, accuracyMeters) {
                if (!this.map || !window.L) return;

                if (this.marker) {
                    this.marker.setLatLng([lat, lng]);
                } else {
                    this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                    this.marker.on('dragend', (e) => {
                        const pos = e.target.getLatLng();
                        this.lat = pos.lat.toFixed(7);
                        this.lng = pos.lng.toFixed(7);
                        this.status = 'success';
                        this.statusTitle = 'Titik Digeser Manual';
                        this.statusSubtitle = 'Koordinat disesuaikan dari pin marker';
                        if (this.circle) {
                            this.circle.setLatLng(pos);
                        }
                    });
                }

                if (accuracyMeters && accuracyMeters > 0) {
                    if (this.circle) {
                        this.circle.setLatLng([lat, lng]);
                        this.circle.setRadius(accuracyMeters);
                    } else {
                        this.circle = L.circle([lat, lng], {
                            radius: accuracyMeters,
                            color: '#0284c7',
                            fillColor: '#38bdf8',
                            fillOpacity: 0.25,
                            weight: 2
                        }).addTo(this.map);
                    }
                } else if (this.circle) {
                    this.map.removeLayer(this.circle);
                    this.circle = null;
                }

                this.map.setView([lat, lng], Math.max(this.map.getZoom(), 15));
            },

            getLocation() {
                if (!navigator.geolocation) {
                    this.status = 'error';
                    this.statusTitle = 'GPS Tidak Didukung';
                    this.statusSubtitle = 'Browser Anda tidak mendukung layanan Geolocation.';
                    return;
                }

                this.status = 'loading';
                this.statusTitle = 'Mencari Lokasi GPS...';
                this.statusSubtitle = 'Sedang meminta akses satelit/koordinat posisi';

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const cLat = pos.coords.latitude;
                        const cLng = pos.coords.longitude;
                        const cAcc = Math.round(pos.coords.accuracy);

                        this.lat = cLat.toFixed(7);
                        this.lng = cLng.toFixed(7);
                        this.accuracy = cAcc;

                        this.status = 'success';
                        this.statusTitle = 'Lokasi Didapat';
                        this.statusSubtitle = 'Akurasi: ' + cAcc + ' m';

                        if (this.map) {
                            this.updateMarker(cLat, cLng, cAcc);
                        }
                    },
                    (err) => {
                        this.status = 'error';
                        switch (err.code) {
                            case err.PERMISSION_DENIED:
                                this.statusTitle = 'GPS Ditolak';
                                this.statusSubtitle = 'Izinkan akses lokasi pada browser lalu coba lagi.';
                                break;
                            case err.POSITION_UNAVAILABLE:
                                this.statusTitle = 'GPS Tidak Tersedia';
                                this.statusSubtitle = 'Informasi lokasi tidak dapat diperoleh.';
                                break;
                            case err.TIMEOUT:
                                this.statusTitle = 'GPS Timeout';
                                this.statusSubtitle = 'Waktu permintaan lokasi habis. Coba ulangi.';
                                break;
                            default:
                                this.statusTitle = 'GPS Gagal';
                                this.statusSubtitle = err.message || 'Terjadi kesalahan saat membaca GPS.';
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            }
        }));
    }

    if (window.Alpine) {
        initGpsComponent();
    } else {
        document.addEventListener('alpine:init', initGpsComponent);
    }
})();
</script>
