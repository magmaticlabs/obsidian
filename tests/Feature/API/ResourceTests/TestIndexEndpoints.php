<?php

namespace Tests\Feature\API\ResourceTests;

/**
 * @mixin \Tests\Feature\API\ResourceTests\ResourceTestCase
 */
trait TestIndexEndpoints
{
    /**
     * @test
     */
    public function index_data_matches_show()
    {
        $resource = $this->createResource();

        $response = $this->get(route("api.{$this->resourceType}.index"));
        $this->validateResponse($response, 200);

        $compare = $this->get(route("api.{$this->resourceType}.show", $resource->id));
        $data = json_decode($compare->getContent(), true)['data'];

        $response->assertJson([
            'data' => [
                $data,
            ],
        ]);
    }

    /**
     * @test
     */
    public function index_number_of_entries_is_correct()
    {
        $response = $this->get(route("api.{$this->resourceType}.index"));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertSame(0, \count($data['data']));

        // --

        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $this->createResource();
        }

        $response = $this->get(route("api.{$this->resourceType}.index"));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true)['data'];
        $this->assertSame($count, \count($data));
    }
}
