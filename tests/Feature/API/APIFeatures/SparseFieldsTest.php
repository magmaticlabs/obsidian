<?php

namespace Tests\Feature\API\APIFeatures;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\APIResource\ResourceTestCase;

/**
 * @internal
 * @coversNothing
 */
final class SparseFieldsTest extends ResourceTestCase
{
    /**
     * @test
     */
    public function default_return_all()
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $attributes = $organization->toArray();
        unset($attributes['id']);

        $response = $this->get(route('api.organizations.index'));

        $response->assertJson([
            'data' => [
                [
                    'attributes' => $attributes,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function sparse_return()
    {
        $fields = [
            'display_name',
        ];

        sort($fields);

        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();

        $attributes = [];
        foreach ($fields as $field) {
            $attributes[$field] = $organization->{$field};
        }

        $response = $this->get(route('api.organizations.index', [
            'fields[organizations]' => implode(',', $fields),
        ]));

        $compare = json_decode($response->getContent(), true);
        $compare = $compare['data'][0]['attributes'];

        ksort($compare);

        $this->assertSame($attributes, $compare);
    }

    /**
     * @test
     */
    public function mismatch_type_return_all()
    {
        /** @var Organization $organization */
        $organization = $this->factory(Organization::class)->create();
        $attributes = $organization->toArray();
        unset($attributes['id']);

        $response = $this->get(route('api.organizations.index', [
            'fields[foobar]' => 'display_name',
        ]));

        $response->assertJson([
            'data' => [
                [
                    'attributes' => $attributes,
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function unknown_fields_return_empty()
    {
        $fields = [
            '__INVALID__',
        ];

        sort($fields);

        $this->factory(Organization::class)->create();

        $response = $this->get(route('api.organizations.index', [
            'fields[organizations]' => implode(',', $fields),
        ]));

        $compare = json_decode($response->getContent(), true);
        $compare = $compare['data'][0]['attributes'];

        ksort($compare);

        $this->assertSame([], $compare);
    }
}
