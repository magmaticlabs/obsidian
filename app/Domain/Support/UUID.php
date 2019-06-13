<?php

namespace MagmaticLabs\Obsidian\Domain\Support;

use Ramsey\Uuid\Uuid as Concrete;

final class UUID
{
    /**
     * Concrete instance.
     *
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $concrete;

    /**
     * Class constructor.
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
     * Magic method to convert to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->concrete->toString();
    }

    /**
     * Create a new instance.
     *
     * @throws \Exception
     *
     * @return static
     */
    public static function generate()
    {
        return new static();
    }

    /**
     * Create from a string.
     *
     * @param string $string
     *
     * @throws \Exception
     *
     * @return static
     */
    public static function fromString(string $string): self
    {
        return new static($string);
    }

    /**
     * Explicit conversion to string.
     *
     * @return string
     */
    public function toString(): string
    {
        return (string) $this;
    }
}
