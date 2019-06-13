<?php

namespace Tests\Feature\API\Packages\Repository;

use Tests\Feature\API\Packages\PackageTestCase;

/**
 * @internal
 * @coversNothing
 */
final class IndexTest extends PackageTestCase
{
    public function testCorrectData()
    {
        $response = $this->get($this->getRoute('repository.index', $this->model->id));
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [
                'type' => 'repositories',
                'id'   => $this->repository->id,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('repository.index', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
