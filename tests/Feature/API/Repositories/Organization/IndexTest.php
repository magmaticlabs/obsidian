<?php

namespace Tests\Feature\API\Repositories\Organization;

use Tests\Feature\API\Repositories\RepositoryTestCase;

/**
 * @internal
 * @coversNothing
 */
final class IndexTest extends RepositoryTestCase
{
    public function testCorrectData()
    {
        $response = $this->get($this->getRoute('organization.index', $this->model->id));
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [
                'type' => 'organizations',
                'id'   => $this->organization->id,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('organization.index', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
