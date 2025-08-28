<?php

namespace App\Filament\Resources\TokenResource\Pages;

use App\Models\User;
use Filament\Pages\Actions;
use Illuminate\Support\Carbon;
use App\Filament\Resources\TokenResource;
use Filament\Resources\Pages\CreateRecord;

class CreateToken extends CreateRecord
{
    protected static string $resource = TokenResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Ambil user dari tokenable_id
        $user = User::findOrFail($data['tokenable_id']);

        // Buat token via Sanctum (ini yang generate otomatis field "token")
        $token = $user->createToken(
            $data['name'],    // nama token
            ['*'],            // abilities
            Carbon::parse($data['expires_at']) // expired
        );

        // simpan plain token di DB
        $accessToken = $token->accessToken;
        $accessToken->plain_token = $token->plainTextToken;
        $accessToken->save();

        session()->flash('generated_token', $token->plainTextToken);

        return $token->accessToken;
    }

    protected function getRedirectUrl(): string
    {
        // setelah simpan, redirect ke list
        return $this->getResource()::getUrl('index');
    }
}
