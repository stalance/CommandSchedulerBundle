<?php

namespace JMose\CommandSchedulerBundle\Event;

use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;

class SchedulerCommandCreatedEvent
{
    /**
     * List of failed commands.
     *
     * @param ScheduledCommand $command
     */
    public function __construct(private ScheduledCommand $command)
    {
    }

    /**
     * @return ScheduledCommand
     */
    public function getCommand(): ScheduledCommand
    {
        return $this->command;
    }
}
