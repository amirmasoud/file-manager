<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Auth::routes();

Route::get('block/non-ir', function () {
    return view('block');
});

Route::get('/home', 'HomeController@index');

// Route::get('login', function () {
//     // auth()->loginUsingId(1);
//     // return redirect()->intended();
// });

Route::get('download','DownloadVideoController@get')
    ->middleware(['fw-only-whitelisted'])
    ->name('download');

Route::get('/home', 'HomeController@index');

$middleware = array_merge(\Config::get('lfm.middlewares'), [
    '\Unisharp\Laravelfilemanager\middlewares\MultiUser',
    '\Unisharp\Laravelfilemanager\middlewares\CreateDefaultFolder'
]);
$prefix = \Config::get('lfm.prefix', 'laravel-filemanager');
$as = 'unisharp.lfm.';

// make sure authenticated
Route::group(compact('middleware', 'prefix', 'as'), function () {
    // list images & files
    Route::get('/jsonitems', [
        'uses' => 'ItemsController@getItems',
        'as' => 'getItems'
    ]);

    // upload
    Route::any('/upload', [
        'uses' => 'UploadController@upload',
        'as' => 'upload'
    ]);

    Route::get('perm/{role}/{action}/{file}', 'PermissionController@takeAction')->middleware('auth');
    Route::get('encode', 'EncodeController@start')->middleware('auth');
});
