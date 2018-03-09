<?php

namespace App\Jobs;

use FFMpeg;
use Storage;
use App\FilePath;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(FilePath $filePath)
    {
        $this->filePath = $filePath;
        \Log::debug('Call Handle');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::debug('Call Handle');
        $lowBitrate = (new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))->setKiloBitrate(250);
        $midBitrate = (new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))->setKiloBitrate(500);
        $highBitrate = (new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'))->setKiloBitrate(1000);

        $input = str_replace(storage_path() . '/files/', '', $this->filePath->path);
        // $basename = basename($input);
        // $online_stream_directory = str_replace($basename, '', $input) . str_replace('.', '_', $basename);
        FFMpeg::fromDisk('movie')
            ->open($input)
            ->exportForHLS()
            ->setSegmentLength(10)
            ->addFormat($lowBitrate)
            ->addFormat($midBitrate)
            ->addFormat($highBitrate)
            ->save('online.m3u8');
    }
}
