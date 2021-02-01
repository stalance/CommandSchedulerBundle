<?php

namespace JMose\CommandSchedulerBundle\EventSubscriber;

use App\Notification\CronMonitorNotification;
use Doctrine\ORM\EntityManagerInterface;
use JMose\CommandSchedulerBundle\AppEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use JMose\CommandSchedulerBundle\Event\SchedulerCommandCreatedEvent;
use JMose\CommandSchedulerBundle\Event\SchedulerCommandExecutedEvent;
use JMose\CommandSchedulerBundle\Event\SchedulerCommandFailedEvent;

final class SchedulerCommandSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private EntityManagerInterface $em;
    private ContainerInterface $container;

    /**
     * TODO check if parameters needed
     * SchedulerCommandSubscriber constructor.
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AppEvents::SCHEDULER_COMMAND_CREATED => ['onScheduledCommandCreated',    -10],
            AppEvents::SCHEDULER_COMMAND_FAILED => ['onScheduledCommandFailed',     20],
            AppEvents::SCHEDULER_COMMAND_EXECUTED => ['onScheduledCommandExecuted',   0],
        ];
    }

    # TODO check if useful
    public function onScheduledCommandCreated(SchedulerCommandCreatedEvent $event)
    {
        $this->logger->info('ScheduledCommandCreated', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandFailed(SchedulerCommandFailedEvent $event)
    {
        #$this->notifier->send(new CronMonitorNotification($event->getFailedCommands()), ["chris@sky-scripts.de"]);

        $this->logger->info('SchedulerCommandFailedEvent', ['details' => $event->getMessage()]);
    }

    public function onScheduledCommandExecuted(SchedulerCommandExecutedEvent $event)
    {
        $this->logger->info('ScheduledCommandExecuted', ['name' => $event->getCommand()->getName()]);
    }
}
