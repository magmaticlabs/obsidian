<?php

namespace Tests\Feature\API\APIResource;

use MagmaticLabs\Obsidian\Domain\Eloquent\Model;

/**
 * @property Model  $model
 * @property string $type
 *
 * @mixin \Tests\Feature\API\APIResource\ResourceTestCase
 */
trait CreateTest
{
    public function testCreate()
    {
        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 201);

        $response->assertHeader('Location');

        $location = $response->headers->get('Location');
        $resourceid = basename($location);
        $this->assertSame($this->getRoute('show', $resourceid), $location);

        $data = [
            'type' => $this->type,
            'id'   => $resourceid,
        ];

        if (isset($this->data['data']['attributes'])) {
            $data['attributes'] = $this->data['data']['attributes'];
        }

        $response->assertJson([
            'data' => $data,
        ]);
    }

    public function testClientIDCausesValidationError()
    {
        $this->data['data']['id'] = 'foobar';

        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
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

        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
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

    public function getRequiredAttributes()
    {
        if (empty($this->required)) {
            return [['']];
        }

        return [$this->required];
    }

    /**
     * @dataProvider getRequiredAttributes
     *
     * @param mixed $attribute
     */
    public function testMissingRequiredAttributesCausesValidationError($attribute)
    {
        if (empty($attribute)) {
            // Prevent "no assertions"
            $this->assertTrue(true);

            return;
        }

        unset($this->data['data']['attributes'][$attribute]);

        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->post($this->getRoute('create'), $this->data);
        $this->validateResponse($response, 400);

        $response->assertJson([
            'errors' => [
                ['source' => ['pointer' => sprintf('/data/attributes/%s', $attribute)]],
            ],
        ]);
    }

    public function getOptionalAttributes()
    {
        $output = [];

        foreach ($this->optional as $key => $value) {
            $output[] = [$key, $value];
        }

        // Prevent skipped tests
        if (empty($output)) {
            return [['', '']];
        }

        return $output;
    }

    /**
     * @dataProvider getOptionalAttributes
     *
     * @param mixed $attribute
     * @param mixed $value
     */
    public function testMissingOptionalAttributesSetToDefault($attribute, $value)
    {
        if (empty($attribute)) {
            // Prevent "no assertions"
            $this->assertTrue(true);

            return;
        }

        unset($this->data['data']['attributes'][$attribute]);

        /** @var \Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this->post($this->getRoute('create'), $this->data);
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
