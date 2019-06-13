<?php

namespace Tests\Unit\Domain;

use Illuminate\Support\Facades\Storage;
use MagmaticLabs\Obsidian\Domain\BuildProcessing\BuildProcessor;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\ProcessExecutor\MockProcessExecutor;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\Output;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class BuildTest extends TestCase
{
    /**
     * Build instance.
     *
     * @var Build
     */
    private $build;

    /**
     * Filesystem.
     *
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    private $storage;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $organization = $this->factory(Organization::class)->create();

        $repository = $this->factory(Repository::class)->create([
            'organization_id' => $organization->id,
        ]);

        $package = $this->factory(Package::class)->create([
            'repository_id' => $repository->id,
            'source'        => 'git@github.com:testing/testing.git',
        ]);

        $this->build = $this->factory(Build::class)->create([
            'package_id'      => $package->id,
            'ref'             => 'master',
            'commit'          => null,
            'status'          => 'pending',
            'start_time'      => null,
            'completion_time' => null,
        ]);

        $this->storage = Storage::fake('local');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        Storage::fake('local');

        parent::tearDown();
    }

    // --

    public function testWorkingDirValue()
    {
        $workingdir = sprintf('builds/working/%s', $this->build->id);

        $processor = new BuildProcessor(
            new MockProcessExecutor([], []),
            $this->storage,
            new NullOutput()
        );

        static::assertSame($workingdir, $processor->getWorkingDir($this->build));
    }

    public function testPreflight()
    {
        $commithash = sha1('testing');
        $commandExecuted = false;

        $responses = [
            '/^git/' => $commithash,
        ];

        $pattern = sprintf('#^%s#', app_path('Scripts/clone_repository.sh'));

        $actions = [
            $pattern => function () use (&$commandExecuted) {
                $commandExecuted = true;
            },
        ];

        $executor = new MockProcessExecutor($responses, $actions);
        $output = new TestOutput();

        // --

        $processor = new BuildProcessor(
            $executor,
            $this->storage,
            $output
        );

        $processor->preflight($this->build);
        $this->build->refresh();

        static::assertSame('ready', $this->build->status);
        static::assertSame($commithash, $this->build->commit);

        static::assertTrue($commandExecuted);

        static::assertNotEmpty($output->output);
    }

    public function testBuild()
    {
        $executor = new MockProcessExecutor([], []);

        $processor = new BuildProcessor(
            $executor,
            $this->storage,
            new NullOutput()
        );

        $this->build->status = 'ready';
        $this->storage->makeDirectory($workingdir = $processor->getWorkingDir($this->build));

        $processor->process($this->build);

        $command = app_path('Scripts/build_package.sh');
        static::assertSame([$command], $executor->getCommands());
    }

    public function testBuildFailure()
    {
        $pattern = sprintf('#^%s#', app_path('Scripts/build_package.sh'));

        $executor = new MockProcessExecutor([], [
            $pattern => function () {
                throw new \RuntimeException();
            },
        ]);

        $processor = new BuildProcessor(
            $executor,
            $this->storage,
            new NullOutput()
        );

        $this->build->status = 'ready';
        $this->storage->makeDirectory($workingdir = $processor->getWorkingDir($this->build));

        $this->expectException(\RuntimeException::class);
        $processor->process($this->build);
    }

    public function testBuildMissingWorkingDir()
    {
        $executor = new MockProcessExecutor([], []);

        $processor = new BuildProcessor(
            $executor,
            $this->storage,
            new NullOutput()
        );

        $this->build->status = 'ready';

        $this->expectException(\RuntimeException::class);
        $processor->process($this->build);
    }

    public function testSuccess()
    {
        $executor = new MockProcessExecutor([], []);

        $processor = new BuildProcessor(
            $executor,
            $this->storage,
            new NullOutput()
        );

        $processor->success($this->build);
        $this->build->refresh();

        static::assertSame('success', $this->build->status);
        static::assertNotNull($this->build->start_time);
        static::assertNotNull($this->build->completion_time);
    }

    public function testFailure()
    {
        $executor = new MockProcessExecutor([], []);

        $processor = new BuildProcessor(
            $executor,
            $this->storage,
            new NullOutput()
        );

        $processor->failure($this->build);
        $this->build->refresh();

        static::assertSame('failure', $this->build->status);
        static::assertNotNull($this->build->start_time);
        static::assertNotNull($this->build->completion_time);
    }

    public function testCleanup()
    {
        $executor = new MockProcessExecutor([], []);

        $processor = new BuildProcessor(
            $executor,
            $this->storage,
            new NullOutput()
        );

        $this->storage->makeDirectory($workingdir = $processor->getWorkingDir($this->build));

        $processor->cleanup($this->build);

        $this->storage->assertMissing($workingdir);
    }
}

class TestOutput extends Output
{
    public $output = '';

    public function clear()
    {
        $this->output = '';
    }

    protected function doWrite($message, $newline)
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
