<?php

namespace App\Tests\App;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * Register Bundles for test-configuration.
     *
     * @return array
     */
    #[Pure]
    public function registerBundles(): array
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new \JMose\CommandSchedulerBundle\JMoseCommandSchedulerBundle(),
            new \Liip\TestFixturesBundle\LiipTestFixturesBundle(),
            new \Symfony\Bundle\DebugBundle\DebugBundle(),
            //new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),
            //new \DAMA\DoctrineTestBundle\DAMADoctrineTestBundle::class => ['test' => true],
            //Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    #[Pure]
    public function getCacheDir(): string
    {
        return __DIR__.'/../../build/cache/'.$this->getEnvironment();
    }

    #[Pure]
    public function getLogDir(): string
    {
        return __DIR__.'/../../build/kernel_logs/'.$this->getEnvironment();
    }
}
