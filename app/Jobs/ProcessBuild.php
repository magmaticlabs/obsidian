<?php

namespace MagmaticLabs\Obsidian\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;

class ProcessBuild implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var Build */
    private $build;

    public function __construct(Build $build)
    {
        $this->build = $build;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $exitcode = Artisan::call('obsidian:build', [
            'id'        => $this->build->id,
            '--verbose' => true,
        ]);
    }
}
