<?php

namespace MagmaticLabs\Obsidian\Domain\Support;

abstract class CommandHandler
{
    /**
     * Register command callbacks for this handler with the command bus
     *
     * @param CommandBus $bus
     */
    public function subscribe(CommandBus $bus): void
    {
        try {
            $self = new \ReflectionClass($this);
        } catch (\ReflectionException $e) {
            return;
        }

        // Attempt to find callbacks via reflection

        foreach ($self->getMethods() as $method) {
            $comment = $method->getDocComment();
            if (preg_match('/@command (.+?)\n/', $comment, $matches)) {
                $bus->register($matches[1], [$this, $method->getName()]);
            }
        }
    }
}
