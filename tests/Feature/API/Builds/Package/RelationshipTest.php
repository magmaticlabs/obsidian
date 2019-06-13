<?php

namespace Tests\Feature\API\Builds\Package;

use Tests\Feature\API\Builds\BuildTestCase;

/**
 * @internal
 * @coversNothing
 */
final class RelationshipTest extends BuildTestCase
{
    public function testCorrectData()
    {
        $response = $this->get($this->getRoute('package', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $this->package->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'type'       => 'packages',
                'id'         => $this->package->id,
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('package', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
