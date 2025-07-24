<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MilestoneResource\Pages;
use App\Filament\Resources\MilestoneResource\RelationManagers;
use App\Models\Milestone;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\DateColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;

class MilestoneResource extends Resource
{
    protected static ?string $model = Milestone::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?int $navigationSort = 6;

    /**
     * Mendapatkan label navigasi untuk resource ini.
     *
     * @return string Label navigasi (diterjemahkan)
     */
    protected static function getNavigationLabel(): string
    {
        return __('Milestone');
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->label('Nama Milestone'),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3),

            DatePicker::make('start_date')
                ->label('Tanggal Mulai'),

            DatePicker::make('end_date')
                ->label('Tanggal Selesai'),

            Select::make('progress_status')
                ->label('Status Progres')
                ->searchable()
                ->options([
                    'Belum Mulai' => 'Belum Mulai',
                    'Sedang Berjalan' => 'Sedang Berjalan',
                    'Selesai' => 'Selesai',
                ])
                ->default('Belum Mulai')
                ->required(),

            Select::make('access_status')
                ->label('Status Akses')
                ->searchable()
                ->options([
                    'Open' => 'Open',
                    'Closed' => 'Closed',
                ])
                ->default('Open')
                ->required(),
            ColorPicker::make('color')
                ->label(__('Type color'))
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label(__('Color'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')->label('Nama')->sortable()->searchable(),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date()
                    ->sortable(),
                BadgeColumn::make('progress_status')
                    ->label('Progres')
                    ->colors([
                        'primary' => 'Belum Mulai',
                        'warning' => 'Sedang Berjalan',
                        'success' => 'Selesai',
                    ]),

                BadgeColumn::make('access_status')
                    ->label('Akses')
                    ->colors([
                        'info' => 'Open',
                        'danger' => 'Closed',
                    ]),
            ])
            ->filters([
                SelectFilter::make('progress_status')
                    ->label('Status Progres')
                    ->options([
                        'Belum Mulai' => 'Belum Mulai',
                        'Sedang Berjalan' => 'Sedang Berjalan',
                        'Selesai' => 'Selesai',
                    ]),

                SelectFilter::make('access_status')
                    ->label('Status Akses')
                    ->options([
                        'Open' => 'Open',
                        'Closed' => 'Closed',
                    ]),
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
            'index' => Pages\ListMilestones::route('/'),
            'create' => Pages\CreateMilestone::route('/create'),
            'edit' => Pages\EditMilestone::route('/{record}/edit'),
        ];
    }
}
