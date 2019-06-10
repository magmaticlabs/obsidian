<?php

namespace Tests\Unit\Domain;

use Illuminate\Support\Facades\Storage;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;
use MagmaticLabs\Obsidian\Domain\ProcessExecutor\MockProcessExecutor;
use Tests\TestCase;

class BuildTest extends TestCase
{
    /**
     * Build instance
     *
     * @var Build
     */
    private $build;

    /**
     * Filesystem
     *
     * @var \Illuminate\Filesystem\FilesystemAdapter
     */
    private $storage;

    /**
     * Working directory path for the build
     *
     * @var string
     */
    private $working_dir;

    /**
     * Path to log file for the build
     *
     * @var string
     */
    private $logfile;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
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

        $this->working_dir = sprintf('builds/working/%s', $this->build->id);
        $this->logfile = sprintf('builds/logs/%s.log', $this->build->id);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        Storage::fake('local');

        parent::tearDown();
    }

    // --

    public function testPreflight()
    {
        $commithash = sha1('testing');

        $responses = [
            '/^git rev-parse/' => $commithash,
        ];

        $actions = [
            '#^' . app_path('Scripts/clone_repository.sh') . '#' => function () {
                $this->storage->makeDirectory($this->working_dir);
                $this->storage->makeDirectory($this->working_dir . '/.git');
            },
        ];

        $executor = new MockProcessExecutor($this->storage, $responses, $actions);

        $this->build->preflight($executor, $this->storage);
        $this->build->refresh();

        $this->assertEquals('ready', $this->build->status);
        $this->assertEquals($commithash, $this->build->commit);
        $this->assertNotNull($this->build->start_time);

        $this->storage->assertExists($this->working_dir);
        $this->storage->assertExists($this->working_dir . '/.git');

        $this->storage->assertExists($this->logfile);
    }

    public function testPreflightLogFile()
    {
        $commithash = sha1('testing');
        $scriptpath = app_path('Scripts/clone_repository.sh');

        $responses = [
            "#^${scriptpath}#" => 'Clone',
            '/^git rev-parse/' => $commithash,
        ];

        $executor = new MockProcessExecutor($this->storage, $responses, []);

        $this->build->preflight($executor, $this->storage);

        $this->storage->assertExists($this->logfile);
        $this->assertStringContainsString("Clone\n$commithash", $this->storage->get($this->logfile));
    }

    // --

    public function testBuildSuccessful()
    {
        $this->build->update(['status' => 'ready']);

        $this->storage->makeDirectory($this->working_dir);
        $this->storage->put($this->logfile, '__testing__');

        $executor = new MockProcessExecutor($this->storage, [], []);

        $result = $this->build->build($executor, $this->storage);

        $this->assertTrue($result);
    }

    public function testBuildFailure()
    {
        $this->build->update(['status' => 'ready']);

        $this->storage->makeDirectory($this->working_dir);
        $this->storage->put($this->logfile, '__testing__');

        $actions = [
            '#^' . app_path('Scripts/build_package.sh') . '#' => function () {
                throw new \RuntimeException('__testing__');
            },
        ];

        $executor = new MockProcessExecutor($this->storage, [], $actions);

        $result = $this->build->build($executor, $this->storage);

        $this->assertFalse($result);
    }

    public function testBuildMissingWorkingDir()
    {
        $this->build->update(['status' => 'ready']);

        $executor = new MockProcessExecutor($this->storage, [], []);

        $this->expectException(\Exception::class);
        $this->build->build($executor, $this->storage);
    }

    public function testBuildCleanup()
    {
        $this->build->update(['status' => 'ready']);

        $this->storage->makeDirectory($this->working_dir);
        $this->storage->put($this->logfile, '__testing__');

        $executor = new MockProcessExecutor($this->storage, [], []);

        $this->build->build($executor, $this->storage);

        $this->storage->assertMissing($this->working_dir);
    }

    //--

    public function testSuccess()
    {
        $this->build->update(['status' => 'running']);

        $this->build->success();
        $this->build->refresh();

        $this->assertEquals('success', $this->build->status);
        $this->assertNotNull($this->build->completion_time);
    }

    public function testFailure()
    {
        $this->build->update(['status' => 'running']);

        $this->build->failure();
        $this->build->refresh();

        $this->assertEquals('failure', $this->build->status);
        $this->assertNotNull($this->build->completion_time);
    }
}
