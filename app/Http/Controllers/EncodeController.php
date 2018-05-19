<?php

namespace App\Http\Controllers;

use File;
use App\Jobs\ProcessVideo;
use Illuminate\Http\Request;
use Unisharp\Laravelfilemanager\Events\ImageWasUploaded;

class EncodeController extends Controller
{
    public function start()
    {
        $path = storage_path('app' . request('path'));
        if (File::exists($path)) {
            // event(new ImageWasUploaded($path));
            ProcessVideo::dispatch($path)->delay(now()->addSecond());
            return response()->json([], 204);
        } else {
            abort(404);
        }
    }
}
