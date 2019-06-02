<?php

namespace MagmaticLabs\Obsidian\Domain\Transformers;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

class RelationshipTransformer extends Transformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(Model $model)
    {
        return [
            'id'               => $model->getKey(),
            '__relationship__' => true,
        ];
    }
}
