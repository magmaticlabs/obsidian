<?php

namespace MagmaticLabs\Obsidian\Domain\Transformers;

use League\Fractal\TransformerAbstract;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

class Transformer extends TransformerAbstract
{
    /**
     * Do not automatically include the listed relationships
     *
     * @var array
     */
    protected $noAutoInclude = [];

    /**
     * Setter for defaultIncludes.
     *
     * @param array $defaultIncludes
     *
     * @return TransformerAbstract
     */
    public function setDefaultIncludes($defaultIncludes)
    {
        $defaultIncludes = array_diff($defaultIncludes, $this->noAutoInclude);

        return parent::setDefaultIncludes($defaultIncludes);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Model $model)
    {
        return $model->toArray();
    }
}
