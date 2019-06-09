<?php

namespace MagmaticLabs\Obsidian\Domain\Transformers;

use League\Fractal\Resource\Item;
use MagmaticLabs\Obsidian\Domain\Eloquent\Package;

final class PackageTransformer extends Transformer
{
    protected $availableIncludes = [
        'repository',
    ];

    public function includeRepository(Package $package)
    {
        return new Item($package->repository, new RepositoryTransformer(), 'repositories');
    }
}
