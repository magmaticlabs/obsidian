<?php

namespace MagmaticLabs\Obsidian\Domain\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as BaseModel;
use MagmaticLabs\Obsidian\Domain\Support\UUID;

/**
 * @method static array      all($columns = ['*'])
 * @method static int        count()
 * @method static Model      create(array $attributes = [])
 * @method static Model      find($id, $columns = ['*'])
 * @method static Model      findOrFail($id, $columns = ['*'])
 * @method static Model      findOrNew($id, $columns = ['*'])
 * @method static Collection findMany($ids, $columns = ['*'])
 * @method static Builder    query()
 */
abstract class Model extends BaseModel
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'modified';

    /**
     * The name of the "deleted at" column.
     *
     * @var string
     */
    const DELETED_AT = 'deleted';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * Since we are using non-integer keys, disable this.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * By default we want most of our models to be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * We will be using UUID strings by default.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that aren't mass assignable.
     *
     * By default all attributes are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * By default the 'deleted' and pivot attributes are hidden.
     *
     * @var array
     */
    protected $hidden = [
        self::DELETED_AT,
        'pivot',
    ];

    /**
     * Indicates if a UUID be generated for the model when it is created.
     *
     * @var bool
     */
    protected $generateKey = true;

    /**
     * API resource key.
     *
     * @var string
     */
    protected $resource_key = '';

    /**
     * Resource key accessor.
     *
     * @return string
     */
    public function getResourceKey()
    {
        if (empty($this->resource_key)) {
            return $this->getTable();
        }

        return $this->resource_key;
    }

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if ($model->generateKey && empty($model->attributes[$model->getKeyName()])) {
                $model->{$model->getKeyName()} = UUID::generate()->toString();
            }
        });
    }
}
