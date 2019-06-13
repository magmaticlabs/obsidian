<?php

namespace Tests\Feature\API\Builds\Package;

use Tests\Feature\API\Builds\BuildTestCase;

/**
 * @internal
 * @coversNothing
 */
final class IndexTest extends BuildTestCase
{
    public function testCorrectData()
    {
        $response = $this->get($this->getRoute('package.index', $this->model->id));
        $this->validateResponse($response, 200);

        $response->assertJsonFragment([
            'data' => [
                'type' => 'packages',
                'id'   => $this->package->id,
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('package.index', '__INVALID__'));
        $this->validateResponse($response, 404);
    }
}
