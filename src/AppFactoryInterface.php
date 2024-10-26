<?php
namespace Concept\App;

use Concept\Config\ConfigurableInterface;

interface AppFactoryInterface extends ConfigurableInterface
{
    /**
     * Create an app instance
     * 
     * @return AppInterface
     */
    public function create(): AppInterface;

}
    