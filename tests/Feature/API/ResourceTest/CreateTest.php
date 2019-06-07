<?php

namespace Tests\Feature\API\ResourceTest;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

/**
 * @property Model  $model
 * @property string $type
 *
 * @mixin \Tests\Feature\API\ResourceTest\ResourceTest
 */
trait CreateTest
{
    public function testCreate()
    {
        /* @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $location = $response->headers->get('Location');
        $resourceid = basename($location);
        $this->assertEquals($this->getRoute('show', $resourceid), $location);

        $response->assertJson([
            'data' => [
                'type'       => $this->type,
                'id'         => $resourceid,
                'attributes' => $this->data['data']['attributes'],
            ],
        ]);
    }

    public function testClientIDCausesValidationError()
    {
        $this->data['data']['id'] = 'foobar';

        /* @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->post($this->getRoute('create'), $this->data);
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

        /* @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);

        $this->data['data']['type'] = '__INVALID__';

        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => '/data/type']],
            ],
        ]);
    }

    public function testMissingRequiredAttributesCausesValidationError()
    {
        foreach ($this->required as $attribute) {
            $data = $this->data;
            unset($data['data']['attributes'][$attribute]);

            /* @var \Illuminate\Foundation\Testing\TestResponse $response */
            $response = $this->post($this->getRoute('create'), $data);
            $this->validateResponse($response, 400);

            $response->assertJson([
                'errors' => [
                    ['source' => ['pointer' => sprintf('/data/attributes/%s', $attribute)]],
                ],
            ]);
        }
    }

    public function testMissingOptionalAttributesSetToDefault()
    {
        foreach ($this->optional as $attribute => $value) {
            $data = $this->data;
            unset($data['data']['attributes'][$attribute]);

            /* @var \Illuminate\Foundation\Testing\TestResponse $response */
            $response = $this->post($this->getRoute('create'), $data);
            $this->validateResponse($response, 201);

            $response->assertJson([
                'data' => [
                    'attributes' => [
                        $attribute => $value,
                    ],
                ],
            ]);
        }
    }
}
