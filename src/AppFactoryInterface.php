<?php
namespace Concept\App;

use Concept\Config\ConfigurableInterface;
use Concept\Singularity\Factory\ServiceFactoryInterface;

interface AppFactoryInterface extends ServiceFactoryInterface, ConfigurableInterface
{
    /**
     * Create an app instance
     * 
     * @return AppInterface
     */
    public function create(array $args = []): AppInterface;

}
    