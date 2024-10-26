<?php
namespace Concept\App;

use Psr\Container\ContainerInterface;
use Concept\App\ConfigInterface;
use Concept\App\Config;
use Concept\Factory\FactoryInterface;
use Concept\App\AppInterface;
use Concept\Di\Factory\DiFactoryInterface;

class Bootloader    
{
    public function createApp(string $root, string $initialConfigPath): AppInterface
    {
        $config = $this->createConfig($root, $initialConfigPath);
        $factory = $this->createFactory($config);
//!!!!!!use AppFactory to create app instance        
        $appFactory = $factory->create(AppFactoryInterface::class);
        $app = $appFactory
            ->withConfig($config->fromPath('app'))
            ->create();
        
        foreach ($config->fromPath('app.middlewares') as $middleware) {
            $app = $app->withMiddleware($factory->create($middleware));
        }
        
        return $app;
    }

    

    protected function createConfig(string $root, string $initialConfigPath): ConfigInterface
    {
        $root = realpath($root);

        if (false === $root) {
            throw new \RuntimeException('Root not found');
        }

        $initialConfigPath = realpath(sprintf('%s%s%s', $root, DIRECTORY_SEPARATOR, $initialConfigPath));


        if (false === $initialConfigPath) {
            throw new \RuntimeException('Initial config not found');
        }

        $config = (new Config());

        $config
            ->load($initialConfigPath)
            ->merge(['root' => realpath($root)])
        ;

        return $config;
    }

    protected function createFactory(ConfigInterface $config): FactoryInterface
    {
        $factoryClass = $config->get('factory.class');

        if (!is_a($factoryClass, DiFactoryInterface::class, true)) {
            throw new \RuntimeException(sprintf('Class %s must implement FactoryInterface', $factoryClass));
        }

        $factory = (new $factoryClass())
            ->withConfig($config->fromPath(DiFactoryInterface::NODE_DI_CONFIG));

        $container = $this->createContainer($factory, $config);
        $factory = $factory->withContainer($container);
        $container->attach(FactoryInterface::class, $factory);

        return $factory;
    }

    protected function createContainer(FactoryInterface $factory, ConfigInterface $config): ContainerInterface
    {
        $container =  $factory->create(ContainerInterface::class);

        $container
            ->attach(ContainerInterface::class, $container)
            ->attach(ConfigInterface::class, $config)
            ->attach(FactoryInterface::class, $factory);
        
        return $container;
    }

}