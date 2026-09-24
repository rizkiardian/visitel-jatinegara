<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitReportResource\Pages;
use App\Models\BusinessCustomer;
use App\Models\VisitReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitReportResource extends Resource
{
    protected static ?string $model = VisitReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Manajemen Kinerja';

    protected static ?string $navigationLabel = 'Laporan Kunjungan';

    protected static ?string $modelLabel = 'Laporan Kunjungan';

    protected static ?string $pluralModelLabel = 'Laporan Kunjungan';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::where('validation_status', 'Pending')->count();
        return $pending > 0 ? (string)$pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Pelanggan & Kunjungan')
                    ->description('Informasi pelanggan, personil Account Manager, dan jadwal pelaksanaan.')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->default(fn () => auth()->user()?->employee_id)
                            ->disabled(fn () => auth()->user()?->role === 'AM' && auth()->user()?->employee_id)
                            ->dehydrated()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('business_customer_id', null);
                                $set('nipnas', null);
                                $set('bc_status', null);
                                $set('customer_pic_name', null);
                            })
                            ->label('Account Manager (AM)'),
                        Forms\Components\Select::make('visit_type')
                            ->options([
                                'Visit' => 'Direct Visit (Kunjungan Langsung / Onsite)',
                                'NonVisit' => 'Non-Visit (Call / Online / Chat / Vicon)',
                            ])
                            ->required()
                            ->default('Visit')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state === 'Visit') {
                                    $set('communication_channel', null);
                                }
                                $set('activity_topic', null);
                            })
                            ->label('Tipe Kunjungan'),
                        Forms\Components\Select::make('activity_category_id')
                            ->relationship('activityCategory', 'name')
                            ->required()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('activity_type_id', null);
                                $set('activity_topic', null);
                            })
                            ->label('Kategori Aktivitas (Funnel)'),
                        Forms\Components\Select::make('activity_type_id')
                            ->relationship(
                                name: 'activityType',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Forms\Get $get, ?VisitReport $record) {
                                    $catId = $get('activity_category_id');
                                    if ($catId) {
                                        $query->where(function ($q) use ($catId, $record) {
                                            $q->where('activity_category_id', $catId);
                                            if ($record?->activity_type_id) {
                                                $q->orWhere('id', $record->activity_type_id);
                                            }
                                        });
                                    }
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(fn (Forms\Get $get): bool => blank($get('activity_category_id')))
                            ->placeholder(fn (Forms\Get $get): string => blank($get('activity_category_id'))
                                ? '— Pilih Kategori Funnel di samping dahulu —'
                                : '— Pilih Jenis Kegiatan —'
                            )
                            ->label('Jenis Kegiatan'),
                        Forms\Components\Select::make('communication_channel')
                            ->label('Kanal Komunikasi Online')
                            ->options([
                                'Telepon' => '📞 Telepon / Voice Call',
                                'Chat' => '💬 Chat (WhatsApp / Telegram)',
                                'Email' => '✉️ Email Resmi',
                                'Video Conference' => '📹 Video Conference (Zoom / Meet / Teams)',
                            ])
                            ->required(fn(Forms\Get $get): bool => $get('visit_type') === 'NonVisit')
                            ->visible(fn(Forms\Get $get): bool => $get('visit_type') === 'NonVisit')
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('activity_topic', null)),
                        Forms\Components\Select::make('activity_topic')
                            ->label('Topik Interaksi Online')
                            ->options(function (Forms\Get $get, ?VisitReport $record): array {
                                $channel = $get('communication_channel');
                                $catId = $get('activity_category_id');

                                if (blank($channel) || blank($catId)) {
                                    return $record?->activity_topic ? [$record->activity_topic => $record->activity_topic] : [];
                                }

                                $catName = \App\Models\ActivityCategory::find($catId)?->name;

                                $topicMatrix = [
                                    'Telepon' => [
                                        'Approaching' => [
                                            'Cold Calling / Perkenalan Prospek Baru' => '📞 Cold Calling / Perkenalan Prospek Baru',
                                            'Konfirmasi Jadwal Pertemuan / Janji Temu' => '📅 Konfirmasi Jadwal Pertemuan / Janji Temu',
                                            'Skrining Kebutuhan & Minat Pelanggan Awal' => '🔍 Skrining Kebutuhan & Minat Pelanggan Awal',
                                        ],
                                        'Dealing' => [
                                            'Follow Up Status Keputusan Penawaran' => '⏳ Follow Up Status Keputusan Penawaran',
                                            'Klarifikasi Cepat Komponen Harga / Budget' => '💬 Klarifikasi Cepat Komponen Harga / Budget',
                                            'Konfirmasi Pengiriman Dokumen Kontrak / PKS' => '📑 Konfirmasi Pengiriman Dokumen Kontrak / PKS',
                                        ],
                                        'Aftersales' => [
                                            'Konfirmasi Pembayaran Tagihan (Desk Collection)' => '💰 Konfirmasi Pembayaran Tagihan (Desk Collection)',
                                            'Konfirmasi Pasca Penanganan Gangguan' => '✅ Konfirmasi Pasca Penanganan Gangguan',
                                            'Pengecekan Kepuasan Layanan (Customer Courtesy Call)' => '📞 Pengecekan Kepuasan Layanan (Customer Courtesy Call)',
                                        ],
                                    ],
                                    'Chat' => [
                                        'Approaching' => [
                                            'Eksplorasi Kebutuhan & Tanya Jawab Produk' => '🔍 Eksplorasi Kebutuhan & Tanya Jawab Produk',
                                            'Pengiriman Brosur / Ringkasan Penawaran' => '📑 Pengiriman Brosur / Ringkasan Penawaran',
                                            'Koordinasi Lokasi & Waktu Janji Temu' => '📍 Koordinasi Lokasi & Waktu Janji Temu',
                                        ],
                                        'Dealing' => [
                                            'Diskusi & Negosiasi Harga Cepat' => '💬 Diskusi & Negosiasi Harga Cepat',
                                            'Konfirmasi Persetujuan Final Penawaran' => '⚡ Konfirmasi Persetujuan Final Penawaran',
                                            'Update Status Pembuatan Kontrak / PKS' => '📝 Update Status Pembuatan Kontrak / PKS',
                                        ],
                                        'Aftersales' => [
                                            'Pengingat Jatuh Tempo Tagihan (Payment Reminder)' => '⏰ Pengingat Jatuh Tempo Tagihan (Payment Reminder)',
                                            'Koordinasi Penanganan Keluhan (Complain Handling)' => '🛠️ Koordinasi Penanganan Keluhan (Complain Handling)',
                                            'Konfirmasi Kebutuhan Perpanjangan Kontrak / Renewal' => '🔄 Konfirmasi Kebutuhan Perpanjangan Kontrak / Renewal',
                                        ],
                                    ],
                                    'Email' => [
                                        'Approaching' => [
                                            'Pengiriman Company Profile & Portofolio Solusi' => '💼 Pengiriman Company Profile & Portofolio Solusi',
                                            'Undangan Formal Pertemuan / Audiensi Bisnis' => '✉️ Undangan Formal Pertemuan / Audiensi Bisnis',
                                            'Formulir Kuesioner Kebutuhan Pelanggan' => '📋 Formulir Kuesioner Kebutuhan Pelanggan',
                                        ],
                                        'Dealing' => [
                                            'Pengiriman Surat Penawaran Harga Resmi (SPH)' => '✉️ Pengiriman Surat Penawaran Harga Resmi (SPH)',
                                            'Pengiriman Draf Kontrak / PKS untuk Legal Review' => '📝 Pengiriman Draf Kontrak / PKS untuk Legal Review',
                                            'Pengiriman Berita Acara / Dokumen BASO' => '📑 Pengiriman Berita Acara / Dokumen BASO',
                                        ],
                                        'Aftersales' => [
                                            'Pengiriman Faktur Pajak & Invoice Tagihan' => '🧾 Pengiriman Faktur Pajak & Invoice Tagihan',
                                            'Surat Tanggapan Resmi Gangguan / Keluhan Pelanggan' => '📩 Surat Tanggapan Resmi Gangguan / Keluhan Pelanggan',
                                            'Pengiriman Laporan Performa Layanan Bulanan (Monthly SLA)' => '📊 Pengiriman Laporan Performa Layanan Bulanan (Monthly SLA)',
                                        ],
                                    ],
                                    'Video Conference' => [
                                        'Approaching' => [
                                            'Audiensi Virtual dengan Manajemen / C-Level' => '👔 Audiensi Virtual dengan Manajemen / C-Level',
                                            'Pengenalan Solusi & Discovery Session' => '💡 Pengenalan Solusi & Discovery Session',
                                        ],
                                        'Dealing' => [
                                            'Presentasi Solusi & Demo Teknis (PoC Virtual)' => '💻 Presentasi Solusi & Demo Teknis (PoC Virtual)',
                                            'Rapat Negosiasi Komersial / Finalisasi Deal' => '🤝 Rapat Negosiasi Komersial / Finalisasi Deal',
                                            'Rapat Koordinasi Teknis Proyek (Kick-off Meeting)' => '🚀 Rapat Koordinasi Teknis Proyek (Kick-off Meeting)',
                                        ],
                                        'Aftersales' => [
                                            'Evaluasi Layanan Bulanan & Review SLA' => '📊 Evaluasi Layanan Bulanan & Review SLA',
                                            'Koordinasi Teknis Pemulihan Gangguan Kompleks' => '🛠️ Koordinasi Teknis Pemulihan Gangguan Kompleks',
                                            'Diskusi Rencana Upgrade & Pengembangan Layanan (Upselling)' => '📈 Diskusi Rencana Upgrade & Pengembangan Layanan (Upselling)',
                                        ],
                                    ],
                                ];

                                $topics = $topicMatrix[$channel][$catName] ?? [];

                                if ($record?->activity_topic && !isset($topics[$record->activity_topic])) {
                                    $topics[$record->activity_topic] = $record->activity_topic;
                                }

                                return $topics;
                            })
                            ->placeholder(function (Forms\Get $get): string {
                                if (blank($get('activity_category_id'))) {
                                    return '— Pilih Kategori Funnel di samping dahulu —';
                                }
                                if (blank($get('communication_channel'))) {
                                    return '— Pilih Kanal Komunikasi di samping dahulu —';
                                }
                                $catName = \App\Models\ActivityCategory::find($get('activity_category_id'))?->name;
                                return $catName ? "— Pilih Topik Interaksi ({$catName}) —" : '— Pilih Topik Interaksi —';
                            })
                            ->disabled(fn(Forms\Get $get): bool => blank($get('activity_category_id')) || blank($get('communication_channel')))
                            ->required(fn(Forms\Get $get): bool => $get('visit_type') === 'NonVisit')
                            ->visible(fn(Forms\Get $get): bool => $get('visit_type') === 'NonVisit')
                            ->searchable()
                            ->preload()
                            ->live(),
                        Forms\Components\Select::make('business_customer_id')
                            ->relationship(
                                name: 'businessCustomer',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Forms\Get $get, ?VisitReport $record) {
                                    $employeeId = $get('employee_id') ?? auth()->user()?->employee_id;
                                    if ($employeeId) {
                                        $query->where(function ($q) use ($employeeId, $record) {
                                            $q->where('employee_id', $employeeId);
                                            if ($record?->business_customer_id) {
                                                $q->orWhere('id', $record->business_customer_id);
                                            }
                                        });
                                    }
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->createOptionForm(BusinessCustomerResource::getFormSchema())
                            ->createOptionAction(function (Forms\Components\Actions\Action $action, Forms\Get $get) {
                                return $action
                                    ->modalHeading('Tambah Business Customer (BC) Baru')
                                    ->modalSubmitActionLabel('Simpan BC Baru')
                                    ->modalWidth('3xl')
                                    ->color('primary')
                                    ->icon('heroicon-m-plus-circle')
                                    ->tooltip('Tambah BC Baru')
                                    ->extraAttributes([
                                        'class' => 'text-primary-600 hover:text-primary-700 bg-primary-50 hover:bg-primary-100 dark:bg-primary-950 dark:hover:bg-primary-900 rounded-lg p-1 transition shadow-sm',
                                    ])
                                    ->fillForm(fn (): array => [
                                        'status' => 'New',
                                        'employee_id' => $get('employee_id') ?? auth()->user()?->employee_id,
                                    ]);
                            })
                            ->helperText('Pelanggan belum terdaftar? Klik tombol (+) di sisi kanan kolom untuk membuat BC baru.')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($customer = BusinessCustomer::find($state)) {
                                    $set('nipnas', $customer->nipnas ?? '-');
                                    $set('bc_status', $customer->status ?? 'New');
                                    if ($customer->default_pic_name) {
                                        $set('customer_pic_name', $customer->default_pic_name);
                                    }
                                } else {
                                    $set('nipnas', null);
                                    $set('bc_status', null);
                                }
                            })
                            ->label('Nama BC (Customer)'),
                        Forms\Components\TextInput::make('nipnas')
                            ->label('NIPNAS')
                            ->placeholder('Auto-fill saat BC dipilih')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, ?VisitReport $record) {
                                if ($record?->businessCustomer) {
                                    $component->state($record->businessCustomer->nipnas ?? '-');
                                }
                            }),
                        Forms\Components\TextInput::make('bc_status')
                            ->label('Status BC')
                            ->placeholder('— pilih BC terlebih dahulu —')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Forms\Components\TextInput $component, ?VisitReport $record) {
                                if ($record?->businessCustomer) {
                                    $component->state($record->businessCustomer->status ?? '-');
                                }
                            }),
                        Forms\Components\TextInput::make('customer_pic_name')
                            ->label('PIC Pelanggan')
                            ->placeholder('Nama kontak person saat kunjungan')
                            ->required(),
                        Forms\Components\DatePicker::make('visit_date')
                            ->default(now())
                            ->required()
                            ->label('Tanggal Kunjungan'),
                        Forms\Components\TimePicker::make('visit_time')
                            ->default(now()->format('H:i'))
                            ->label('Waktu Kunjungan'),
                    ])->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                Forms\Components\Section::make('Lokasi GPS & Peta Kunjungan')
                    ->description('Konfirmasi posisi koordinat AM secara langsung melalui GPS browser.')
                    ->visible(fn(Forms\Get $get): bool => $get('visit_type') === 'Visit' || $get('visit_type') === null)
                    ->schema([
                        Forms\Components\View::make('filament.forms.components.gps-location-picker')
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('latitude')
                            ->dehydrated(false),
                        Forms\Components\Hidden::make('longitude')
                            ->dehydrated(false),
                        Forms\Components\Hidden::make('accuracy_meters')
                            ->dehydrated(false),
                    ]),

                Forms\Components\Section::make(fn(Forms\Get $get): string => $get('visit_type') === 'NonVisit' ? 'Bukti Aktivitas Interaksi Online' : 'Dokumentasi Foto & Berkas Kunjungan')
                    ->description(fn(Forms\Get $get): string => $get('visit_type') === 'NonVisit'
                        ? 'Unggah bukti percakapan chat WhatsApp, email penawaran resmi, atau screenshot sesi video conference.'
                        : 'Unggah foto kehadiran fisik di lokasi dan lampiran berkas dokumen kontrak / BASO jika ada.')
                    ->schema([
                        Forms\Components\FileUpload::make('photo_location')
                            ->label('Foto Lokasi / Depan Gedung')
                            ->image()
                            ->disk('public')
                            ->directory('visit-photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->openable()
                            ->downloadable()
                            ->dehydrated(false)
                            ->visible(fn(Forms\Get $get): bool => $get('visit_type') === 'Visit' || $get('visit_type') === null)
                            ->helperText('Ambil foto gedung, gerbang, atau papan nama kantor customer.')
                            ->columnSpan(1),
                        Forms\Components\FileUpload::make('photo_pic')
                            ->label('Foto Bersama PIC Pelanggan')
                            ->image()
                            ->disk('public')
                            ->directory('visit-photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->openable()
                            ->downloadable()
                            ->dehydrated(false)
                            ->visible(fn(Forms\Get $get): bool => $get('visit_type') === 'Visit' || $get('visit_type') === null)
                            ->helperText('Ambil foto saat berdiskusi atau bersama PIC customer.')
                            ->columnSpan(1),
                        Forms\Components\Repeater::make('document_file_url')
                            ->label(fn(Forms\Get $get): string => $get('visit_type') === 'NonVisit' ? 'Bukti Digital Aktivitas Online (Screenshot Chat / Email / SPH / Vicon)' : 'Lampiran Dokumen Fisik (Kontrak / BASO / Tanda Terima)')
                            ->schema([
                                Forms\Components\FileUpload::make('file')
                                    ->label('Unggah Berkas / Dokumen')
                                    ->disk('public')
                                    ->directory('visit-documents')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                    ->openable()
                                    ->downloadable()
                                    ->required()
                                    ->helperText('Format: PDF, JPG, PNG, WEBP (Maks 10MB)'),
                                Forms\Components\TextInput::make('caption')
                                    ->label('Keterangan Dokumen (Opsional)')
                                    ->placeholder('Misal: Lembar TTD Kontrak, Screenshot Chat WA, dll')
                                    ->maxLength(150),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->addActionLabel('+ Tambah Berkas Dokumen')
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(0)
                            ->itemLabel(fn(array $state): ?string => !empty($state['caption']) ? $state['caption'] : (!empty($state['file']) ? basename($state['file']) : 'Berkas Baru'))
                            ->columnSpanFull()
                            ->helperText(fn(Forms\Get $get): string => $get('visit_type') === 'NonVisit'
                                ? match ($get('communication_channel')) {
                                    'Telepon' => 'Klik "+ Tambah Berkas Dokumen" untuk mengunggah tangkapan layar (screenshot) log panggilan atau riwayat call.',
                                    'Chat' => 'Klik "+ Tambah Berkas Dokumen" untuk mengunggah tangkapan layar (screenshot) percakapan WhatsApp atau Telegram.',
                                    'Email' => 'Klik "+ Tambah Berkas Dokumen" untuk mengunggah file PDF Surat Penawaran Harga (SPH), invoice, atau screenshot email terkirim.',
                                    'Video Conference' => 'Klik "+ Tambah Berkas Dokumen" untuk mengunggah tangkapan layar sesi virtual meeting atau file notulensi rapat.',
                                    default => 'Klik "+ Tambah Berkas Dokumen" untuk menambah bukti digital (screenshot chat, email, call log, atau berkas penawaran harga).',
                                }
                                : 'Klik "+ Tambah Berkas Dokumen" untuk menambah lampiran fisik (scan TTD kontrak, lembar BASO, faktur/tanda terima).'
                            ),
                    ])->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                Forms\Components\Section::make('Katalog Layanan & Potensi Nilai Bisnis')
                    ->description('Klasifikasi R-Level, estimasi nilai transaksi, dan katalog layanan Telkom yang ditawarkan.')
                    ->schema([
                        Forms\Components\Select::make('r_level_id')
                            ->relationship('rLevel', 'name')
                            ->preload()
                            ->label('R-Level (Tingkat Kunjungan)'),
                        Forms\Components\TextInput::make('estimated_value')
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Estimasi Nilai Transaksi (Rp)')
                            ->placeholder('0'),

                        Forms\Components\Select::make('service_category_id')
                            ->label('Kategori Layanan')
                            ->options(
                                \App\Models\ServiceCategory::pluck('name', 'id')
                                    ->toArray() + ['all' => 'Semua Kategori (Tampilkan Semua)']
                            )
                            ->placeholder('-- Pilih Kategori Layanan Terlebih Dahulu --')
                            ->helperText('Pilih kategori terlebih dahulu agar daftar layanan yang relevan muncul.')
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Forms\Components\Select $component, ?VisitReport $record) {
                                if ($record && $record->exists) {
                                    $catIds = $record->services()->pluck('service_category_id')->unique()->values();
                                    if ($catIds->count() > 1) {
                                        $component->state('all');
                                    } elseif ($catIds->count() === 1) {
                                        $component->state((string) $catIds->first());
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('services_empty_hint')
                            ->label('Layanan Ditawarkan / Terkait')
                            ->content('Pilih Kategori Layanan di atas terlebih dahulu untuk menampilkan daftar opsi layanan.')
                            ->visible(fn(Forms\Get $get): bool => blank($get('service_category_id')))
                            ->columnSpanFull(),

                        Forms\Components\CheckboxList::make('services')
                            ->relationship(
                                name: 'services',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query, Forms\Get $get) => $query->when(
                                    $get('service_category_id') && $get('service_category_id') !== 'all',
                                    fn($q) => $q->where('service_category_id', $get('service_category_id')),
                                    fn($q) => $get('service_category_id') === 'all' ? $q : $q->whereRaw('1 = 0')
                                )
                            )
                            ->visible(fn(Forms\Get $get): bool => filled($get('service_category_id')))
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                                'md' => 4,
                            ])
                            ->columnSpanFull()
                            ->label('Layanan Ditawarkan / Terkait'),
                    ])->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                Forms\Components\Section::make('Catatan Aktivitas & Voice of Customer')
                    ->schema([
                        Forms\Components\Textarea::make('activity_description')
                            ->rows(3)
                            ->label('Deskripsi Kegiatan / Story Kunjungan')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('action_plan')
                            ->rows(2)
                            ->label('Action Plan (Langkah Selanjutnya)')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('voc')
                            ->rows(2)
                            ->label('Voice of Customer (VOC / Feedback / Keluhan)')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Status Validasi Supervisor')
                    ->schema([
                        Forms\Components\Select::make('validation_status')
                            ->options([
                                'Pending' => 'Pending (Menunggu Review)',
                                'Valid' => 'Valid (Disetujui)',
                                'Rejected' => 'Rejected (Ditolak)',
                            ])
                            ->default('Pending')
                            ->required()
                            ->label('Status Validasi'),
                        Forms\Components\Select::make('validator_id')
                            ->relationship('validator', 'name')
                            ->searchable()
                            ->label('Validator / Supervisor'),
                        Forms\Components\Textarea::make('validation_notes')
                            ->rows(2)
                            ->label('Catatan Validasi')
                            ->columnSpanFull(),
                    ])->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Identitas Pelanggan & Kunjungan')
                    ->schema([
                        Infolists\Components\TextEntry::make('employee.name')
                            ->label('Account Manager (AM)')
                            ->icon('heroicon-m-user')
                            ->hint(fn(VisitReport $record) => $record->employee?->telda?->name ? 'Telda: ' . $record->employee->telda->name : null),
                        Infolists\Components\TextEntry::make('visit_type')
                            ->label('Tipe Kunjungan')
                            ->badge()
                            ->color(fn(string $state): string => $state === 'Visit' ? 'primary' : 'gray')
                            ->formatStateUsing(fn(string $state): string => $state === 'Visit' ? 'Direct Visit (Onsite)' : 'Non-Visit (Online)'),
                        Infolists\Components\TextEntry::make('activityCategory.name')
                            ->label('Kategori Funnel')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Approaching' => 'info',
                                'Dealing' => 'warning',
                                'Aftersales' => 'success',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('activityType.name')
                            ->label('Jenis Kegiatan')
                            ->badge()
                            ->color('gray')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('communication_channel')
                            ->label('Kanal Komunikasi')
                            ->badge()
                            ->color('info')
                            ->icon(fn(string $state): string => match ($state) {
                                'Telepon' => 'heroicon-m-phone',
                                'Chat' => 'heroicon-m-chat-bubble-left-right',
                                'Email' => 'heroicon-m-envelope',
                                'Video Conference' => 'heroicon-m-video-camera',
                                default => 'heroicon-m-signal',
                            })
                            ->visible(fn(VisitReport $record): bool => $record->visit_type === 'NonVisit' && filled($record->communication_channel)),
                        Infolists\Components\TextEntry::make('activity_topic')
                            ->label('Topik Interaksi Online')
                            ->badge()
                            ->color('success')
                            ->visible(fn(VisitReport $record): bool => $record->visit_type === 'NonVisit' && filled($record->activity_topic)),
                        Infolists\Components\TextEntry::make('businessCustomer.name')
                            ->label('Nama Customer (BC)')
                            ->icon('heroicon-m-building-office-2'),
                        Infolists\Components\TextEntry::make('businessCustomer.nipnas')
                            ->label('NIPNAS')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('businessCustomer.status')
                            ->label('Status BC')
                            ->badge()
                            ->placeholder('New'),
                        Infolists\Components\TextEntry::make('customer_pic_name')
                            ->label('PIC Pelanggan')
                            ->icon('heroicon-m-identification')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('visit_date')
                            ->label('Tanggal Kunjungan')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('visit_time')
                            ->label('Waktu Kunjungan')
                            ->time('H:i')
                            ->suffix(' WIB'),
                    ])->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                Infolists\Components\Section::make('Lokasi GPS & Peta Kunjungan')
                    ->visible(fn(VisitReport $record): bool => $record->visit_type === 'Visit' || $record->visit_type === null)
                    ->schema([
                        Infolists\Components\ViewEntry::make('gps_location')
                            ->view('filament.infolists.components.gps-location-view')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Dokumentasi Foto Fisik Kunjungan')
                    ->visible(fn(VisitReport $record): bool => $record->visit_type === 'Visit' || $record->visit_type === null)
                    ->schema([
                        Infolists\Components\ViewEntry::make('visit_photos')
                            ->view('filament.infolists.components.visit-photos-view')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make(fn(VisitReport $record): string => $record->visit_type === 'NonVisit' ? 'Bukti Aktivitas Interaksi Online' : 'Lampiran Dokumen Fisik (Kontrak / BASO / Tanda Terima)')
                    ->visible(fn(VisitReport $record): bool => !empty($record->document_file_url))
                    ->schema([
                        Infolists\Components\ViewEntry::make('document_files')
                            ->view('filament.infolists.components.visit-documents-view')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Katalog Layanan & Potensi Nilai Bisnis')
                    ->schema([
                        Infolists\Components\TextEntry::make('rLevel.name')
                            ->label('R-Level (Tingkat Kunjungan)')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('estimated_value')
                            ->label('Estimasi Nilai Transaksi')
                            ->money('IDR', locale: 'id_ID'),
                        Infolists\Components\TextEntry::make('services.name')
                            ->label('Layanan Ditawarkan / Terkait')
                            ->badge()
                            ->separator(', ')
                            ->columnSpanFull(),
                    ])->columns([
                        'default' => 1,
                        'md' => 2,
                    ]),

                Infolists\Components\Section::make('Catatan Aktivitas & Voice of Customer')
                    ->schema([
                        Infolists\Components\TextEntry::make('activity_description')
                            ->label('Deskripsi Kegiatan / Story Kunjungan')
                            ->placeholder('Tidak ada catatan kegiatan')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('action_plan')
                            ->label('Action Plan (Langkah Selanjutnya)')
                            ->placeholder('Tidak ada action plan')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('voc')
                            ->label('Voice of Customer (VOC / Feedback)')
                            ->placeholder('Tidak ada catatan VOC')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Status Validasi Supervisor')
                    ->schema([
                        Infolists\Components\TextEntry::make('validation_status')
                            ->label('Status Validasi')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Valid' => 'success',
                                'Rejected' => 'danger',
                                'Pending' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('validator.name')
                            ->label('Validator / Supervisor')
                            ->placeholder('Belum divalidasi'),
                        Infolists\Components\TextEntry::make('validated_at')
                            ->label('Waktu Validasi')
                            ->dateTime('d M Y, H:i')
                            ->placeholder('-'),
                        Infolists\Components\TextEntry::make('validation_notes')
                            ->label('Catatan Validasi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('#')
                    ->width('60px'),
                Tables\Columns\TextColumn::make('visit_date')
                    ->date('d M Y')
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable()
                    ->label('Account Manager')
                    ->description(fn(VisitReport $record): string => $record->employee?->telda?->name ? 'Telda: ' . $record->employee->telda->name : ''),
                Tables\Columns\TextColumn::make('businessCustomer.name')
                    ->searchable()
                    ->sortable()
                    ->label('Pelanggan (BC)')
                    ->limit(24)
                    ->tooltip(fn(VisitReport $record): string => $record->businessCustomer?->name ?? ''),
                Tables\Columns\TextColumn::make('activityCategory.name')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Approaching' => 'info',
                        'Dealing' => 'warning',
                        'Aftersales' => 'success',
                        default => 'gray',
                    })
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('visit_type')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'Visit' ? 'primary' : 'gray')
                    ->formatStateUsing(fn(string $state): string => $state === 'Visit' ? 'Direct' : 'Non-Visit')
                    ->description(fn(VisitReport $record): ?string => $record->visit_type === 'NonVisit'
                        ? ($record->communication_channel && $record->activity_topic
                            ? $record->communication_channel . ' • ' . $record->activity_topic
                            : ($record->communication_channel ?? $record->activity_topic ?? $record->activityType?->name))
                        : $record->activityType?->name)
                    ->label('Tipe & Kegiatan'),
                Tables\Columns\IconColumn::make('locations_count')
                    ->counts('locations')
                    ->label('GPS')
                    ->boolean()
                    ->trueIcon('heroicon-s-map-pin')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('info')
                    ->tooltip(fn(VisitReport $record) => $record->locations()->exists() ? 'GPS Terverifikasi' : 'Tanpa Titik GPS'),
                Tables\Columns\IconColumn::make('photos_count')
                    ->counts('photos')
                    ->label('Foto')
                    ->boolean()
                    ->trueIcon('heroicon-s-camera')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->tooltip(fn(VisitReport $record) => $record->photos()->count() . ' Foto Terlampir'),
                Tables\Columns\IconColumn::make('document_file_url')
                    ->label('Berkas')
                    ->boolean()
                    ->trueIcon('heroicon-s-document-check')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->tooltip(function (VisitReport $record): string {
                        $count = is_array($record->document_file_url) ? count($record->document_file_url) : (!empty($record->document_file_url) ? 1 : 0);
                        return $count > 0 ? "{$count} Berkas Terlampir" : 'Tanpa Lampiran Berkas';
                    }),
                Tables\Columns\TextColumn::make('services.name')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->label('Layanan'),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable()
                    ->label('Nilai Est.'),
                Tables\Columns\TextColumn::make('validation_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Valid' => 'success',
                        'Rejected' => 'danger',
                        'Pending' => 'warning',
                        default => 'gray',
                    })
                    ->label('Validasi'),
            ])
            ->defaultSort('visit_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('validation_status')
                    ->options([
                        'Pending' => 'Pending',
                        'Valid' => 'Valid',
                        'Rejected' => 'Rejected',
                    ])
                    ->label('Status Validasi'),
                Tables\Filters\SelectFilter::make('activity_category_id')
                    ->relationship('activityCategory', 'name')
                    ->label('Kategori Aktivitas'),
                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->label('Account Manager'),
                Tables\Filters\SelectFilter::make('visit_type')
                    ->options([
                        'Visit' => 'Direct Visit (Onsite)',
                        'NonVisit' => 'Non-Visit (Online)',
                    ])
                    ->label('Tipe Kunjungan'),
                Tables\Filters\SelectFilter::make('communication_channel')
                    ->options([
                        'Chat' => 'Chat (WhatsApp / Telegram)',
                        'Email' => 'Email Resmi',
                        'Video Conference' => 'Video Conference',
                    ])
                    ->label('Kanal Komunikasi'),
            ])
            ->actions([
                Tables\Actions\Action::make('quickValidate')
                    ->label('Validasi')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('validation_status')
                            ->options([
                                'Valid' => 'Setujui (Valid)',
                                'Rejected' => 'Tolak (Rejected)',
                            ])
                            ->default('Valid')
                            ->required()
                            ->label('Keputusan Validasi'),
                        Forms\Components\Textarea::make('validation_notes')
                            ->label('Catatan Validasi')
                            ->placeholder('Opsional, misal alasan penolakan atau catatan tindak lanjut'),
                    ])
                    ->action(function (VisitReport $record, array $data): void {
                        $record->update([
                            'validation_status' => $data['validation_status'],
                            'validation_notes' => $data['validation_notes'] ?? null,
                            'validated_at' => now(),
                        ]);
                    })
                    ->visible(fn(VisitReport $record): bool => $record->validation_status === 'Pending'),
                Tables\Actions\ViewAction::make()
                    ->modalWidth('5xl'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitReports::route('/'),
            'create' => Pages\CreateVisitReport::route('/create'),
            'edit' => Pages\EditVisitReport::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && auth()->user()->role === 'AM' && auth()->user()->employee_id) {
            $query->where('employee_id', auth()->user()->employee_id);
        }

        return $query;
    }
}
