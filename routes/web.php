<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::post('/demo/dropzone-upload', function (Request $request) {
    $request->validate([
        'file' => ['required', 'file', 'max:5120'],
    ]);

    $file = $request->file('file');
    $storedPath = $file->store('demo-uploads');

    return response()->json([
        'name' => $file->getClientOriginalName(),
        'path' => $storedPath,
        'size' => $file->getSize(),
        'type' => $file->getMimeType(),
    ]);
})->name('demo.dropzone-upload');
