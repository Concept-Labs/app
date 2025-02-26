<?php
namespace Concept\App;

use Concept\App\Event\AppCreated;
use Concept\Config\ConfigInterface;
use Concept\EventDispatcher\EventBusInterface;
use Concept\Singularity\Config\Composer\ComposerContext;
use Concept\Singularity\Config\Composer\ComposerContextInterface;
use Concept\Singularity\Event\CreateServiceAfter;
use Concept\Singularity\Event\CreateServiceBefore;
use Concept\Singularity\Event\SingularityEventInterface;
use Concept\Singularity\Singularity;
use Concept\Singularity\SingularityInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class Bootstrap
{
    private ?string $rootPath = null;
    private ?SingularityInterface $container = null;
    private ?ComposerContextInterface $composerContext = null;
    private ?AppInterface $app = null;

    public function __construct(string $rootPath, string|array|ConfigInterface $initialConfig)
    {
        $this->init($rootPath, $initialConfig);
    }

    public function createApplication(): AppInterface
    {
        return $this->getContainer()
            ->get(AppFactoryInterface::class)
                ->setConfig(
                    $this->getContainer()->getConfigManager()->getConfig()->from('app')
                )
                ->create();
        ;

        return $this;
    }

    protected function getRootPath(?string $path = null): string
    {
        return $this->rootPath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    protected function getContainer(): SingularityInterface
    {
        return $this->container ??= new Singularity();
    }

    protected function init(string $rootPath, string|array|ConfigInterface $initialConfig): static
    {
        $this->rootPath = $rootPath;

        $this->initErrorHandler();
        $this->initExceptionHandler();

        $this->initConfig($initialConfig);
        $this->initEventBus();

        return $this;
    }

    protected function initConfig(string|array|ConfigInterface $initialConfig): static
    {
        $initialConfig = match (true) {
            is_string($initialConfig) && file_exists($initialConfig) => $this->getRootPath($initialConfig),
            default => $initialConfig
        };

        $this->getContainer()->configure(
            $this->getComposerConfig()->getConfig(),
            $initialConfig
        );

        return $this;
    }

    

    protected function getComposerConfig(): ComposerContextInterface
    {
        return $this->composerContext ??= (new ComposerContext())->build();
    }

   

    protected function initErrorHandler(): static
    {
        // set_error_handler(
        //     fn($errno, $errstr, $errfile, $errline) => throw new \ErrorException($errstr, 0, $errno, $errfile, $errline)
        // );

        return $this;
    }

    protected function initExceptionHandler(): static
    {
        //set_exception_handler(
            //fn($e) => $this->getContainer()->get(AppInterface::class)->handleException($e)
        //);

        return $this;
    }

    protected function initEventBus(): static
    {
        $eventBus = $this->getContainer()->get(EventBusInterface::class, [], [get_class($this)]);

        $eventBus->register(
            AppCreated::class,
                static function($event) { echo('AppCreated event fired'); }
            );
        
        $eventBus->register(
            CreateServiceBefore::class,
                static function($event) { echo('<br>CreateServiceBefore event fired:'.$event->getContext()->get('context')->getServiceId()); }
            );
        $eventBus->register(
            CreateServiceAfter::class,
                static function($event) { echo('<br>CreateServiceAfter event fired:'.$event->getContext()->get('context')->getServiceClass()); }
            );
        $eventBus->register(
            SingularityEventInterface::class,
                static function($event) { echo('<br>Singularity all events fired:'.$event->getContext()->get('context')->getServiceClass()); }
            );

        $listenerProvider = $this->getContainer()->get(ListenerProviderInterface::class, [], [get_class($this)]);
        $eventDispatcher = $this->getContainer()->get(EventDispatcherInterface::class, [$listenerProvider], [get_class($this)]);

        $this->getContainer()->register(EventDispatcherInterface::class, $eventDispatcher);

        return $this;
    }
}
