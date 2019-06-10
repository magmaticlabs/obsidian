<?php

namespace MagmaticLabs\Obsidian\Domain\Eloquent;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MagmaticLabs\Obsidian\Domain\ProcessExecutor\ProcessExecutor;

final class Build extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'start_time'      => 'datetime',
        'completion_time' => 'datetime',
    ];

    /**
     * Package relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    /**
     * Operations to perform before the build itself
     *
     * @param ProcessExecutor $executor
     */
    public function preflight(ProcessExecutor $executor, Filesystem $storage)
    {
        if ('pending' != $this->status) {
            throw new \LogicException('Attempting to run preflight after launch');
        }

        $executor->setLogFile(sprintf('builds/logs/%s.log', $this->id));

        $working_dir = sprintf('builds/working/%s', $this->id);

        // Print log header
        $executor->log(sprintf('[%s] Build ID: %s', (string) Carbon::now(), $this->id));
        $executor->log(str_repeat('-', 64));
        $executor->log(sprintf('%s - %s', $this->package->source, $this->ref));
        $executor->log(str_repeat('-', 64));
        $executor->exec(app_path('Scripts/software_versions.sh'));
        $executor->log(str_repeat('-', 64));

        // Grab the source repository and clone it to local disk
        $executor->exec(vsprintf('%s "%s" "%s"', [
            app_path('Scripts/clone_repository.sh'),
            $this->package->source,
            $storage->path($working_dir),
        ]));

        // Determine the commit ID of the requested reference
        $commit = $executor->exec(sprintf('git rev-parse %s', $this->ref), $storage->path($working_dir));

        // Update the build model with the preflight data
        $this->update([
            'status'     => 'ready',
            'commit'     => $commit,
            'start_time' => Carbon::now(),
        ]);
    }

    /**
     * Process the build
     *
     * @param ProcessExecutor $executor
     * @param Filesystem      $storage
     *
     * @return bool
     */
    public function build(ProcessExecutor $executor, Filesystem $storage): bool
    {
        if ('ready' != $this->status) {
            throw new \LogicException('Attempting to build out of order');
        }

        $executor->setLogFile($logfile = sprintf('builds/logs/%s.log', $this->id));

        $working_dir = sprintf('builds/working/%s', $this->id);
        if (!$storage->exists($working_dir)) {
            throw new \RuntimeException('Missing build working directory');
        }

        $success = true;

        try {
            $executor->exec(vsprintf('%s "%s"', [
                app_path('Scripts/build_package.sh'),
                $storage->path($working_dir),
            ]));
        } catch (\Exception $ex) {
            $success = false;
        }

        // Copy the log file over
        $storage->move($logfile, sprintf('builds/%s/buildlog.log', $this->id));

        // Cleanup
        $storage->deleteDirectory($working_dir);

        return $success;
    }

    /**
     * Update the model with our success
     */
    public function success()
    {
        if ('running' != $this->status) {
            throw new \LogicException('Attempting to build out of order');
        }

        $this->update([
            'status'          => 'success',
            'completion_time' => Carbon::now(),
        ]);
    }

    /**
     * Update the build model with our failure
     */
    public function failure()
    {
        if ('running' != $this->status) {
            throw new \LogicException('Attempting to build out of order');
        }

        $this->update([
            'status'          => 'failure',
            'completion_time' => Carbon::now(),
        ]);
    }
}
