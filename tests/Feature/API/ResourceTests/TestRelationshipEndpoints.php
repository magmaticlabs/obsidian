<?php

namespace Tests\Feature\API\ResourceTests;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @mixin \Tests\Feature\API\ResourceTests\ResourceTestCase
 */
trait TestRelationshipEndpoints
{
    abstract public function relationshipProvider(): array;

    /**
     * @test
     * @dataProvider relationshipProvider
     */
    public function relationship_missing_parent_model(string $relation)
    {
        $response = $this->get(route("api.{$this->resourceType}.{$relation}", '__INVALID__'));
        $this->validateResponse($response, 404);

        $response = $this->get(route("api.{$this->resourceType}.{$relation}.index", '__INVALID__'));
        $this->validateResponse($response, 404);

        $response = $this->post(route("api.{$this->resourceType}.{$relation}.create", '__INVALID__'));
        $this->validateResponse($response, 404);

        $response = $this->patch(route("api.{$this->resourceType}.{$relation}.update", '__INVALID__'));
        $this->validateResponse($response, 404);

        $response = $this->delete(route("api.{$this->resourceType}.{$relation}.destroy", '__INVALID__'));
        $this->validateResponse($response, 404);
    }

    /**
     * @test
     * @dataProvider relationshipProvider
     */
    public function relationship_data(string $relation, string $type, bool $collection = true)
    {
        $resource = $this->createResource();
        $obj = $this->createRelationship($resource, $relation);
        if ($collection) {
            $obj = $obj->first();
        }

        // Concrete

        $response = $this->get(route("api.{$this->resourceType}.{$relation}", $resource->id));
        $this->validateResponse($response, 200);

        $compare = $this->get(route("api.{$type}.show", $obj->id));
        $compare = json_decode($compare->getContent(), true)['data'];

        $response->assertJson([
            'data' => $collection ? [$compare] : $compare,
        ]);

        // Relation

        $response = $this->get(route("api.{$this->resourceType}.{$relation}.index", $resource->id));
        $this->validateResponse($response, 200);

        $item = [
            'type' => $type,
            'id'   => $obj->id,
        ];

        $response->assertJson([
            'data' => $collection ? [$item] : $item,
        ]);
    }

    /**
     * @test
     * @dataProvider relationshipProvider
     */
    public function relationship_counts_matches(string $relation, string $type, bool $collection = true)
    {
        if (!$collection) {
            $this->expectNotToPerformAssertions();

            return;
        }

        $resource = $this->createResource();

        $count = 5;

        $this->createRelationship($resource, $relation, $count);

        $response = $this->get(route("api.{$this->resourceType}.{$relation}", $resource->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertSame($count, \count($data['data']));

        $response = $this->get(route("api.{$this->resourceType}.{$relation}.index", $resource->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertSame($count, \count($data['data']));
    }

    // --

    /**
     * @test
     * @dataProvider relationshipProvider
     */
    public function create_relationship(string $relation)
    {
        $resource = $this->createResource();

        $response = $this->post(route("api.{$this->resourceType}.{$relation}.create", $resource->id));
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     * @dataProvider relationshipProvider
     */
    public function update_relationship(string $relation)
    {
        $resource = $this->createResource();

        $response = $this->patch(route("api.{$this->resourceType}.{$relation}.update", $resource->id));
        $this->validateResponse($response, 403);
    }

    /**
     * @test
     * @dataProvider relationshipProvider
     */
    public function delete_relationship(string $relation)
    {
        $resource = $this->createResource();

        $response = $this->delete(route("api.{$this->resourceType}.{$relation}.destroy", $resource->id));
        $this->validateResponse($response, 403);
    }

    /**
     * @return EloquentModel|\Illuminate\Database\Eloquent\Collection
     */
    abstract protected function createRelationship(EloquentModel $resource, string $relation, int $times = 1);
}
