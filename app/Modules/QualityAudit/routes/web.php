<?php

use Illuminate\Support\Facades\Route;

Route::get('/modules/quality-audit', function () {
    return view('module-quality-audit::index');
})->name('modules.quality-audit.index');
