<?php

namespace MagmaticLabs\Obsidian\Domain\Support;

use Carbon\Carbon;
use MagmaticLabs\Obsidian\Domain\Eloquent\User;

class Command
{
    /**
     * Type of command
     *
     * @var string
     */
    protected $type;

    /**
     * Data passed into the command
     *
     * @var array
     */
    protected $data;

    /**
     * ID of the object being acted on
     *
     * @var string
     */
    protected $objectid;

    /**
     * Timestamp of when the command was issued
     *
     * @var \Carbon\Carbon|\Carbon\CarbonInterface
     */
    protected $timestamp;

    /**
     * Context around the command
     *
     * @var string
     */
    protected $context;

    /**
     * Currently authenticated user
     *
     * @var \MagmaticLabs\Obsidian\Domain\Eloquent\User
     */
    protected $user;

    /**
     * Class constructor
     *
     * @param string $type
     * @param array  $data
     */
    public function __construct(string $type, array $data)
    {
        $this->type = strtolower(trim($type));
        $this->data = $data;
        $this->objectid = $this->findObjectId($data);
        $this->timestamp = Carbon::now();
        $this->user = User::find(auth()->user()->getAuthIdentifier());
        $this->context = $this->findContext(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10));
    }

    /**
     * Type accessor
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Data accessor
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Object id accessor
     *
     * @return string
     */
    public function getObjectId(): string
    {
        return $this->objectid;
    }

    /**
     * Timestamp accessor
     *
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp->toString();
    }

    /**
     * Context accessor
     *
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * User accessor
     *
     * @return array
     */
    public function getUser(): array
    {
        return $this->user->toArray();
    }

    /**
     * Dump all info to an array
     *
     * @return array
     */
    public function dump()
    {
        return [
            'objectid'  => $this->objectid,
            'timestamp' => $this->timestamp->toString(),
            'context'   => $this->context,
            'user'      => $this->user->toArray(),
            'data'      => $this->data,
        ];
    }

    /**
     * Find the object ID
     *
     * @param array $data
     *
     * @return string|null
     */
    private function findObjectId(array $data): ?string
    {
        if (isset($data['id'])) {
            return $data['id'];
        }

        if (isset($data['attributes']['id'])) {
            return $data['attributes']['id'];
        }

        return null;
    }

    /**
     * Find the context around the command
     *
     * @param array $backtrace
     *
     * @return string
     */
    private function findContext(array $backtrace): string
    {
        if (isset($backtrace[0]['file'])) {
            if (preg_match('#API/(.+?)Controller\.php#', $backtrace[0]['file'], $matches)) {
                $resource = strtolower($matches[1]);

                if (isset($backtrace[1]['function'])) {
                    $action = $backtrace[1]['function'];
                } else {
                    $action = 'unknown';
                }

                return sprintf('api:%s.%s', $resource, $action);
            }
        }

        return 'unknown';
    }
}
