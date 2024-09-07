<?php
namespace Concept\Http;

use Psr\Container\ContainerInterface;
use Concept\Config\ConfigInterface;
use Concept\Factory\FactoryInterface;
use Concept\Http\App\HttpAppInterface;

class Bootloader    
{
    const BOOTLOADER_CONFIG_FILE = __DIR__ . '/etc/bootloader.json';

    static protected ?Bootloader $instance = null;

    protected ?ConfigInterface $config = null;
    protected ?ContainerInterface $container = null;
    protected ?FactoryInterface $factory = null;
    protected ?HttpAppInterface $app = null;

    public function __construct()
    {
        static::$instance = $this;
        $this->init();
    }

    static public function getInstance(): Bootloader
    {
        return static::$instance;
    }

    public function init()
    {
        $this->container = $this->createContainer();
        $this->config = $this->createConfig();
        $this->factory = $this->createFactory($this->config, $this->container);
        $this->app = $this->createApp();

        return $this;
    }

    static public function getApp(): HttpAppInterface
    {
        return static::getInstance()->app;
    }

    static public function getConfig(): ConfigInterface
    {
        return static::getInstance()->config;
    }

    static public function getContainer(): ContainerInterface
    {
        return static::getInstance()->container;
    }

    static public function getFactory(): FactoryInterface
    {
        return static::getInstance()->factory;
    }

    protected function createApp()
    {

        $app = (
                $this->getFactory()
                ->withServiceId(HttpAppInterface::class)
                ->create()
        )
            ->withConfig($this->getConfig())
            ->withContainer($this->getContainer())
            ->withFactory($this->getFactory())
        ;

        return $app;
    }

    protected function createContainer()
    {
        $container = new \Concept\Container\Container();
        return $container;
    }

    protected function createConfig()
    {
        $config = new \Concept\Config\Config();
        foreach (glob(static::BOOTLOADER_CONFIG_FILE) as $file) {
            $config->merge(json_decode(file_get_contents($file), true));
        }
        
        return $config;
    }

    protected function createFactory(ConfigInterface $config, ?ContainerInterface $container = null)
    {
        $factory = (new \Concept\Di\Factory\DiFactory())
            ->withConfig($config)
            ->withContainer($container);

        return $factory;
    }

    
}