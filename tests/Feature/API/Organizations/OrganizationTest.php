<?php

namespace Tests\Feature\API\Organizations;

use MagmaticLabs\Obsidian\Domain\Eloquent\Organization;
use Tests\Feature\API\ResourceTest\ResourceTest;

abstract class OrganizationTest extends ResourceTest
{
    /**
     * Resource type
     *
     * @var string
     */
    protected $type = 'organizations';

    /**
     * Model instance
     *
     * @var Organization
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->model = $this->factory(Organization::class)->create();
        $this->model->addMember($this->user);
        $this->model->promoteMember($this->user);

        $this->data = [
            'data' => [
                'type'       => 'organizations',
                'attributes' => [
                    'name'         => 'testing',
                    'display_name' => '__TESTING__',
                    'description'  => 'This is a test organization',
                ],
            ],
        ];
    }

    /**
     * Demote the authenticated user from owner of the organization
     */
    protected function demote()
    {
        $this->model->demoteMember($this->user);
    }

    // --

    public function invalidDataName()
    {
        return [
            'non-string'  => [[]],
            'too-short'   => ['no'],
            'regex-break' => ['This is Illegal!'],
        ];
    }

    public function invalidDataDisplayName()
    {
        return [
            'non-string'  => [[]],
            'too-short'   => ['no'],
        ];
    }

    public function invalidDataDescription()
    {
        return [
            'non-string'  => [[]],
        ];
    }
}
