<?php
namespace JMose\CommandSchedulerBundle;

final class AppEvents
{
    public const SCHEDULER_COMMAND_FAILED   = 'app.scheduler-commands.failed';
    public const SCHEDULER_COMMAND_EXECUTED = 'app.scheduler-commands.exceuted';
    public const SCHEDULER_COMMAND_CREATED  = 'app.scheduler-commands.created';
}
