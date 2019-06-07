<?php

namespace Tests\Feature\API\Organizations\Members;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;
use Tests\Feature\API\Organizations\OrganizationTest;

final class RelationshipTest extends OrganizationTest
{
    public function testCorrectCounts()
    {
        $response = $this->get($this->getRoute('members', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(1, count($data['data']));

        // --

        $count = 5;

        foreach (factory(User::class)->times($count)->create() as $user) {
            $this->model->addMember($user);
        }

        $response = $this->get($this->getRoute('members', $this->model->id));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count + 1, count($data['data']));
    }

    public function testCorrectData()
    {
        $user = factory(User::class)->create();
        $this->model->addMember($user);

        $response = $this->get($this->getRoute('members', $this->model->id));
        $this->validateResponse($response, 200);

        $attributes = $user->toArray();
        unset($attributes['id']);

        $response->assertJson([
            'data' => [
                [
                    'type'       => 'users',
                    'id'         => $this->user->id,
                ],
                [
                    'type'       => 'users',
                    'id'         => $user->id,
                    'attributes' => $attributes,
                ],
            ],
        ]);
    }

    public function testNonExist()
    {
        $response = $this->get($this->getRoute('members', '__INVAILD__'));
        $this->validateResponse($response, 404);
    }
}
