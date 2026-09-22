<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessCustomerResource\Pages;
use App\Models\BusinessCustomer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessCustomerResource extends Resource
{
    protected static ?string $model = BusinessCustomer::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Business Customer';

    protected static ?string $modelLabel = 'Business Customer';

    protected static ?string $pluralModelLabel = 'Business Customer';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas Pelanggan')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->label('Nama Perusahaan / BC')
                            ->placeholder('PT Telkom Indonesia')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('nipnas')
                            ->label('NIPNAS')
                            ->placeholder('Nomor NIPNAS Telkom'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'New' => 'Baru (New)',
                                'Existing' => 'Eksisting (Existing)',
                            ])
                            ->required()
                            ->default('New')
                            ->label('Status Pelanggan'),
                        Forms\Components\Select::make('telda_id')
                            ->relationship('telda', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Wilayah Kerja (Telda)'),
                        Forms\Components\Select::make('service_id')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Layanan Utama'),
                        Forms\Components\TextInput::make('segment')
                            ->label('Segmen Pelanggan')
                            ->placeholder('Enterprise / SME / Government'),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak & Lokasi')
                    ->schema([
                        Forms\Components\TextInput::make('default_pic_name')
                            ->label('Nama PIC Utama'),
                        Forms\Components\TextInput::make('default_pic_contact')
                            ->label('Kontak PIC (No. Telp / Email)'),
                        Forms\Components\Textarea::make('address')
                            ->rows(2)
                            ->label('Alamat Kantor BC')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->label('Latitude GPS')
                            ->placeholder('-6.215000'),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->label('Longitude GPS')
                            ->placeholder('106.870000'),
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
                    ->label('Nama Pelanggan (BC)'),
                Tables\Columns\TextColumn::make('nipnas')
                    ->searchable()
                    ->label('NIPNAS')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'New' => 'primary',
                        'Existing' => 'success',
                        default => 'gray',
                    })
                    ->label('Status'),
                Tables\Columns\TextColumn::make('telda.name')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->label('Telda'),
                Tables\Columns\TextColumn::make('segment')
                    ->label('Segmen')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('default_pic_name')
                    ->label('PIC Utama')
                    ->description(fn(BusinessCustomer $record): string => $record->default_pic_contact ?? '')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('visit_reports_count')
                    ->counts('visitReports')
                    ->label('Total Visit')
                    ->sortable(),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'New' => 'Baru (New)',
                        'Existing' => 'Eksisting (Existing)',
                    ])
                    ->label('Status Pelanggan'),
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
            'index' => Pages\ListBusinessCustomers::route('/'),
            'create' => Pages\CreateBusinessCustomer::route('/create'),
            'edit' => Pages\EditBusinessCustomer::route('/{record}/edit'),
        ];
    }
}
