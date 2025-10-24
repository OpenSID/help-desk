<?php

use App\Models\User;
use App\Models\Ticket;
use App\Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoadMap\DataController;
use App\Http\Controllers\Auth\OidcAuthController;
use App\Http\Controllers\Public\PublicTicketController;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Kanban;
use App\Filament\Pages\Scrum;

Route::view('/', 'index')->name('home');
// Route::redirect('/dashboard', '/admin/dashboard') ;

// Dashboard route - redirect to Board page
// Route::get('/dashboard', function () {
//     return redirect()->to(route('filament.pages.board'));
// })->name('filament.pages.dashboard');

Route::get('captcha', function () {
    return captcha_img('flat');
});

// Share ticket
Route::get('/tickets/public/check', [PublicTicketController::class, 'getCaptcha'])->name('tickets.public.captcha');
Route::post('/tickets/public/check', [PublicTicketController::class, 'checkTicket'])->name('tickets.public.check');

Route::get('/tickets/share/{ticket:code}', function (Ticket $ticket) {
    return redirect()->to(route('filament.resources.tickets.view', $ticket));
})->name('filament.resources.tickets.share');

// Validate an account
Route::get('/validate-account/{user:creation_token}', function (User $user) {
    return view('validate-account', compact('user'));
})
    ->name('validate-account')
    ->middleware([
        'web',
        DispatchServingFilamentEvent::class
    ]);

// Login default redirection
Route::redirect('/login-redirect', '/login')->name('login');

// Road map JSON data
Route::get('road-map/data/{project}', [DataController::class, 'data'])
    ->middleware(['verified', 'auth'])
    ->name('road-map.data');

Route::name('oidc.')
    ->prefix('oidc')
    ->group(function () {
        Route::get('redirect', [OidcAuthController::class, 'redirect'])->name('redirect');
        Route::get('callback', [OidcAuthController::class, 'callback'])->name('callback');
    });

// Route::get('/kanban/{project}', Kanban::class)->name('filament.pages.kanban');
// Route::get('/scrum/{project}', Scrum::class)->name('filament.pages.scrum');
