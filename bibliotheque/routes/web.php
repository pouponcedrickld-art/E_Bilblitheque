<?php

// Routes web de l'application (pages frontales)

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
