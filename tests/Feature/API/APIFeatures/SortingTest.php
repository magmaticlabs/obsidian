<?php

namespace Tests\Feature\API\APIFeatures;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\ResourceTest\ResourceTest;

/**
 * @internal
 * @coversNothing
 */
final class SortingTest extends ResourceTest
{
    public function testDefaultSortByID()
    {
        /** @var Organization $organization */
        $organizations = $this->factory(Organization::class)->times(10)->create();

        $data = [];
        foreach ($organizations as $organization) {
            $data[] = [
                'id' => (string) $organization->id,
            ];
        }

        $response = $this->get(route('api.organizations.index'));

        $response->assertJson([
            'data' => $this->sortData($data, 'id'),
        ]);
    }

    public function testSortByName()
    {
        /** @var Organization $organization */
        $organizations = $this->factory(Organization::class)->times(10)->create();

        $data = [];
        foreach ($organizations as $organization) {
            $data[] = [
                'id'         => (string) $organization->id,
                'attributes' => [
                    'name' => $organization->name,
                ],
            ];
        }

        $response = $this->get(route('api.organizations.index', 'sort=name'));

        $response->assertJson([
            'data' => $this->sortData($data, 'attributes.name'),
        ]);
    }

    public function testReverseSortByName()
    {
        /** @var Organization $organization */
        $organizations = $this->factory(Organization::class)->times(10)->create();

        $data = [];
        foreach ($organizations as $organization) {
            $data[] = [
                'id'         => (string) $organization->id,
                'attributes' => [
                    'name' => $organization->name,
                ],
            ];
        }

        $response = $this->get(route('api.organizations.index', 'sort=-name'));

        $response->assertJson([
            'data' => array_reverse($this->sortData($data, 'attributes.name')),
        ]);
    }

    public function testMultiLevelNotSupported()
    {
        $response = $this->get(route('api.organizations.index', 'sort=repository.name'));
        $this->validateResponse($response, 400);
    }

    public function testInvalidFormat()
    {
        $response = $this->get(route('api.organizations.index', 'sort[name]'));
        $this->validateResponse($response, 400);
    }

    public function testInvalidAttribute()
    {
        $response = $this->get(route('api.organizations.index', 'sort=foobar'));
        $this->validateResponse($response, 400);
    }
}
