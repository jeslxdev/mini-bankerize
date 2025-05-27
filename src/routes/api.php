<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProposalController;

Route::group([], function () {
    Route::post('/proposal', [ProposalController::class, 'store']);
});
