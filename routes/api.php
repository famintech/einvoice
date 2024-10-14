<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Platform\LoginMain;

Route::post('/get-accesstoken', [LoginMain::class, 'login']);
