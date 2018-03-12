<?php
namespace App\Listeners;

use Log;
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
        Log::debug('Started');
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
            ->setSegmentLength(10)
            ->addFormat($lowBitrate)
            ->addFormat($midBitrate)
            ->addFormat($highBitrate)
            ->save($online_stream_directory . '/online.m3u8');
        Log::debug('Start ... ');
        $online_stream_directory = 'public/storage/' . str_replace($basename, '', $input) . str_replace('.', '_', $basename);
        $m3u8s = glob(base_path($online_stream_directory) . '/*.m3u8');
        Log::debug($online_stream_directory);
        foreach ($m3u8s as $m3u8) {
            $file_contents = file_get_contents($m3u8);
            $file_contents = str_replace('online_', 'download?file=/public/hello/720P_ (2)_mp4/online_', $file_contents);
            file_put_contents($m3u8, $file_contents);
        }
    }
}
