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
        // \Log::debug('Call Handle');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
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
        \Log::debug($basename);
        \Log::debug($input);
        $online_stream_directory = 'public/' . str_replace($basename, '', $input) . str_replace('.', '_', $basename);
        \FFMpeg::fromDisk('local')
            ->open($input)
            ->exportForHLS()
            ->setSegmentLength(10)
            ->addFormat($lowBitrate)
            ->addFormat($midBitrate)
            ->setPlaylistPath('hello')
            // ->addFormat($highBitrate)
            ->save($online_stream_directory . '/online.m3u8');
    }
}
