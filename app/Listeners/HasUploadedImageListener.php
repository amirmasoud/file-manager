<?php
namespace App\Listeners;

use App\FilePath;
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
        // Get te public path to the file and save it to the database
        $publicFilePath = str_replace(public_path(), "", $event->path());
        $filePath = FilePath::create([
            'path' => $publicFilePath,
        ]);
        $lowBitrate = (new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))->setKiloBitrate(250);
        $midBitrate = (new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))->setKiloBitrate(500);
        $highBitrate = (new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))->setKiloBitrate(1024);

        $input = str_replace(storage_path() . '/app/', '', $filePath->path);
        $basename = basename($input);
        $online_stream_directory = 'public/' . str_replace($basename, '', $input) . str_replace('.', '_', $basename);
        \FFMpeg::fromDisk('local')
            ->open($input)
            ->exportForHLS()
            ->setPlaylistPath('hello')
            ->setSegmentLength(10)
            ->addFormat($lowBitrate)
            // ->addFormat($midBitrate)
            ->addFormat($highBitrate)
            ->save($online_stream_directory . '/online.m3u8');
        // ProcessVideo::dispatch($filePath);
    }
}
