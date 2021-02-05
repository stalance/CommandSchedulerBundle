<?php

namespace JMose\CommandSchedulerBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use JMose\CommandSchedulerBundle\AppEvents;
use JMose\CommandSchedulerBundle\Event\SchedulerCommandCreatedEvent;
use JMose\CommandSchedulerBundle\Event\SchedulerCommandExecutedEvent;
use JMose\CommandSchedulerBundle\Event\SchedulerCommandFailedEvent;
use JMose\CommandSchedulerBundle\Notification\CronMonitorNotification;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;

final class SchedulerCommandSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private EntityManagerInterface $em;
    private ContainerInterface $container;
    private NotifierInterface $notifier;

    /**
     * TODO check if parameters needed
     * SchedulerCommandSubscriber constructor.
     *
     * @param ContainerInterface     $container
     * @param LoggerInterface        $logger
     * @param EntityManagerInterface $em
     * @param NotifierInterface      $notifier
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger, EntityManagerInterface $em, NotifierInterface $notifier, private array $monitor_mail=[], private string $monitor_mail_subject="CronMonitor:")
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->em = $em;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SchedulerCommandCreatedEvent::class => ['onScheduledCommandCreated',    -10],
            SchedulerCommandFailedEvent::class => ['onScheduledCommandFailed',     20],
            SchedulerCommandExecutedEvent::class => ['onScheduledCommandExecuted',   0],
        ];
    }

    // TODO check if useful
    public function onScheduledCommandCreated(SchedulerCommandCreatedEvent $event)
    {
        $this->logger->info('ScheduledCommandCreated', ['name' => $event->getCommand()->getName()]);
    }

    public function onScheduledCommandFailed(SchedulerCommandFailedEvent $event)
    {
        #...$this->notifier->getAdminRecipients()
        $recipients = [];
        foreach ($this->monitor_mail as $mailadress)
        {
            $recipients[] = new Recipient($mailadress);
        }
        $this->notifier->send(new CronMonitorNotification($event->getFailedCommands(), $this->monitor_mail_subject), ...$recipients);

        #$this->logger->warning('SchedulerCommandFailedEvent', ['details' => $event->getMessage()]);
    }

    public function onScheduledCommandExecuted(SchedulerCommandExecutedEvent $event)
    {
        $this->logger->info('ScheduledCommandExecuted', ['name' => $event->getCommand()->getName()]);
    }
}
