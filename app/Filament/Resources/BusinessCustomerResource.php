<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessCustomerResource\Pages;
use App\Models\BusinessCustomer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessCustomerResource extends Resource
{
    protected static ?string $model = BusinessCustomer::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Business Customer';

    protected static ?string $modelLabel = 'Business Customer';

    protected static ?string $pluralModelLabel = 'Business Customer';

    protected static ?int $navigationSort = 2;

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Identitas Pelanggan')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Nama Perusahaan / BC')
                        ->placeholder('PT Telkom Indonesia')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('employee_id')
                        ->relationship('employee', 'name')
                        ->searchable()
                        ->preload()
                        ->default(fn () => auth()->user()?->employee_id)
                        ->disabled(fn () => auth()->user()?->role !== 'Admin')
                        ->dehydrated()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state && ($emp = \App\Models\Employee::find($state))) {
                                $set('telda_id', $emp->telda_id);
                            }
                        })
                        ->label('Account Manager (AM) PIC'),
                    Forms\Components\TextInput::make('nipnas')
                        ->maxLength(50)
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
                        ->default(function (Forms\Get $get) {
                            $empId = $get('employee_id') ?? auth()->user()?->employee_id;
                            return $empId ? \App\Models\Employee::find($empId)?->telda_id : null;
                        })
                        ->label('Wilayah Kerja (Telda)'),
                    Forms\Components\Select::make('service_id')
                        ->relationship('service', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Layanan Utama'),
                    Forms\Components\Select::make('segment')
                        ->options([
                            'Enterprise' => [
                                'Energi' => 'Energi',
                                'Oil & Gas' => 'Oil & Gas',
                                'Manufaktur' => 'Manufaktur',
                                'Multifinance' => 'Multifinance',
                                'Keuangan' => 'Keuangan / Perbankan',
                                'Property' => 'Property & Real Estate',
                                'Logistik' => 'Logistik',
                                'Ekspedisi' => 'Ekspedisi',
                                'Healthcare' => 'Healthcare (Kesehatan/RS)',
                                'Media & Komunikasi' => 'Media & Komunikasi',
                                'ISP' => 'ISP / Telekomunikasi',
                                'Digital IT' => 'Digital & Teknologi IT',
                                'Hotel' => 'Hospitality & Hotel',
                                'Retail' => 'Retail',
                                'Distributor' => 'Distributor',
                                'F&B & Entertainment' => 'F&B & Entertainment',
                                'Agri' => 'Agrikultur & Perkebunan',
                                'Transportasi' => 'Transportasi',
                            ],
                            'SME / Indibiz' => [
                                'Indibiz' => 'Indibiz Umum',
                                'Indibiz Ruko' => 'Indibiz Ruko',
                                'Indibiz Manufaktur' => 'Indibiz Manufaktur',
                                'Indibiz Building' => 'Indibiz Building / Gedung',
                                'Indibiz Media & Komunikasi' => 'Indibiz Media & Komunikasi',
                                'Indibiz Logistik' => 'Indibiz Logistik',
                                'Indibiz Ekspedisi' => 'Indibiz Ekspedisi',
                                'Indibiz Healthcare' => 'Indibiz Healthcare',
                                'Indibiz Edukasi' => 'Indibiz Edukasi',
                                'Indibiz Property' => 'Indibiz Property',
                                'Indibiz ISP' => 'Indibiz ISP',
                                'Ruko' => 'Ruko / Pertokoan',
                            ],
                            'Government & Edukasi' => [
                                'Pemerintahan' => 'Pemerintahan (Government)',
                                'Sekolah' => 'Pendidikan / Sekolah',
                                'Yayasan' => 'Yayasan / Sosial',
                            ],
                            'Lainnya' => [
                                'Lainnya' => 'Lainnya',
                            ],
                        ])
                        ->searchable()
                        ->preload()
                        ->label('Segmen Pelanggan'),
                ])->columns(2),

            Forms\Components\Section::make('Kontak & Lokasi')
                ->schema([
                    Forms\Components\TextInput::make('default_pic_name')
                        ->maxLength(100)
                        ->label('Nama PIC Utama'),
                    Forms\Components\TextInput::make('default_pic_contact')
                        ->maxLength(100)
                        ->label('Kontak PIC (No. Telp / Email)'),
                    Forms\Components\Textarea::make('address')
                        ->rows(2)
                        ->maxLength(500)
                        ->label('Alamat Kantor BC')
                        ->columnSpanFull(),
                ])->columns(2),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::getFormSchema());
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
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Account Manager (AM)')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
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
                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Account Manager'),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && auth()->user()->role === 'AM' && auth()->user()->employee_id) {
            $query->where('employee_id', auth()->user()->employee_id);
        }

        return $query;
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
