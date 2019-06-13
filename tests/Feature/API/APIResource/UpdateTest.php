<?php

namespace Tests\Feature\API\APIResource;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

/**
 * @property Model  $model
 * @property string $type
 *
 * @mixin \Tests\Feature\API\APIResource\ResourceTestCase
 */
trait UpdateTest
{
    public function testUpdate()
    {
        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 200);

        $response->assertJson([
            'data' => [
                'type'       => $this->type,
                'id'         => $this->model->id,
                'attributes' => $this->data['data']['attributes'],
            ],
        ]);
    }

    public function testMissingOrInvalidIDCausesValidationError()
    {
        unset($this->data['data']['id']);

        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);

        $this->data['data']['id'] = 'foobar';

        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/id']],
            ],
        ]);
    }

    public function testMissingOrInvalidTypeCausesValidationError()
    {
        unset($this->data['data']['type']);

        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);

        $this->data['data']['type'] = '__INVALID__';

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testNoAttributesValidWithNoop()
    {
        unset($this->data['data']['attributes']);

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 200);

        $attributes = $this->model->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                'attributes' => $attributes,
            ],
        ]);
    }

    public function testRelationshipFails()
    {
        $this->data['relationships'] = [
            'something' => [
                'data' => [
                    'type' => 'foobar',
                    'id'   => 'foobar',
                ],
            ],
        ];

        $response = $this->patch($this->getRoute('update', $this->model->id), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/relationships']],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->patch($this->getRoute('update', '__INVALID__'), $this->data);
        $this->validateResponse($response, 404);
    }
}
