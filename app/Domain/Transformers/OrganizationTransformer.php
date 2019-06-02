<?php

namespace MagmaticLabs\Obsidian\Domain\Transformers;

use League\Fractal\Resource\Collection;
use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;

final class OrganizationTransformer extends Transformer
{
    protected $availableIncludes = [
        'members',
        'owners',
    ];

    public function includeMembers(Organization $organization)
    {
        return new Collection($organization->members, new Transformer(), 'users');
    }

    public function includeOwners(Organization $organization)
    {
        return new Collection($organization->owners, new Transformer(), 'users');
    }
}
