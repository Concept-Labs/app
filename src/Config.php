<?php
namespace Concept\App;

class Config extends \Concept\Config\Config implements ConfigInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAppConfig(): array
    {
        return $this->get(self::CONFIG_APP_NODE);
    }
}