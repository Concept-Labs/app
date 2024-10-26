<?php
namespace Concept\App;

interface ConfigInterface extends \Concept\Config\ConfigInterface
{

    const CONFIG_APP_NODE = 'app';

    /**
     * Get the app configuration.
     *
     * @return array
     */
    public function getAppConfig(): array;
    
}
