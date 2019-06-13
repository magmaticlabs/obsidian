<?php

namespace Tests\Feature\API\APIFeatures;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\ResourceTest\ResourceTest;

/**
 * @internal
 * @coversNothing
 */
final class SparseFieldsTest extends ResourceTest
{
    public function testDefaultReturnAll()
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

    public function testSparseReturn()
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

        static::assertSame($attributes, $compare);
    }

    public function testMismatchTypeReturnAll()
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

    public function testUnknownFieldsReturnEmpty()
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

        static::assertSame([], $compare);
    }
}
