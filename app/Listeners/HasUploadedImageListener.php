<?php
namespace App\Listeners;

use App\Jobs\ProcessVideo;
use Unisharp\Laravelfilemanager\Events\ImageWasUploaded;

class HasUploadedImageListener
{
    /**
     * Handle the event.
     *
     * @param  ImageWasUploaded  $event
     * @return void
     */
    public function handle(ImageWasUploaded $event)
    {
        ProcessVideo::dispatch($event->path())->delay(now()->addSecond());
    }
}
