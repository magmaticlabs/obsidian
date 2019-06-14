<?php

namespace Tests\Feature\API\APIResource;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

abstract class RelationshipTestCase extends ResourceTestCase
{
    const SINGULAR = 1;
    const PLURAL   = 2;

    /**
     * The name of the relationship.
     *
     * @var string
     */
    protected $relationship = '__INVALID__';

    /**
     * Resource type of the related resource.
     *
     * @var int
     */
    protected $relationship_type = '__INVALID__';

    /**
     * Singular vs plural relationship.
     *
     * @var int
     */
    protected $relationship_plurality = self::PLURAL;

    //--

    public function testMissingParentModel()
    {
        $response = $this->get($this->route($this->relationship, '__INVAILD__'));
        $this->validateResponse($response, 404);

        $response = $this->get($this->route(sprintf('%s.index', $this->relationship), '__INVAILD__'));
        $this->validateResponse($response, 404);

        $response = $this->post($this->route(sprintf('%s.create', $this->relationship), '__INVAILD__'));
        $this->validateResponse($response, 404);

        $response = $this->patch($this->route(sprintf('%s.update', $this->relationship), '__INVAILD__'));
        $this->validateResponse($response, 404);

        $response = $this->delete($this->route(sprintf('%s.destroy', $this->relationship), '__INVAILD__'));
        $this->validateResponse($response, 404);
    }

    public function testDataMatchesShow()
    {
        $model = $this->createModel(1);
        $relation = $this->createRelationshipModel($model);

        $response = $this->get($this->route($this->relationship, $model->id));
        $this->validateResponse($response, 200);

        $compare = $this->get(route(sprintf('api.%s.show', $this->relationship_type), $relation->id));
        $compare = json_decode($compare->getContent(), true);

        if (self::SINGULAR === $this->relationship_plurality) {
            $data = $compare['data'];
        } else {
            $data = [
                $compare['data'],
            ];
        }

        $response->assertJson([
            'data' => $data,
        ]);
    }

    public function testCountsMatches()
    {
        if (self::SINGULAR === $this->relationship_plurality) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $model = $this->createModel(1);

        $response = $this->get($this->route($this->relationship, $model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame(0, \count($data['data']));

        // --

        $count = 5;

        $this->createRelationshipModel($model, $count);

        $response = $this->get($this->route($this->relationship, $model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame($count, \count($data['data']));
    }

    public function testRelationshipDataCorrect()
    {
        $model = $this->createModel(1);
        $relation = $this->createRelationshipModel($model);

        $response = $this->get($this->route(sprintf('%s.index', $this->relationship), $model->id));
        $this->validateResponse($response, 200);

        if (self::SINGULAR === $this->relationship_plurality) {
            $data = [
                'type' => $this->relationship_type,
                'id'   => $relation->id,
            ];
        } else {
            $data = [
                [
                    'type' => $this->relationship_type,
                    'id'   => $relation->id,
                ],
            ];
        }

        $response->assertJson([
            'data' => $data,
        ]);
    }

    public function testRelationshipCountsMatches()
    {
        if (self::SINGULAR === $this->relationship_plurality) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $model = $this->createModel(1);

        $response = $this->get($this->route(sprintf('%s.index', $this->relationship), $model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame(0, \count($data['data']));

        // --

        $count = 5;

        $this->createRelationshipModel($model, $count);

        $response = $this->get($this->route(sprintf('%s.index', $this->relationship), $model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame($count, \count($data['data']));
    }

    // --

    public function testCreate()
    {
        $model = $this->createModel(1);

        $response = $this->post($this->route(sprintf('%s.create', $this->relationship), $model->id));
        $this->validateResponse($response, 403);
    }

    public function testUpdate()
    {
        $model = $this->createModel(1);

        $response = $this->patch($this->route(sprintf('%s.update', $this->relationship), $model->id));
        $this->validateResponse($response, 403);
    }

    public function testDelete()
    {
        $model = $this->createModel(1);

        $response = $this->delete($this->route(sprintf('%s.destroy', $this->relationship), $model->id));
        $this->validateResponse($response, 403);
    }

    /**
     * Create the relationship model instance.
     *
     * @param Model $parent
     * @param int   $times
     *
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    abstract protected function createRelationshipModel(Model $parent, int $times = 1);
}
