<?php

namespace MagmaticLabs\Obsidian\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;

final class PKGController extends Controller
{
    /**
     * The root endpoint returns a manifest of all public repositories in the
     * entire system. This is a shortcut for "I want everything".
     */
    public function root()
    {
        $repositories = Repository::query()->get();

        return response(json_encode($this->constructManifest($repositories)), 200);
    }

    /**
     * Return a manifest for all public repositories in an organization.
     */
    public function organization(string $id)
    {
        /** @var Organization $organization */
        $organization = Organization::query()->where('name', $id)->firstOrFail();

        $repositories = $organization->repositories()->get();

        return response(json_encode($this->constructManifest($repositories)), 200);
    }

    /**
     * Return a manifest for a single repository.
     */
    public function repository(string $org, string $id)
    {
        /** @var Organization $organization */
        $organization = Organization::query()->where('name', $org)->firstOrFail();

        $repositories = $organization->repositories()->where('name', $id)->get();
        if (0 === \count($repositories)) {
            abort(404);
        }

        return response(json_encode($this->constructManifest($repositories)), 200);
    }

    public function package(string $org, string $repo, string $id)
    {
        /** @var Organization $organization */
        $organization = Organization::query()->where('name', $org)->firstOrFail();

        /** @var Repository $repository */
        $repository = $organization->repositories()->where('name', $repo)->firstOrFail();

        /** @var Package $package */
        $package = $repository->packages()->where('name', $id)->firstOrFail();

        return response(json_encode($this->buildPackage($package)), 200);
    }

    public function download(string $org, string $repo, string $id, string $version)
    {
        /** @var Organization $organization */
        $organization = Organization::query()->where('name', $org)->firstOrFail();

        /** @var Repository $repository */
        $repository = $organization->repositories()->where('name', $repo)->firstOrFail();

        /** @var Package $package */
        $package = $repository->packages()->where('name', $id)->firstOrFail();

        $version = preg_replace('/\.zip$/', '', $version);

        $build = $package->builds()->where('version', $version)->orderBy('completion_time', 'DESC')->firstOrFail();

        return response(file_get_contents(storage_path(sprintf('app/builds/archive/%s/%s.zip', $build->id, $id))), 200)->withHeaders([
            'content-type' => 'application/zip',
        ]);
    }

    private function constructManifest(Collection $repositories)
    {
        $repos = [];

        /** @var Repository[] $repositories */
        foreach ($repositories as $repository) {
            $packages = [];
            /** @var \MagmaticLabs\Obsidian\Domain\Eloquent\Package $package */
            foreach ($repository->packages()->orderBy('name', 'ASC')->get() as $package) {
                $packages[$package->name] = $this->buildPackage($package);
            }

            $repos[] = [
                'organization' => $repository->organization->name,
                'name'         => $repository->name,
                'display_name' => $repository->display_name,
                'description'  => $repository->description,
                'packages'     => $packages,
            ];
        }

        return [
            'root'         => route('pkg.root'),
            'version'      => '1.0.0',
            'repositories' => $repos,
        ];
    }

    private function buildPackage(Package $package)
    {
        /** @var Build $build */
        $build = $package->builds()->where('status', 'success')->orderBy('completion_time', 'DESC')->first();
        $metadata = $this->parseBuildMeta($build);

        return array_merge($metadata, [
            'downloads'       => 0,
            'active_installs' => 0,
            'added'           => date('Y-m-d H:i:s', strtotime($package->created)),
            'last_updated'    => date('Y-m-d H:i:s', strtotime($build->completion_time)),
            'download_link'   => route('pkg.download', [
                $package->repository->organization->name,
                $package->repository->name,
                $package->name,
                $build->version,
            ]),
            'slug' => $package->name,
        ]);
    }

    private function parseBuildMeta(Build $build)
    {
        $data = json_decode(file_get_contents(storage_path(sprintf('app/builds/archive/%s/header.json', $build->id))), true);

        foreach ($data as $key => $value) {
            unset($data[$key]);
            $data[strtolower($key)] = $value;
        }

        $data['author_uri'] = $data['authoruri'];
        $data['short_description'] = $data['description'];
        unset(
            $data['authoruri'],
            $data['description'],
            $data['packageuri'],
            $data['textdomain'],
            $data['domainpath'],
            $data['network']
        );

        return $data;
    }
}
