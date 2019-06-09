<?php

namespace MagmaticLabs\Obsidian\Http\Controllers\API;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Schema;
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

        if ($includes = $request->query('include')) {
            $this->fractal->parseIncludes($includes);
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

        $this->applysort($request, $query);

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

    /**
     * Apply sorting to the query
     *
     * @param Request          $request
     * @param Builder|Relation $query
     */
    private function applysort(Request $request, $query)
    {
        $model = $query->getModel();
        $table = $model->getTable();

        $sorting = $request->input('sort', null);

        // Apply requested sorting
        if (!empty($sorting)) {
            if (!is_string($sorting)) {
                abort(400, 'Invalid sort format, requires: sort=attr,-attr');
            }

            if (preg_match('/\./', $sorting)) {
                abort(400, 'Sorting is currently only supported on top level resources');
            }

            $columns = explode(',', $sorting);
            foreach ($columns as $column) {
                $column = trim($column);

                if (empty($column)) {
                    continue;
                }

                if (preg_match('/^\-/', $column)) {
                    $direction = 'DESC';
                    $column = substr($column, 1);
                } else {
                    $direction = 'ASC';
                }

                if (!Schema::hasColumn($table, $column)) {
                    abort(400, sprintf('Tried to sort %s on unknown attribute: %s', $table, $column));
                }

                $query->orderBy($model->qualifyColumn($column), $direction);
            }
        }

        // Always sort by the primary key last
        $query->orderBy($model->getQualifiedKeyName(), 'asc');
    }
}
