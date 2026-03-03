<?php
/**
 * TicketClassificationResource
 *
 * Resource Filament untuk manajemen klasifikasi tiket.
 *
 * @package App\Filament\Resources
 */

namespace App\Filament\Resources;

use App\Filament\Resources\TicketClassificationResource\Pages;
use App\Models\TicketClassification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class TicketClassificationResource extends Resource
{
    protected static ?string $model = TicketClassification::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 6;

    /**
     * Mendapatkan label navigasi untuk resource ini.
     *
     * @return string Label navigasi (diterjemahkan)
     */
    public static function getNavigationLabel(): string
    {
        return __('Classification');
    }

    /**
     * Mendapatkan label jamak (plural) untuk resource ini.
     *
     * @return string|null Label plural (diterjemahkan)
     */
    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    /**
     * Mendapatkan grup navigasi untuk resource ini.
     *
     * @return string|null Nama grup navigasi (diterjemahkan)
     */
    public static function getNavigationGroup(): ?string
    {
        return __('Referential');
    }

    /**
     * Mendefinisikan form input untuk create/edit klasifikasi tiket.
     *
     * @param Form $form Instance form Filament
     * @return Form Form dengan skema input
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                // Input nama klasifikasi
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Classification name'))
                                    ->required()
                                    ->maxLength(255),

                                // Picker warna klasifikasi
                                Forms\Components\ColorPicker::make('color')
                                    ->label(__('Classification color'))
                                    ->required(),
                            ])
                    ])
            ]);
    }

    /**
     * Mendefinisikan tabel daftar klasifikasi tiket.
     *
     * @param Table $table Instance tabel Filament
     * @return Table Tabel dengan kolom dan aksi
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kolom warna klasifikasi
                Tables\Columns\ColorColumn::make('color')
                    ->label(__('Color'))
                    ->sortable()
                    ->searchable(),

                // Kolom nama klasifikasi
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                // Tambahkan filter jika diperlukan
            ])
            ->actions([
                // Aksi edit per baris
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Aksi hapus massal
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /**
     * Mendefinisikan relasi yang tersedia untuk resource ini.
     *
     * @return array Daftar relasi
     */
    public static function getRelations(): array
    {
        return [
            // Tambahkan relasi jika ada
        ];
    }

    /**
     * Mendefinisikan halaman-halaman (routes) untuk resource ini.
     *
     * @return array Daftar halaman dan route-nya
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTicketClassifications::route('/'),
            'create' => Pages\CreateTicketClassification::route('/create'),
            'edit' => Pages\EditTicketClassification::route('/{record}/edit'),
        ];
    }
}
