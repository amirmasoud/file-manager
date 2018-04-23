<?php
namespace App\Listeners;

use Log;
use FFMpeg;
use App\FilePath;
use App\Jobs\ProcessVideo;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;
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

        $bit240 = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(400);
        // $bit360 = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(750);
        // $bit480 = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(1000);
        // $bit720 = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(2500);
        // $bit1080 = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(4500);

        $bitrates = [
            '400000',
            '750000',
            '1000000',
            '2500000',
            '4500000',
        ];

        $dimensions = [
            [426, 240],
            [640, 360],
            [854, 480],
            [1280, 720],
            [1920, 1080],
        ];

        $input = str_replace(storage_path() . '/app/', '', $filePath->path);

        Log::debug('ffprobe -v error -select_streams v:0 -show_entries stream=bit_rate -of default=noprint_wrappers=1:nokey=1 "' . storage_path() . '/app/' . $input . '"');
        $bitrate = (int) exec('ffprobe -v error -select_streams v:0 -show_entries stream=bit_rate -of default=noprint_wrappers=1:nokey=1 "' . storage_path() . '/app/' . $input . '"');
        Log::debug('here');

        // $basename = basename($input);
        $dir = crc32(\Carbon\Carbon::now());
        $online_stream_directory = 'public/' . $dir;
        Log::debug('here 2');

        $ffmpeg = FFMpeg::fromDisk('local')
                        ->open($input)
                        ->exportForHLS()
                        ->setSegmentLength(10);

        $ffmpeg->addFormat($bit240, function($media) {
            $media->addFilter(function ($filters) {
                $filters->resize(new Dimension(426, 240));
            });
        });

        Log::debug('here 3');

        $i = 0;
        while ($bitrate >= $bitrates[$i]) {
            Log::debug('here 3.1');
            $br = (new X264('libmp3lame', 'libx264'))->setKiloBitrate(($bitrates[$i]/1000));
            Log::debug('here 3.2');

            $ffmpeg->addFormat($br, function($media) use ($dimensions, $i) {
                $media->addFilter(function ($filters) use ($dimensions, $i) {
                    $filters->resize(new Dimension($dimensions[$i][0], $dimensions[$i][1]));
                });
            });
            $i++;
            Log::debug('here 3.3');
        }

        Log::debug('here 4');

        $ffmpeg->save($online_stream_directory . '/online.m3u8');

        $online_stream_directory = 'storage/app/public/' . $dir;
        $m3u8s = glob(base_path(trim($online_stream_directory)) . '/*.m3u8');

        foreach ($m3u8s as $m3u8) {
            $file_contents = file_get_contents($m3u8);
            $file_contents = str_replace('online_', 'download?file=/public/' . $dir . '/online_', $file_contents);
            file_put_contents($m3u8, $file_contents);
        }
    }
}
