<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Token;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TokenResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TokenResource\RelationManagers;

class TokenResource extends Resource
{
    protected static ?string $model = Token::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    // tambahkan ini supaya muncul di MANAGEMENT
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $navigationLabel = 'Tokens';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Token Name')
                                    ->required(),

                                Forms\Components\Select::make('tokenable_id')
                                    ->label('User')
                                    ->searchable()
                                    ->options(fn() => \App\Models\User::pluck('name', 'id'))
                                    ->required(),

                                // penting: tambahkan ini biar tokenable_type otomatis ke User
                                Forms\Components\Hidden::make('tokenable_type')
                                    ->default(\App\Models\User::class)
                                    ->required(),

                                Forms\Components\DateTimePicker::make('expires_at')
                                    ->label('Expired At')
                                    ->default(now('Asia/Jakarta')->addYear())
                                    ->required(),
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable(),

                Tables\Columns\TextColumn::make('plain_token')
                    ->label('Token')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('tokenable.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expired At')
                    ->dateTime()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        if ($record->expires_at && $record->expires_at->isPast()) {
                            return 'Token expired';
                        }
                        return $record->expires_at ? $record->expires_at->format('M d, Y H:i:s') : '-';
                    })
                    ->color(function ($record) {
                        return $record->expires_at && $record->expires_at->isPast() ? 'danger' : null;
                    }),
                Tables\Columns\TextColumn::make('last_used_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created At'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('copy_token')
                    ->label('Copy Token')
                    ->icon('heroicon-o-clipboard')
                    ->color('success') // hijau
                    ->action(function (Token $record, $livewire) {
                        $livewire->dispatchBrowserEvent('copy-token', [
                            'token' => $record->plain_token,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Token copied!')
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListTokens::route('/'),
            'create' => Pages\CreateToken::route('/create'),
            'edit' => Pages\EditToken::route('/{record}/edit'),
        ];
    }
}
