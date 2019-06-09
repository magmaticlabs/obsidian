<?php

namespace MagmaticLabs\Obsidian\Domain\Transformers;

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use MagmaticLabs\Obsidian\Domain\Eloquent\Repository;

final class RepositoryTransformer extends Transformer
{
    protected $availableIncludes = [
        'organization',
        'packages',
    ];

    public function includeOrganization(Repository $repository)
    {
        return new Item($repository->organization, new OrganizationTransformer(), 'organizations');
    }

    public function includePackages(Repository $repository)
    {
        return new Collection($repository->packages, new PackageTransformer(), 'packages');
    }
}
