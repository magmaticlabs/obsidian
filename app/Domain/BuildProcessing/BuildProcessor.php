<?php

namespace MagmaticLabs\Obsidian\Domain\BuildProcessing;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use LogicException;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\ProcessExecutor\ProcessExecutor;
use RuntimeException;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class BuildProcessor
{
    /**
     * Process Executor.
     *
     * @var ProcessExecutor
     */
    private $executor;

    /**
     * Filesystem backend.
     *
     * @var Filesystem
     */
    private $storage;

    /**
     * Output.
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * Class constructor.
     *
     * @param ProcessExecutor $executor
     * @param Filesystem      $storage
     * @param OutputInterface $output
     */
    public function __construct(ProcessExecutor $executor, Filesystem $storage, OutputInterface $output)
    {
        $this->executor = $executor;
        $this->storage = $storage;
        $this->output = $output;
    }

    /**
     * Get the path to the working directory for the build.
     *
     * @param Build $build
     *
     * @return string
     */
    public function getWorkingDir(Build $build): string
    {
        return sprintf('builds/working/%s', $build->id);
    }

    /**
     * Get the path to the staging directory for the build.
     *
     * @param Build $build
     *
     * @return string
     */
    public function getStagingDir(Build $build): string
    {
        return sprintf('builds/staging/%s', $build->id);
    }

    /**
     * Get the path to the archive directory for the build.
     *
     * @param Build $build
     *
     * @return string
     */
    public function getArchiveDir(Build $build): string
    {
        return sprintf('builds/archive/%s', $build->id);
    }

    /**
     * Operations to perform before the build itself.
     *
     * In particular, we will be sanity checking the working directory, cloning
     * down the repository from the source, recording the commit hash of the
     * target reference, and marking the build as ready for actual building.
     *
     * @param Build $build
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    public function preflight(Build $build): void
    {
        if ('pending' !== $build->status) {
            throw new LogicException('Attempting to build out of order');
        }

        // What we actually get back is a FilesystemAdapter, but type hinting
        // as that can break things, so we soft convert, and call the path()
        // method later on.
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = $this->storage;

        // Clean up the working directory if it already exists
        if ($storage->exists($workingdir = $this->getWorkingDir($build))) {
            $storage->deleteDirectory($workingdir);
        }

        // Clean up and recreate the staging directory
        if ($storage->exists($stagingdir = $this->getStagingDir($build))) {
            $storage->deleteDirectory($stagingdir);
        }
        $storage->makeDirectory($stagingdir);

        // Clean up and recreate the archive directory
        if ($storage->exists($archivedir = $this->getArchiveDir($build))) {
            $storage->deleteDirectory($archivedir);
        }
        $storage->makeDirectory($archivedir);

        // Print log header
        $this->output->writeln(sprintf('[%s] Build ID: %s', (string) Carbon::now(), $build->id));

        $buildref = $build->ref;

        // Grab the source repository and clone it to local disk
        // This throws a RuntimeException if anything goes wrong
        $this->executor->exec($this->scriptPath('clone_repository.sh'), [
            $build->package->source,
            $buildref,
            $storage->path($workingdir),
        ], $this->output);

        // Determine the commit ID of the requested reference
        $commit = trim($this->executor->exec('git', [
            'rev-parse',
            $buildref,
        ], new NullOutput(), $storage->path($workingdir)));

        $this->output->writeln(sprintf('Located reference: %s', $commit));

        // Update the build model with the preflight data
        $build->update([
            'status' => 'ready',
            'commit' => $commit,
        ]);
    }

    /**
     * Process the build.
     *
     * @param Build $build
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    public function process(Build $build): void
    {
        if ('ready' !== $build->status) {
            throw new LogicException('Attempting to build out of order');
        }

        if (!$this->storage->exists($workingdir = $this->getWorkingDir($build))) {
            throw new RuntimeException('Missing build working directory');
        }

        if (!$this->storage->exists($stagingdir = $this->getStagingDir($build))) {
            throw new RuntimeException('Missing build staging directory');
        }

        if (!$this->storage->exists($archivedir = $this->getArchiveDir($build))) {
            throw new RuntimeException('Missing build archive directory');
        }

        // Update the status
        $build->update([
            'status'     => 'running',
            'start_time' => Carbon::now(),
        ]);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        // What we actually get back is a FilesystemAdapter, but type hinting
        // as that can break things, so we soft convert, and call the path()
        // method later on.
        $storage = $this->storage;

        // This throws a RuntimeException if anything goes wrong
        $this->executor->exec($this->scriptPath('build_package.sh'), [
            $storage->path($workingdir),
            $storage->path($stagingdir),
            $storage->path($archivedir),
            $build->package->name,
        ], $this->output);
    }

    /**
     * Clean up after the build.
     *
     * @param Build $build
     */
    public function cleanup(Build $build): void
    {
        if ($this->storage->exists($workingdir = $this->getWorkingDir($build))) {
            $this->storage->deleteDirectory($workingdir);
        }

        if ($this->storage->exists($stagingdir = $this->getStagingDir($build))) {
            $this->storage->deleteDirectory($stagingdir);
        }
    }

    /**
     * Mark the build as having completed successfully.
     *
     * @param Build $build
     */
    public function success(Build $build): void
    {
        $this->complete($build, 'success');
    }

    /**
     * Mark the build as having completed in failure.
     *
     * @param Build $build
     */
    public function failure(Build $build): void
    {
        $this->complete($build, 'failure');
    }

    /**
     * Get the path to a script.
     *
     * @param string $script
     *
     * @return string
     */
    private function scriptPath(string $script): string
    {
        return app_path(sprintf('Scripts/%s', $script));
    }

    /**
     * Mark the build as having completed with the given status.
     *
     * @param Build  $build
     * @param string $status
     */
    private function complete(Build $build, string $status): void
    {
        // Ensure we always have a start time
        $start = $build->start_time;
        if (empty($start)) {
            $start = Carbon::now();
        }

        $build->update([
            'status'          => $status,
            'start_time'      => $start,
            'completion_time' => Carbon::now(),
        ]);
    }
}
