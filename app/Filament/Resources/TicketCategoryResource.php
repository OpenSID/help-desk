<?php
/**
 * TicketCategoryResource
 *
 * Resource Filament untuk manajemen kategori tiket (Solution Categories).
 *
 * - Menyediakan form input untuk nama dan warna kategori.
 * - Menampilkan tabel daftar kategori dengan kolom warna dan nama.
 * - Mendukung aksi edit dan hapus massal.
 * - Navigasi di grup "Referential" dengan ikon koleksi.
 *
 * @package App\Filament\Resources
 */

namespace App\Filament\Resources;

use App\Filament\Resources\TicketCategoryResource\Pages;
use App\Models\TicketCategory;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class TicketCategoryResource extends Resource
{
    protected static ?string $model = TicketCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?int $navigationSort = 5;

    /**
     * Mendapatkan label navigasi untuk resource ini.
     *
     * @return string Label navigasi (diterjemahkan)
     */
    protected static function getNavigationLabel(): string
    {
        return __('Solution Categories');
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
    protected static function getNavigationGroup(): ?string
    {
        return __('Referential');
    }

    /**
     * Mendefinisikan form input untuk create/edit kategori tiket.
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
                                // Input nama kategori
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Type name'))
                                    ->required()
                                    ->maxLength(255),

                                // Picker warna kategori
                                Forms\Components\ColorPicker::make('color')
                                    ->label(__('Type color'))
                                    ->required(),
                            ])
                    ])
            ]);
    }

    /**
     * Mendefinisikan tabel daftar kategori tiket.
     *
     * @param Table $table Instance tabel Filament
     * @return Table Tabel dengan kolom dan aksi
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kolom warna kategori
                Tables\Columns\ColorColumn::make('color')
                    ->label(__('Color'))
                    ->sortable()
                    ->searchable(),

                // Kolom nama kategori
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
            'index' => Pages\ListTicketCategories::route('/'),
            'create' => Pages\CreateTicketCategory::route('/create'),
            'edit' => Pages\EditTicketCategory::route('/{record}/edit'),
        ];
    }
}
