<?php

namespace MagmaticLabs\Obsidian\Domain\Transformers;

use League\Fractal\TransformerAbstract;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

class Transformer extends TransformerAbstract
{
    /**
     * {@inheritdoc}
     */
    public function transform(Model $model)
    {
        return $model->toArray();
    }
}
