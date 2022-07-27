<?php

use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});


Route::get('/sync', [
    \App\Http\Controllers\SyncController::class,
    'index'
]);

Route::get('/sync/format', [
    \App\Http\Controllers\SyncController::class,
    'getSyncFormatData'
]);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/connector', \App\Http\Livewire\Connector::class)->name('connector');
    Route::get('/connector/{version}/add', \App\Http\Livewire\ConnectorAdd::class)->name('connector.add');
    Route::get('/connector/{id}/manage', \App\Http\Livewire\ConnectorManage::class)->name('connector.manage');
    Route::get('/connector/family/{familyUUID}', \App\Http\Livewire\Mapping::class)->name('connector.mapping');
});
