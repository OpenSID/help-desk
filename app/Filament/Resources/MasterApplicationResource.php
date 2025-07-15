<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterApplicationResource\Pages;
use App\Filament\Resources\MasterApplicationResource\RelationManagers;
use App\Models\MasterApplication;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterApplicationResource extends Resource
{
    protected static ?string $model = MasterApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 6;

    protected static function getNavigationLabel(): string
    {
        return __('Master Application');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Referential');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Type name'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\ColorPicker::make('color')
                                    ->label(__('Type color'))
                                    ->required(),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label(__('Color'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterApplications::route('/'),
            'create' => Pages\CreateMasterApplication::route('/create'),
            'edit' => Pages\EditMasterApplication::route('/{record}/edit'),
        ];
    }
}
