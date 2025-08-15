<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    protected static function getNavigationLabel(): string
    {
        return __('Users');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Permissions');
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
                                    ->label(__('Full name'))
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label(__('Email address'))
                                    ->email()
                                    ->required()
                                    ->rule(
                                        fn ($record) => 'unique:users,email,'
                                            . ($record ? $record->id : 'NULL')
                                            . ',id,deleted_at,NULL'
                                    )
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('telegram_id')
                                    ->label(__('Telegram Id'))
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('github_username')
                                    ->label(__('GitHub Username'))
                                    ->maxLength(255),

                                Forms\Components\CheckboxList::make('roles')
                                    ->label(__('Permission roles'))
                                    ->required()
                                    ->columns(3)
                                    ->relationship(
                                        'roles',
                                        'name',
                                        function ($query) {
                                            if (!Auth::user()->hasRole('Superadmin')) {
                                                $query->where('name', '!=', 'Superadmin');
                                            }
                                        }
                                    )
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Full name'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email address'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('telegram_id')
                    ->label(__('Telegram id'))
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ?: '-'),

                Tables\Columns\TagsColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->limit(2),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('Email verified at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ?: '-'),

                Tables\Columns\TextColumn::make('socials')
                    ->label(__('Linked social networks'))
                    ->view('partials.filament.resources.social-icon'),

                Tables\Columns\TextColumn::make('github_username')
                    ->label(__('GitHub Username'))
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ?: '-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verifyEmail')
                    ->label('Verifikasi Email')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) =>
                        Auth::user()->hasRole('Superadmin')
                    )
                    ->disabled(fn ($record) => !is_null($record->email_verified_at))
                    ->action(function ($record) {
                        $record->update([
                            'email_verified_at' => Carbon::now(),
                        ]);
                        Notification::make()
                            ->title('Email berhasil diverifikasi')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon('heroicon-o-key')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn () => Auth::user()->hasRole('Superadmin'))
                    ->action(function ($record) {
                        $record->update([
                            'password' => Hash::make('12345678'),
                        ]);
                        Notification::make()
                            ->title('Password berhasil direset ke 12345678')
                            ->success()
                            ->send();
                    })
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
