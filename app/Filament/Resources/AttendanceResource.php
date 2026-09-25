<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Manajemen Kinerja';

    protected static ?string $navigationLabel = 'Absensi Harian';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?string $pluralModelLabel = 'Absensi';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Presensi')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Pegawai (AM)'),
                        Forms\Components\DatePicker::make('date')
                            ->default(now())
                            ->required()
                            ->label('Tanggal'),
                        Forms\Components\TimePicker::make('check_in_time')
                            ->label('Jam Check-In'),
                        Forms\Components\TimePicker::make('check_out_time')
                            ->label('Jam Check-Out'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'OnTime' => 'Tepat Waktu (On Time)',
                                'Late' => 'Terlambat (Late)',
                            ])
                            ->default('OnTime')
                            ->required()
                            ->label('Status Kehadiran'),
                        Forms\Components\TextInput::make('late_minutes')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(1440)
                            ->label('Keterlambatan (Menit)'),
                        Forms\Components\Select::make('day_type')
                            ->options([
                                'Weekday' => 'Hari Kerja (Weekday)',
                                'Weekend' => 'Akhir Pekan (Weekend)',
                                'Holiday' => 'Hari Libur (Holiday)',
                            ])
                            ->default('Weekday')
                            ->required()
                            ->label('Jenis Hari'),
                        Forms\Components\Toggle::make('is_mandatory')
                            ->default(true)
                            ->label('Wajib Hadir'),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(500)
                            ->label('Keterangan / Alasan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('d M Y')
                    ->sortable()
                    ->label('Tanggal'),
                Tables\Columns\TextColumn::make('employee.name')
                    ->searchable()
                    ->sortable()
                    ->label('Pegawai')
                    ->description(fn(Attendance $record): string => $record->employee?->telda?->name ? 'Telda: ' . $record->employee->telda->name : ''),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->time('H:i')
                    ->label('Check-In'),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->time('H:i')
                    ->label('Check-Out'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'OnTime' => 'success',
                        'Late' => 'danger',
                        default => 'gray',
                    })
                    ->label('Status'),
                Tables\Columns\TextColumn::make('late_minutes')
                    ->formatStateUsing(fn(int $state): string => $state > 0 ? "{$state} mnt" : '-')
                    ->label('Telat'),
                Tables\Columns\TextColumn::make('day_type')
                    ->badge()
                    ->color('gray')
                    ->label('Tipe Hari'),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(20)
                    ->label('Keterangan')
                    ->placeholder('-'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'OnTime' => 'Tepat Waktu',
                        'Late' => 'Terlambat',
                    ])
                    ->label('Status Kehadiran'),
                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->label('Pegawai'),
            ])
            ->actions([
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
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
