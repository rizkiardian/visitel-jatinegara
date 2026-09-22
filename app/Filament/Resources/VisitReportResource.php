<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitReportResource\Pages;
use App\Models\VisitReport;
use Filament\Forms;
use Filament\Forms\Form;
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
                Forms\Components\Section::make('Detail Kunjungan')
                    ->description('Informasi waktu dan personil yang melakukan kunjungan.')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Account Manager (AM)'),
                        Forms\Components\Select::make('business_customer_id')
                            ->relationship('businessCustomer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Business Customer (BC)'),
                        Forms\Components\TextInput::make('customer_pic_name')
                            ->label('Nama PIC Pelanggan')
                            ->placeholder('Nama kontak saat kunjungan'),
                        Forms\Components\Select::make('visit_type')
                            ->options([
                                'Visit' => 'Direct Visit (Kunjungan Langsung)',
                                'NonVisit' => 'Non-Visit (Call / Online)',
                            ])
                            ->required()
                            ->default('Visit')
                            ->label('Tipe Kunjungan'),
                        Forms\Components\DatePicker::make('visit_date')
                            ->default(now())
                            ->required()
                            ->label('Tanggal Kunjungan'),
                        Forms\Components\TimePicker::make('visit_time')
                            ->default(now()->format('H:i'))
                            ->label('Waktu Kunjungan'),
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
                Tables\Actions\ViewAction::make(),
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
}
