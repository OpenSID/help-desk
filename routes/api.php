<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Route API untuk webhook GitHub.
 * - Menggunakan controller GitHubWebhookController untuk menangani webhook.
 */
Route::post('/github/webhook', [App\Http\Controllers\GitHubWebhookController::class, 'handle'])->name('github.webhook');

// # test token api
// Route::middleware('auth:sanctum')->get('/test', function (Request $request) {
//     return response()->json([
//         'message' => 'Token valid!',
//         'user'    => $request->user(),
//     ]);
// });
