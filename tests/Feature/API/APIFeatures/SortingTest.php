<?php

namespace Tests\Feature\API\APIFeatures;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\APIResource\ResourceTestCase;

/**
 * @internal
 * @coversNothing
 */
final class SortingTest extends ResourceTestCase
{
    /**
     * @test
     */
    public function default_sort_by_id()
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

    /**
     * @test
     */
    public function sort_by_name()
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

    /**
     * @test
     */
    public function reverse_sort_by_name()
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

    /**
     * @test
     */
    public function multi_level_not_supported()
    {
        $response = $this->get(route('api.organizations.index', 'sort=repository.name'));
        $this->validateResponse($response, 400);
    }

    /**
     * @test
     */
    public function invalid_format()
    {
        $response = $this->get(route('api.organizations.index', 'sort[name]'));
        $this->validateResponse($response, 400);
    }

    /**
     * @test
     */
    public function invalid_attribute()
    {
        $response = $this->get(route('api.organizations.index', 'sort=foobar'));
        $this->validateResponse($response, 400);
    }
}
