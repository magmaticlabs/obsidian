<?php

namespace Tests\Feature\API\Tokens;

use MagmaticLabs\Obsidian\Domain\Eloquent\User;

final class IndexTest extends TokenTest
{
    public function testDataMatchesShow()
    {
        $response = $this->get($this->getRoute('index'));
        $this->validateResponse($response, 200);

        $compare = $this->get($this->getRoute('show', $this->model->id));
        $compare = json_decode($compare->getContent(), true);

        $response->assertJson([
            'data' => [
                $compare['data'],
            ],
        ]);
    }

    public function testCountsMatches()
    {
        // Empty the collection
        $class = get_class($this->model);
        $class::query()->delete();

        $response = $this->get($this->getRoute('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(0, count($data['data']));

        // --

        $count = 5;

        for ($i = 0; $i < $count; ++$i) {
            $this->user->createToken('__TESTING__');
        }

        $response = $this->get($this->getRoute('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($count, count($data['data']));
    }

    public function testOnlyShowsMine()
    {
        $class = get_class($this->model);
        $class::query()->delete();

        /** @var User $owner */
        $owner = factory(User::class)->create();
        $owner->createToken('_test_')->token;

        $response = $this->get($this->getRoute('index'));
        $this->validateResponse($response, 200);

        $data = json_decode($response->getContent(), true);
        $this->assertEmpty($data['data']);
    }
}
