<?php

declare(strict_types=1);

use JMose\CommandSchedulerBundle\Command\ExecuteCommand;
use JMose\CommandSchedulerBundle\Command\MonitorCommand;
use JMose\CommandSchedulerBundle\Command\RemoveCommand;
use JMose\CommandSchedulerBundle\Command\StartSchedulerCommand;
use JMose\CommandSchedulerBundle\Command\StopSchedulerCommand;
use JMose\CommandSchedulerBundle\Command\UnlockCommand;
use JMose\CommandSchedulerBundle\Controller\DetailController;
use JMose\CommandSchedulerBundle\Controller\ListController;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use JMose\CommandSchedulerBundle\EventSubscriber\SchedulerCommandSubscriber;
use JMose\CommandSchedulerBundle\Form\Type\CommandChoiceType;
use JMose\CommandSchedulerBundle\Service\CommandParser;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(DetailController::class)
        ->public()
        ->autowire()
        ->call('setManagerName', ['%jmose_command_scheduler.doctrine_manager%'])
        ->call('setTranslator', [service('translator')])
        ->tag('container.service_subscriber')
        ->tag('controller.service_arguments');

    $services->set(ListController::class)
        ->public()
        ->autowire()
        ->call('setManagerName', ['%jmose_command_scheduler.doctrine_manager%'])
        ->call('setTranslator', [service('translator')])
        ->call('setLockTimeout', ['%jmose_command_scheduler.lock_timeout%'])
        ->call('setLogger', [service('logger')])
        ->tag('container.service_subscriber')
        ->tag('controller.service_arguments');

    $services->set(CommandParser::class)
        ->args(
            [
                service('kernel'),
                '%jmose_command_scheduler.excluded_command_namespaces%',
                '%jmose_command_scheduler.included_command_namespaces%',
            ]
        );

    $services->set(CommandChoiceType::class)
        ->autowire()
        ->tag('form.type', ['alias' => 'command_choice']);

    $services->set(ExecuteCommand::class)
        ->args(
            [
                service('event_dispatcher'),
                service('doctrine'),
                '%jmose_command_scheduler.doctrine_manager%',
                '%jmose_command_scheduler.log_path%',
            ]
        )
        ->tag('console.command');

    $services->set(MonitorCommand::class)
        ->args(
            [
                service('event_dispatcher'),
                service('doctrine'),
                '%jmose_command_scheduler.doctrine_manager%',
                '%jmose_command_scheduler.lock_timeout%',
                '%jmose_command_scheduler.monitor_mail%',
                '%jmose_command_scheduler.monitor_mail_subject%',
                '%jmose_command_scheduler.send_ok%',
            ]
        )
        ->tag('console.command');

    $services->set(UnlockCommand::class)
        ->args(
            [
                service('doctrine'),
                '%jmose_command_scheduler.doctrine_manager%',
                '%jmose_command_scheduler.lock_timeout%',
            ]
        )
        ->tag('console.command');

    $services->set(RemoveCommand::class)
        ->args(
            [
                service('doctrine'),
                '%jmose_command_scheduler.doctrine_manager%'
            ]
        )
        ->tag('console.command');

    $services->set(StartSchedulerCommand::class)
        ->tag('console.command');

    $services->set(StopSchedulerCommand::class)
        ->tag('console.command');

    $services->set(ScheduledCommand::class)
        ->tag('controller.service_arguments');

    $services->set(SchedulerCommandSubscriber::class)
        ->args(
            [
                service('service_container'),
                service('logger'),
                service('doctrine.orm.default_entity_manager'),
                service('notifier'),
                '%jmose_command_scheduler.monitor_mail%',
                '%jmose_command_scheduler.monitor_mail_subject%',
            ]
        )
        ->tag('kernel.event_subscriber');
};
