<?php

namespace Tests\Feature\API\Repositories\Organization;

use Tests\Feature\API\Repositories\RepositoryTestCase;

/**
 * @internal
 * @coversNothing
 */
final class RelationshipTest extends RepositoryTestCase
{
    public function testCorrectData()
    {
        $response = $this->get($this->getRoute('organization', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $this->organization->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'organizations',
                'id'         => $this->organization->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('organization', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
