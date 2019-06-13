<?php

namespace Tests\Feature\API\Organizations\Owners;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\Organizations\OrganizationTest;

/**
 * @internal
 * @coversNothing
 */
final class IndexTest extends OrganizationTest
{
    public function testCorrectCounts()
    {
        $response = $this->get($this->getRoute('owners.index', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame(1, \count($data['data']));

        // --

        $count = 5;

        foreach (factory(User::class)->times($count)->create() as $user) {
            $this->model->addMember($user);
            $this->model->promoteMember($user);
        }

        $response = $this->get($this->getRoute('owners.index', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        static::assertSame($count + 1, \count($data['data']));
    }

    public function testCorrectData()
    {
        $user = $this->factory(User::class)->create();
        $this->model->addMember($user);
        $this->model->promoteMember($user);

        $response = $this->get($this->getRoute('owners.index', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $user->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => $this->sortData([
                [
                    'type' => 'users',
                    'id'   => $this->user->id,
                ],
                [
                    'type' => 'users',
                    'id'   => $user->id,
                ],
            ], 'id'),
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('owners.index', '__INVAILD__'));
        $this->validateResponse($response, 404);
    }
}
