<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Manajemen Pegawai';

    protected static ?string $navigationLabel = 'Daftar Pegawai (AM)';

    protected static ?string $modelLabel = 'Pegawai';

    protected static ?string $pluralModelLabel = 'Pegawai';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Biodata Pegawai')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Nama Lengkap'),
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP (Nomor Induk Pegawai)'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->label('Email Dinas / Telkom'),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->label('Nomor WhatsApp / HP'),
                        Forms\Components\Select::make('role_id')
                            ->relationship('role', 'name')
                            ->required()
                            ->label('Peran (Role)'),
                        Forms\Components\Select::make('telda_id')
                            ->relationship('telda', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Wilayah Kerja (Telda)'),
                        Forms\Components\Select::make('supervisor_id')
                            ->relationship('supervisor', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Atasan Langsung (Supervisor)'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Status Aktif'),
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
                    ->width('50px'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Pegawai'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label('Email'),
                Tables\Columns\TextColumn::make('role.name')
                    ->badge()
                    ->color('primary')
                    ->label('Peran'),
                Tables\Columns\TextColumn::make('telda.name')
                    ->badge()
                    ->color('gray')
                    ->label('Telda'),
                Tables\Columns\TextColumn::make('supervisor.name')
                    ->label('Atasan')
                    ->placeholder('-'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),
                Tables\Columns\TextColumn::make('visit_reports_count')
                    ->counts('visitReports')
                    ->label('Total Laporan')
                    ->sortable(),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
                    ->relationship('role', 'name')
                    ->label('Peran (Role)'),
                Tables\Filters\SelectFilter::make('telda_id')
                    ->relationship('telda', 'name')
                    ->label('Telda'),
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
