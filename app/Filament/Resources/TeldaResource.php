<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeldaResource\Pages;
use App\Models\Telda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeldaResource extends Resource
{
    protected static ?string $model = Telda::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Wilayah Kerja (Telda)';

    protected static ?string $modelLabel = 'Telda';

    protected static ?string $pluralModelLabel = 'Telda';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->label('Nama Telda')
                    ->placeholder('Contoh: PSM, KBY, RMG, JTN'),
                Forms\Components\Select::make('witel_id')
                    ->relationship('witel', 'name')
                    ->required()
                    ->label('Witel Induk'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->label('#'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->label('Nama Telda'),
                Tables\Columns\TextColumn::make('witel.name')->badge()->color('primary')->label('Witel Induk'),
                Tables\Columns\TextColumn::make('employees_count')->counts('employees')->label('Jumlah AM'),
                Tables\Columns\TextColumn::make('business_customers_count')->counts('businessCustomers')->label('Jumlah BC'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeldas::route('/'),
            'create' => Pages\CreateTelda::route('/create'),
            'edit' => Pages\EditTelda::route('/{record}/edit'),
        ];
    }
}
