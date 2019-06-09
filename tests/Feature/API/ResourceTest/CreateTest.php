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

    public function getRequiredAttributes()
    {
        return [$this->required];
    }

    /**
     * @dataProvider getRequiredAttributes
     */
    public function testMissingRequiredAttributesCausesValidationError($attribute)
    {
        unset($this->data['data']['attributes'][$attribute]);

        /* @var \Illuminate\Foundation\Testing\TestResponse $response */
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
     */
    public function testMissingOptionalAttributesSetToDefault($attribute, $value)
    {
        if (empty($attribute)) {
            // Prevent "no assertions"
            $this->assertTrue(true);

            return;
        }

        unset($this->data['data']['attributes'][$attribute]);

        /* @var \Illuminate\Foundation\Testing\TestResponse $response */
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
