<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('jmose_command_scheduler_list', '/command-scheduler/list')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\ListController::indexAction']);

    $routingConfigurator->add('jmose_command_scheduler_monitor', '/command-scheduler/monitor')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\ApiController::monitorAction']);

    $routingConfigurator->add('jmose_command_scheduler_api_list', '/command-scheduler/api/list')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\ApiController::listAction']);

    $routingConfigurator->add('jmose_command_scheduler_action_toggle', '/command-scheduler/action/toggle/{id}')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\ListController::toggleAction']);

    $routingConfigurator->add('jmose_command_scheduler_action_remove', '/command-scheduler/action/remove/{id}')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\ListController::removeAction']);

    $routingConfigurator->add('jmose_command_scheduler_action_execute', '/command-scheduler/action/execute/{id}')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\ListController::executeAction']);

    $routingConfigurator->add('jmose_command_scheduler_action_unlock', '/command-scheduler/action/unlock/{id}')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\ListController::unlockAction']);

    $routingConfigurator->add('jmose_command_scheduler_detail_index', '/command-scheduler/detail/view/')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\DetailController::indexAction']);

    $routingConfigurator->add('jmose_command_scheduler_detail_edit', '/command-scheduler/detail/edit/{id}')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\DetailController::edit']);

    $routingConfigurator->add('jmose_command_scheduler_detail_new', '/command-scheduler/detail/edit')
        ->defaults(['_controller' => 'JMose\CommandSchedulerBundle\Controller\DetailController::edit']);
};
