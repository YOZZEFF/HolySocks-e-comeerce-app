<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/storage/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*');

Route::get('/test', function (Request $request) {
    return response()->json([
        'uri' => $request->getUri(),
        'path' => $request->path(),
        'url' => $request->url(),
        'full_url' => $request->fullUrl(),
        'method' => $request->method(),
    ]);
});

Route::get('/test2', function () {
    return response('test2 ok');
});

Route::fallback(function () {
    return response()->json(['fallback' => true, 'path' => request()->path()]);
});
