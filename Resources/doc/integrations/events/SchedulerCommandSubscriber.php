<?php

namespace App\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Dukecity\CommandSchedulerBundle\Event\SchedulerCommandCreatedEvent;
use Dukecity\CommandSchedulerBundle\Event\SchedulerCommandPostExecutionEvent;
use Dukecity\CommandSchedulerBundle\Event\SchedulerCommandFailedEvent;
use Dukecity\CommandSchedulerBundle\Event\SchedulerCommandPreExecutionEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * Example to Subscribe to Events from the Dukecity\CommandSchedulerBundle
 */
final class SchedulerCommandSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private EntityManagerInterface $em;
    private ContainerInterface $container;
    private NotifierInterface|null $notifier;

    /**
     * SchedulerCommandSubscriber constructor.
     *
     * @param ContainerInterface     $container
     * @param LoggerInterface        $logger
     * @param EntityManagerInterface $em
     * @param NotifierInterface|null $notifier
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger, EntityManagerInterface $em, NotifierInterface|null $notifier = null)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->em = $em;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SchedulerCommandCreatedEvent::class => ['onScheduledCommandCreated',    -10],
            SchedulerCommandFailedEvent::class => ['onScheduledCommandFailed',     20],
            SchedulerCommandPreExecutionEvent::class => ['onScheduledCommandPreExecution',   10],
            SchedulerCommandPostExecutionEvent::class => ['onScheduledCommandPostExecution',   30],
        ];
    }

    public function onScheduledCommandCreated(SchedulerCommandCreatedEvent $event)
    {
        $this->logger->info('CustomScheduledCommandCreated', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandFailed(SchedulerCommandFailedEvent $event)
    {
        $this->logger->warning('CustomSchedulerCommandFailedEvent', ['details' => $event->getMessage()]);
    }

    public function onScheduledCommandPreExecution(SchedulerCommandPreExecutionEvent $event)
    {
        $this->logger->info('CustomScheduledCommandPreExecution', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandPostExecution(SchedulerCommandPostExecutionEvent $event)
    {
        $this->logger->info('CustomScheduledCommandPostExecution', [
            'name' => $event->getCommand()->getName(),
            "result" => $event->getResult(),
            "runtime" => $event->getRuntime()->format('%S seconds'),
        ]);
    }
}
