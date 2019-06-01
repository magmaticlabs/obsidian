<?php

namespace MagmaticLabs\Obsidian\Domain\Support;

use Ramsey\Uuid\Uuid as Concrete;

final class UUID
{
    /**
     * Concrete instance
     *
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $concrete;

    /**
     * Class constructor
     *
     * @param string $string
     *
     * @throws \Exception
     */
    private function __construct(string $string = '')
    {
        $this->concrete = empty($string) ? Concrete::uuid4() : Concrete::fromString($string);
    }

    /**
     * Create a new instance
     *
     * @return static
     *
     * @throws \Exception
     */
    public static function generate()
    {
        return new static();
    }

    /**
     * Create from a string
     *
     * @param string $string
     *
     * @return static
     *
     * @throws \Exception
     */
    public static function fromString(string $string): UUID
    {
        return new static($string);
    }

    /**
     * Magic method to convert to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->concrete->toString();
    }

    /**
     * Explicit conversion to string
     *
     * @return string
     */
    public function toString(): string
    {
        return (string) $this;
    }
}
