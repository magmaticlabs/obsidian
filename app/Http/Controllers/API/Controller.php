<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use MagmaticLabs\Obsidian\Domain\Eloquent\Model;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use MagmaticLabs\Obsidian\Domain\Support\JsonApiSerializer;
use MagmaticLabs\Obsidian\Domain\Support\Paginator;
use MagmaticLabs\Obsidian\Domain\Transformers\Transformer;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * @var \League\Fractal\Manager
     */
    protected $fractal;

    /**
     * Class constructor
     */
    public function __construct(Request $request)
    {
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new JsonApiSerializer(url()->to('/api/')));

        if ($fields = $request->query('fields')) {
            if (!is_array($fields)) {
                abort(400, 'Invalid sparse fields format, requires: fields[type]=attr,attr');
            }

            $this->fractal->parseFieldsets($fields);
        }
    }

    /**
     * Get the active user
     *
     * @return \MagmaticLabs\Obsidian\Domain\Eloquent\User|null
     */
    protected function getUser(): ?User
    {
        return auth()->user();
    }

    /**
     * Construct a resource collection
     *
     * @param Request          $request
     * @param Builder|Relation $query
     * @param Transformer      $transformer
     *
     * @return array
     */
    protected function collection(Request $request, $query, Transformer $transformer): array
    {
        if (!($query instanceof Builder || $query instanceof Relation)) {
            throw new \InvalidArgumentException();
        }

        if (empty($this->fractal->getRequestedIncludes())) {
            $transformer->setDefaultIncludes($transformer->getAvailableIncludes());
        }

        $paginator = new Paginator($request, $query);

        $model = $query->getModel();
        $resourceKey = ($model instanceof Model) ? $model->getResourceKey() : $model->getTable();

        $resource = new Collection($paginator->getData(), $transformer, $resourceKey);
        $resource->setPaginator($paginator);

        return $this->fractal->createData($resource)->toArray();
    }

    /**
     * Construct a collection item
     *
     * @param Model       $model
     * @param Transformer $transformer
     *
     * @return array
     */
    protected function item(Model $model, Transformer $transformer): array
    {
        if (empty($this->fractal->getRequestedIncludes())) {
            $transformer->setDefaultIncludes($transformer->getAvailableIncludes());
        }

        $resource = new Item($model, $transformer, $model->getResourceKey());

        return $this->fractal->createData($resource)->toArray();
    }
}
