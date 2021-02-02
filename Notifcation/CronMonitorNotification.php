<?php

namespace JMose\CommandSchedulerBundle\Notification;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class CronMonitorNotification extends Notification implements EmailNotificationInterface
{
    private array $scheduledCommands;

    /**
     * CronMonitorNotification constructor.
     *
     * @param array $scheduledCommands
     */
    #[Pure]
    public function __construct(array $scheduledCommands)
    {
        $this->scheduledCommands = $scheduledCommands;

        parent::__construct('CronMonitor');
    }

    public function asChatMessage(RecipientInterface $recipient, string $transport = null): ?ChatMessage
    {
        return new ChatMessage('TEST 1');
    }

    /**
     * @param Recipient|EmailRecipientInterface $recipient
     * @param string|null                       $transport
     *
     * @return EmailMessage|null
     */
    public function asEmailMessage(Recipient | EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient, $transport);
        $message->getMessage()
        ->htmlTemplate('emails/comment_notification.html.twig')
        ->context(['scheduledCommand' => $this->scheduledCommands])
        ;

        return $message;
    }
}
