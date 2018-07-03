<?php

namespace App\Jobs;

use Log;
use FFMpeg;
use Storage;
use App\FilePath;
use Illuminate\Bus\Queueable;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get the public path to the file and save it to the database
        $publicFilePath = str_replace(public_path(), "", $this->path);
        $filePath = FilePath::create([
            'path' => $publicFilePath,
        ]);

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

        $bitrate = (int) exec('ffprobe -v error -select_streams v:0 -show_entries stream=bit_rate -of default=noprint_wrappers=1:nokey=1 "' . storage_path() . '/app/' . $input . '"');
        exec('ffprobe -v quiet -print_format json -show_format -show_streams "' . storage_path() . '/app/' . $input . '"', $stream);
        $stream = json_decode(implode('', $stream));
        $width = $stream->streams[0]->width;
        $height = $stream->streams[0]->height;
        \Log::debug($height);
        // $aspectRatio = explode(':', $stream->streams[0]->display_aspect_ratio);
        // $aspectRatioX = $aspectRatio[0];
        // $aspectRatioY = $aspectRatio[1];
        $aspectRatio = $width / $height;

        // $basename = basename($input);
        $dir = crc32(\Carbon\Carbon::now());
        $online_stream_directory = pathinfo(trim($input))['dirname'] . '/' . $dir;

        $ffmpeg = FFMpeg::fromDisk('local')
                        ->open($input)
                        ->exportForHLS()
                        ->setSegmentLength(10);

        $bit240 = (new X264('aac', 'libx264'))->setKiloBitrate(($bitrate <= 400000) ? $bitrate/1000 : 400);
        $ffmpeg->addFormat($bit240, function($media) {
            $media->addFilter(function ($filters) {
                $filters->resize(new Dimension(426, 240));
            });
        });


        $i = 0;
        while ($height >= $dimensions[$i][1]) {
            $newBitrate = ($bitrate <= $bitrates[$i] ? $bitrate/1000 : $bitrates[$i]/1000);
            $br = (new X264('aac', 'libx264'))->setKiloBitrate(($newBitrate));

            // $calcWidth = ($dimensions[$i][1] * $aspectRatioX) / $aspectRatioY;
            $calcWidth = $dimensions[$i][1] * $aspectRatio;
            $calcWidth = ($calcWidth % 2) ? $calcWidth + 1 : $calcWidth; // FFMPEG: width divisible by 2
            $ffmpeg->addFormat($br, function($media) use ($dimensions, $i, $calcWidth) {
                $media->addFilter(function ($filters) use ($dimensions, $i, $calcWidth) {
                    $filters->resize(new Dimension((int) $calcWidth, $dimensions[$i][1]));
                });
            });
            $i++;
        }


        $ffmpeg->save($online_stream_directory . '/online.m3u8');

        $real_path = $this->encode($online_stream_directory);
        $online_stream_directory = 'storage/app/' . $online_stream_directory;
        $m3u8s = glob(base_path(trim($online_stream_directory)) . '/*.m3u8');

        foreach ($m3u8s as $m3u8) {
            $file_contents = file_get_contents($m3u8);
            $file_contents = str_replace('online_', 'download?file=/' . $real_path . '/online_', $file_contents);
            file_put_contents($m3u8, $file_contents);
        }
    }

    private function encode($text)
    {
        $parts = explode('/', $text);
        array_walk($parts, function (&$part, $key) {
            $part = urlencode($part);
        });
        return implode('/', $parts);
    }
}
