<?php

namespace MagmaticLabs\Obsidian\Domain\Transformers;

use League\Fractal\Resource\Item;
use MagmaticLabs\Obsidian\Domain\Eloquent\Build;

final class BuildTransformer extends Transformer
{
    protected $availableIncludes = [
        'package',
    ];

    public function includePackage(Build $build)
    {
        return new Item($build->package, new PackageTransformer(), 'packages');
    }
}
