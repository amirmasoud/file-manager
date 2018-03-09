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

Route::get('/', function () {
    $lowBitrate = (new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))->setKiloBitrate(250);
    $midBitrate = (new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))->setKiloBitrate(500);
    $highBitrate = (new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))->setKiloBitrate(1000);
    \Log::debug('Message');
    // dd(FFMpeg::fromDisk('movie')
    //     ->open('Movie/720P_ (2).mp4')
    //     ->exportForHLS()
    //     ->onProgress(function ($percentage) {
    //         echo "$percentage % transcoded";
    //     })
    //     ->setSegmentLength(10)
    //     ->addFormat($lowBitrate)
    //     ->addFormat($midBitrate)
    //     ->addFormat($highBitrate)
    //     ->save('Movie/online/adaptive_steve.m3u8'));
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::get('login', function () {
    // auth()->loginUsingId(1);
    // return redirect()->intended();
});

Route::get('download','DownloadVideoController@get')->name('download');

Auth::routes();

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
});
