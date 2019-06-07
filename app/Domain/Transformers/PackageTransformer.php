<?php

namespace MagmaticLabs\Obsidian\Domain\Transformers;

use League\Fractal\Resource\Collection;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;

final class PackageTransformer extends Transformer
{
    protected $availableIncludes = [
        'repository',
    ];

    public function includeRepository(Package $package)
    {
        return new Collection($package->repository(), new RepositoryTransformer(), 'repositories');
    }
}
