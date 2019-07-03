<?php

namespace Tests\Feature\API\APIFeatures;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\APIResource\ResourceTestCase;

/**
 * @internal
 * @coversNothing
 */
final class FilterTest extends ResourceTestCase
{
    /**
     * @test
     */
    public function default_no_filter()
    {
        $count = 7;
        $this->factory(Organization::class)->times($count)->create();

        $response = $this->get(route('api.organizations.index'));
        $data = json_decode($response->getContent(), true);

        $this->assertSame($count, \count($data['data']));
    }

    /**
     * @test
     */
    public function simple_filter()
    {
        $this->factory(Organization::class)->times(5)->create();
        $this->factory(Organization::class)->create([
            'name' => 'foobar',
        ]);

        $response = $this->get(route('api.organizations.index', 'filter[name]=foobar'));
        $data = json_decode($response->getContent(), true);

        $this->assertSame(1, \count($data['data']));
    }

    /**
     * @test
     */
    public function operations_filter()
    {
        $count = 5;
        $this->factory(Organization::class)->times($count)->create();
        $this->factory(Organization::class)->create([
            'name' => 'foobar',
        ]);

        $response = $this->get(route('api.organizations.index', 'filter[name]=!=foobar'));
        $data = json_decode($response->getContent(), true);

        $this->assertSame($count, \count($data['data']));
    }

    /**
     * @test
     */
    public function wildcard_filter()
    {
        $count = 5;
        $this->factory(Organization::class)->times($count)->create();
        $this->factory(Organization::class)->create([
            'name' => 'foobar',
        ]);
        $this->factory(Organization::class)->create([
            'name' => 'foobaz',
        ]);
        $this->factory(Organization::class)->create([
            'name' => 'foobuzz',
        ]);

        $response = $this->get(route('api.organizations.index', 'filter[name]=foo*'));
        $data = json_decode($response->getContent(), true);

        $this->assertSame(3, \count($data['data']));
    }

    /**
     * @test
     */
    public function negative_wildcard_filter()
    {
        $count = 5;
        $this->factory(Organization::class)->times($count)->create();
        $this->factory(Organization::class)->create([
            'name' => 'foobar',
        ]);
        $this->factory(Organization::class)->create([
            'name' => 'foobaz',
        ]);
        $this->factory(Organization::class)->create([
            'name' => 'foobuzz',
        ]);

        $response = $this->get(route('api.organizations.index', 'filter[name]=!=foo*'));
        $data = json_decode($response->getContent(), true);

        $this->assertSame($count, \count($data['data']));
    }

    /**
     * @test
     */
    public function filter_percent()
    {
        $count = 5;
        $this->factory(Organization::class)->times($count)->create();
        $this->factory(Organization::class)->create([
            'name' => 'foobar%buzz',
        ]);
        $this->factory(Organization::class)->create([
            'name' => 'foobar!buzz',
        ]);

        $response = $this->get(route('api.organizations.index', 'filter[name]==foobar%buzz'));
        $data = json_decode($response->getContent(), true);

        $this->assertSame(1, \count($data['data']));
    }

    /**
     * @test
     */
    public function multi_level_not_supported()
    {
        $response = $this->get(route('api.organizations.index', 'filter[repository.name]=foobar'));
        $this->validateResponse($response, 400);
    }

    /**
     * @test
     */
    public function invalid_format()
    {
        $response = $this->get(route('api.organizations.index', 'filter=foobar'));
        $this->validateResponse($response, 400);
    }

    /**
     * @test
     */
    public function invalid_attribute()
    {
        $response = $this->get(route('api.organizations.index', 'filter[foobar]=foobar'));
        $this->validateResponse($response, 400);
    }
}
