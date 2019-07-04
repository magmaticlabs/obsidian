<?php

namespace Tests\Feature\API\ResourceTests;

use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @mixin \Tests\Feature\API\ResourceTests\ResourceTestCase
 */
trait TestDeleteEndpoints
{
    /**
     * @test
     */
    public function delete_successful()
    {
        $resource = $this->createResource();

        $response = $this->delete(route("api.{$this->resourceType}.destroy", $resource->id));

        $this->validateResponse($response, 204);
    }

    /**
     * @test
     */
    public function resource_deleted()
    {
        $resource = $this->createResource();

        $this->delete(route("api.{$this->resourceType}.destroy", $resource->id));

        $this->expectException(ModelNotFoundException::class);
        $resource->refresh();
    }

    /**
     * @test
     */
    public function delete_non_exist()
    {
        $response = $this->delete(route("api.{$this->resourceType}.destroy", '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
