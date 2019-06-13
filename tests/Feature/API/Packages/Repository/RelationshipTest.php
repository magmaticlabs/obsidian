<?php

namespace Tests\Feature\API\Packages\Repository;

use Tests\Feature\API\Packages\PackageTest;

/**
 * @internal
 * @coversNothing
 */
final class RelationshipTest extends PackageTest
{
    public function testCorrectData()
    {
        $response = $this->get($this->getRoute('repository', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $this->repository->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'repositories',
                'id'         => $this->repository->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('repository', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
