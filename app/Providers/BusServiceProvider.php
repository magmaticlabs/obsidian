<?php

namespace MagmaticLabs\Obsidian\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use MagmaticLabs\Obsidian\Domain\Support\CommandBus;

final class BusServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected $defer = true;

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerCommandBus();
    }

    /**
     * {@inheritdoc}
     */
    public function provides()
    {
        return [CommandBus::class];
    }

    /**
     * Register the command bus and subscribe handlers.
     */
    private function registerCommandBus()
    {
        $this->app->singleton(CommandBus::class, function (Application $app) {
            $bus = new CommandBus();

            $namespace = __NAMESPACE__;
            $namespace = \dirname(strtr($namespace, ['\\' => '/']));
            $namespace = sprintf('\\%s\\Domain\\CommandHandlers', strtr($namespace, ['/' => '\\']));

            $handlers = scandir(app_path('Domain/CommandHandlers'));
            foreach ($handlers as $handlerfile) {
                if (!preg_match('/\.php$/', $handlerfile)) {
                    continue;
                }

                $handlerclass = sprintf('%s\\%s', $namespace, substr($handlerfile, 0, -4));

                /** @var \MagmaticLabs\Obsidian\Domain\Support\CommandHandler $handler */
                $handler = $app->make($handlerclass);
                $handler->subscribe($bus);
            }

            return $bus;
        });
    }
}
