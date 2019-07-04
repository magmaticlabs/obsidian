<?php

namespace Tests\Feature\API\APIFeatures;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use MagmaticLabs\Obsidian\Domain\Support\Paginator;
use Tests\Feature\API\ResourceTests\ResourceTestCase;

/**
 * @internal
 * @coversNothing
 */
final class PaginationTest extends ResourceTestCase
{
    /**
     * @test
     */
    public function default_empty()
    {
        $response = $this->get(route('api.organizations.index'));

        $response->assertJson([
            'meta' => [
                'pagination' => [
                    'total'  => 0,
                    'count'  => 0,
                    'limit'  => Paginator::DEFAULT_LIMIT,
                    'number' => 1,
                    'pages'  => 1,
                ],
            ],
        ]);
    }

    public function dataTotal()
    {
        return [
            [5],
            [10],
            [15],
            [100],
            [150],
            [27],
            [345],
        ];
    }

    public function dataTotalLimits()
    {
        return [
            [5, 2],
            [10, 0],
            [15, 4],
            [100, 15],
            [150, 100],
            [27, 10],
            [300, 150],
        ];
    }

    /**
     * @dataProvider dataTotal
     *
     * @param mixed $total
     *
     * @test
     */
    public function num_pages($total)
    {
        $this->factory(Organization::class)->times($total)->create();

        $response = $this->get(route('api.organizations.index'));

        $response->assertJson([
            'meta' => [
                'pagination' => [
                    'pages' => (int) ceil($total / Paginator::DEFAULT_LIMIT),
                ],
            ],
        ]);
    }

    /**
     * @dataProvider dataTotal
     *
     * @param mixed $total
     *
     * @test
     */
    public function counts($total)
    {
        $this->factory(Organization::class)->times($total)->create();

        $response = $this->get(route('api.organizations.index'));

        $count = min(Paginator::DEFAULT_LIMIT, $total);

        $response->assertJson([
            'meta' => [
                'pagination' => [
                    'total' => $total,
                    'count' => $count,
                ],
            ],
        ]);

        $data = json_decode($response->getContent(), true);
        $this->assertSame($count, \count($data['data']));
    }

    /**
     * @dataProvider dataTotalLimits
     *
     * @param mixed $total
     * @param mixed $limit
     *
     * @test
     */
    public function with_limit($total, $limit)
    {
        $this->factory(Organization::class)->times($total)->create();

        $response = $this->get(route('api.organizations.index', "page[limit]={$limit}"));

        $limit = max(1, min(Paginator::MAX_LIMIT, $limit));
        $count = min($limit, $total);

        $response->assertJson([
            'meta' => [
                'pagination' => [
                    'total' => $total,
                    'count' => $count,
                    'limit' => $limit,
                    'pages' => (int) ceil($total / $limit),
                ],
            ],
        ]);

        $data = json_decode($response->getContent(), true);
        $this->assertSame($count, \count($data['data']));
    }

    /**
     * @test
     */
    public function links_first_page()
    {
        $this->factory(Organization::class)->times(5)->create();

        $response = $this->get(route('api.organizations.index', 'page[limit]=1'));

        $response->assertJson([
            'links' => [
                'first' => route('api.organizations.index', 'page[limit]=1&page[number]=1'),
                'next'  => route('api.organizations.index', 'page[limit]=1&page[number]=2'),
                'last'  => route('api.organizations.index', 'page[limit]=1&page[number]=5'),
            ],
        ]);
    }

    /**
     * @test
     */
    public function links_second_page()
    {
        $this->factory(Organization::class)->times(5)->create();

        $response = $this->get(route('api.organizations.index', 'page[limit]=1&page[number]=2'));

        $response->assertJson([
            'links' => [
                'first' => route('api.organizations.index', 'page[limit]=1&page[number]=1'),
                'next'  => route('api.organizations.index', 'page[limit]=1&page[number]=3'),
                'prev'  => route('api.organizations.index', 'page[limit]=1&page[number]=1'),
                'last'  => route('api.organizations.index', 'page[limit]=1&page[number]=5'),
            ],
        ]);
    }

    /**
     * @test
     */
    public function data_is_different()
    {
        $this->factory(Organization::class)->times(5)->create();

        $response = $this->get(route('api.organizations.index', 'page[limit]=1&page[number]=2'));
        $A = json_decode($response->getContent(), true);

        $response = $this->get(route('api.organizations.index', 'page[limit]=1&page[number]=3'));
        $B = json_decode($response->getContent(), true);

        $this->assertNotSame($A['data'], $B['data']);
    }
}
