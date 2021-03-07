<?php

namespace Dukecity\CommandSchedulerBundle;

use Dukecity\CommandSchedulerBundle\DependencyInjection\DukecityCommandSchedulerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class DukecityCommandSchedulerBundle.
 */
class DukecityCommandSchedulerBundle extends Bundle
{
    /**
     * {@inheritdoc}
     *
     * @return DukecityCommandSchedulerExtension
     */
    public function getContainerExtension(): DukecityCommandSchedulerExtension
    {
        $class = $this->getContainerExtensionClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensionClass(): string
    {
        return DukecityCommandSchedulerExtension::class;
    }
}
