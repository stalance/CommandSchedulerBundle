<?php

namespace Dukecity\CommandSchedulerBundle;

use Dukecity\CommandSchedulerBundle\DependencyInjection\DukecityCommandSchedulerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DukecityCommandSchedulerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     *
     * @return DukecityCommandSchedulerExtension
     */
    public function getContainerExtension()
    {
        $class = $this->getContainerExtensionClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensionClass()
    {
        return DukecityCommandSchedulerExtension::class;
    }
}
