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
    public function testDefaultNoFilter()
    {
        $count = 7;
        $this->factory(Organization::class)->times($count)->create();

        $response = $this->get(route('api.organizations.index'));
        $data = json_decode($response->getContent(), true);

        static::assertSame($count, \count($data['data']));
    }

    public function testSimpleFilter()
    {
        $this->factory(Organization::class)->times(5)->create();
        $this->factory(Organization::class)->create([
            'name' => 'foobar',
        ]);

        $response = $this->get(route('api.organizations.index', 'filter[name]=foobar'));
        $data = json_decode($response->getContent(), true);

        static::assertSame(1, \count($data['data']));
    }

    public function testOperationsFilter()
    {
        $count = 5;
        $this->factory(Organization::class)->times($count)->create();
        $this->factory(Organization::class)->create([
            'name' => 'foobar',
        ]);

        $response = $this->get(route('api.organizations.index', 'filter[name]=!=foobar'));
        $data = json_decode($response->getContent(), true);

        static::assertSame($count, \count($data['data']));
    }

    public function testWildcardFilter()
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

        static::assertSame(3, \count($data['data']));
    }

    public function testNegativeWildcardFilter()
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

        static::assertSame($count, \count($data['data']));
    }

    public function testFilterPercent()
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

        static::assertSame(1, \count($data['data']));
    }

    public function testMultiLevelNotSupported()
    {
        $response = $this->get(route('api.organizations.index', 'filter[repository.name]=foobar'));
        $this->validateResponse($response, 400);
    }

    public function testInvalidFormat()
    {
        $response = $this->get(route('api.organizations.index', 'filter=foobar'));
        $this->validateResponse($response, 400);
    }

    public function testInvalidAttribute()
    {
        $response = $this->get(route('api.organizations.index', 'filter[foobar]=foobar'));
        $this->validateResponse($response, 400);
    }
}
