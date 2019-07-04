<?php

namespace Tests\Feature\API\ResourceTests;

/**
 * @mixin \Tests\Feature\API\ResourceTests\ResourceTestCase
 */
trait TestShowEndpoints
{
    /**
     * @test
     */
    public function show_data_matches_model()
    {
        $resource = $this->createResource();

        $response = $this->get(route("api.{$this->resourceType}.show", $resource->id));
        $this->validateResponse($response, 200);

        $attributes = $resource->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => $this->resourceType,
                'id'         => $resource->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    /**
     * @test
     */
    public function show_unknown_id_gives_404()
    {
        $response = $this->get(route("api.{$this->resourceType}.show", '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
