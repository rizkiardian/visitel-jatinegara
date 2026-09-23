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
                                'Visit' => 'Direct Visit (Kunjungan Langsung)',
                                'NonVisit' => 'Non-Visit (Call / Online)',
                            ])
                            ->required()
                            ->default('Visit')
                            ->live()
                            ->label('Tipe Kunjungan'),
                        Forms\Components\Select::make('business_customer_id')
                            ->relationship(
                                name: 'businessCustomer',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                    $employeeId = $get('employee_id') ?? auth()->user()?->employee_id;
                                    if ($employeeId) {
                                        $query->where('employee_id', $employeeId);
                                    }
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
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
                    ])->columns(2),

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

                Forms\Components\Section::make('Dokumentasi Foto Kunjungan')
                    ->description('Unggah atau ambil foto langsung saat berada di lokasi kunjungan.')
                    ->visible(fn(Forms\Get $get): bool => $get('visit_type') === 'Visit' || $get('visit_type') === null)
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
                            ->helperText('Ambil foto saat berdiskusi atau bersama PIC customer.')
                            ->columnSpan(1),
                    ])->columns(2),

                Forms\Components\Section::make('Funnel Penjualan & Layanan')
                    ->description('Katalog layanan dan klasifikasi funnel bisnis Telkom.')
                    ->schema([
                        Forms\Components\Select::make('activity_category_id')
                            ->relationship('activityCategory', 'name')
                            ->required()
                            ->label('Kategori Aktivitas (Funnel)'),
                        Forms\Components\Select::make('activity_type_id')
                            ->relationship('activityType', 'name')
                            ->label('Jenis Kegiatan'),
                        Forms\Components\Select::make('r_level_id')
                            ->relationship('rLevel', 'name')
                            ->label('R-Level (Tingkat Kunjungan)'),
                        Forms\Components\TextInput::make('estimated_value')
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Estimasi Nilai Transaksi (Rp)')
                            ->placeholder('0'),
                        Forms\Components\CheckboxList::make('services')
                            ->relationship('services', 'name')
                            ->columns(4)
                            ->columnSpanFull()
                            ->label('Layanan Ditawarkan / Terkait'),
                    ])->columns(2),

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
                    ])->columns(2),
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
                            ->color(fn(string $state): string => $state === 'Visit' ? 'primary' : 'gray'),
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
                    ])->columns(2),

                Infolists\Components\Section::make('Lokasi GPS & Peta Kunjungan')
                    ->schema([
                        Infolists\Components\ViewEntry::make('gps_location')
                            ->view('filament.infolists.components.gps-location-view')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Dokumentasi Foto Kunjungan')
                    ->schema([
                        Infolists\Components\ViewEntry::make('visit_photos')
                            ->view('filament.infolists.components.visit-photos-view')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Funnel Penjualan & Layanan')
                    ->schema([
                        Infolists\Components\TextEntry::make('activityCategory.name')
                            ->label('Kategori Funnel')
                            ->badge(),
                        Infolists\Components\TextEntry::make('activityType.name')
                            ->label('Jenis Kegiatan')
                            ->placeholder('-'),
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
                    ])->columns(2),

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
                    ])->columns(3),
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
                    ->label('Tipe'),
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
                        'Visit' => 'Visit',
                        'NonVisit' => 'Non-Visit',
                    ])
                    ->label('Tipe Kunjungan'),
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
