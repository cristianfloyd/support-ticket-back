<?php

use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Password;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Settings\Appearance;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketPdfController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('/return-to-original-user', function() {
    if (session()->has('impersonated_by')) {
        $originalId = session()->pull('impersonated_by');
        Auth::loginUsingId($originalId);
        return redirect()->route('dashboard')->with('success', 'Has vuelto a tu cuenta original');
    }

    return redirect()->route('dashboard');
})->name('return-to-original-user');

Route::get('/tickets/{ticket}/pdf', [TicketPdfController::class, 'generate'])
    ->name('tickets.pdf')
    ->middleware(['auth']);

require __DIR__.'/auth.php';
