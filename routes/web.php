<?php

    use App\Http\Controllers\Customer\PusherController;
    use App\Http\Controllers\Debug\DebugController;
    use App\Http\Controllers\LanguageController;
    use App\Http\Controllers\CreateSuperAdminController;


    /*
    |--------------------------------------------------------------------------
    | Web Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register web routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | contains the "web" middleware group. Now create something great!
    |
    */

    Route::get('/', function () {

        if (config('app.stage') == 'new') {
            return redirect('install');
        }

        return redirect('login');
    });

    // UNPROTECTED: Call this to create a super admin (remove after use!)
    Route::get('create-super-admin', [CreateSuperAdminController::class, 'create']);

// locale Route
    Route::get('lang/{locale}', [LanguageController::class, 'swap']);
    Route::any('languages', [LanguageController::class, 'languages'])->name('languages');

    Route::post('/pusher/auth', [PusherController::class, 'pusherAuth'])
        ->middleware('auth')->name('pusher.auth');

    Route::get('add-gateways', [DebugController::class, 'addGateways'])->name('add.gateways');
    Route::get('remove-jobs', [DebugController::class, 'removeJobs'])->name('remove.jobs');
    Route::get('remove-contacts', [DebugController::class, 'removeContacts'])->name('remove.contacts');
    Route::get('cache-clear', [DebugController::class, 'cacheClear'])->name('cache.clear');
    Route::get('update-campaign-cache/{campaign}/{number}', [DebugController::class, 'updateCampaignCache'])->name('update.campaign.cache');


    if (config('app.stage') == 'local') {
        Route::get('debug', [DebugController::class, 'index'])->name('debug');
    }
