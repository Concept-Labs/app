<?php

namespace Concept\App;

use Psr\Container\ContainerInterface;
use Concept\App\AppInterface;
use Concept\Di\Factory\Composer\ComposerContext;
use Concept\Di\Factory\Context\ConfigContext;
use Concept\Di\Factory\Context\ConfigContextInterface;
use Concept\Factory\FactoryInterface;
use Concept\Di\Factory\DiFactory;

class Bootloader
{
    /**
     * Create an app
     * 
     * @param string $root
     * @param string $initialConfigPath
     * 
     * @return AppInterface
     */
    public function createApp(string $root, string $initialConfigPath): AppInterface
    {
        $configContext = $this->initializeConfigContext($root, $initialConfigPath);
        $this->validateConfig($configContext);

        $container = $this->initializeContainer($configContext);
        return $this->createApplication($container, $configContext);
    }

    /**
     * Initialize the configuration context
     * 
     * @param string $root
     * @param string $initialConfigPath
     * 
     * @return ConfigContextInterface
     */
    protected function initializeConfigContext(string $root, string $initialConfigPath): ConfigContextInterface
    {
        $configContext = $this->createConfigContext($root, $initialConfigPath);
        //$this->mergeComposerContextIfRequired($configContext);

        return $configContext;
    }

    /**
     * Initialize the container
     * 
     * @param ConfigContextInterface $configContext
     * 
     * @return ContainerInterface
     */
    protected function initializeContainer(ConfigContextInterface $configContext): ContainerInterface
    {
        $factory = $this->createFactory($configContext);
        return $this->createContainer($factory, $configContext);
    }

    /**
     * Create the application
     * 
     * @param ContainerInterface $container
     * @param ConfigContextInterface $configContext
     * 
     * @return AppInterface
     */
    protected function createApplication(ContainerInterface $container, ConfigContextInterface $configContext): AppInterface
    {
        return $container
            ->get(AppFactoryInterface::class)
            ->withConfig($configContext->from('app'))
            ->create();
    }

    /**
     * Create a config
     * 
     * @param string $base      Path to the base directory
     * @param string $filepath  Path to the json file
     * 
     * @return ConfigContextInterface
     */
    protected function createConfigContext(string $base, string $filepath): ConfigContextInterface
    {
        $base = realpath($base);

        if (false === $base) {
            throw new \InvalidArgumentException('Bootloader: Invalid root path provided');
        }

        if (strpos($filepath, DIRECTORY_SEPARATOR) !== 0) {
            $filepath = realpath(join(DIRECTORY_SEPARATOR, [$base, $filepath]));
        }

        if (false === $filepath) {
            throw new \InvalidArgumentException('Bootloader: Invalid initial config path provided');
        }

        $config = (new ConfigContext());
        $config
            ->loadJsonFile($filepath)
            ->merge(['base-path' => $base]);
        
        if ($config->has('composer.load-config-context') && $config->get('composer.load-config-context')) {
            $composerContext = (new ComposerContext())->buildComposerContext();
            /**
             * Merge the config values with the composer values
             * Config values have higher priority and may override package values
             */
            $composerContext->merge($config);
            $config = $composerContext;
        }

        return $config;
    }

    /**
     * Create a factory
     * 
     * @param ConfigContextInterface $configContext
     * 
     * @return FactoryInterface
     */
    protected function createFactory(ConfigContextInterface $configContext): FactoryInterface
    {
        return (new DiFactory())
            ->withConfigContext($configContext->from(ConfigContextInterface::NODE_DI_CONFIG));
    }

    /**
     * Create a container
     * 
     * @param FactoryInterface $factory
     * @param ConfigContextInterface $config
     * 
     * @return ContainerInterface
     */
    protected function createContainer(FactoryInterface $factory, ConfigContextInterface $config): ContainerInterface
    {
        $container = $factory->create(ContainerInterface::class);

        $container
            ->attach(ContainerInterface::class, $container)
            ->attach(FactoryInterface::class, $factory->withContainer($container))
            ->attach(ConfigContextInterface::class, $config);

        return $container;
    }

    /**
     * Validate the configuration
     * 
     * @param ConfigContextInterface $config
     * 
     * @return void
     */
    protected function validateConfig(ConfigContextInterface $config): void
    {
        if (!$config->has('app')) {
            throw new \RuntimeException('Bootloader: Missing app config: "/app"');
        }

        if (!$config->has(ConfigContextInterface::NODE_DI_CONFIG)) {
            throw new \RuntimeException('Bootloader: Missing DI config: "/di"');
        }

        if (!$config->has('base-path')) {
            throw new \RuntimeException('Bootloader: Missing base config: "/base-path"');
        }
    }
}
