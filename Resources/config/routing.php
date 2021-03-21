<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('dukecity_command_scheduler_list', '/command-scheduler/list')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\ListController::indexAction']);

    $routingConfigurator->add('dukecity_command_scheduler_monitor', '/command-scheduler/monitor')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\ListController::monitorAction']);

    $routingConfigurator->add('dukecity_command_scheduler_action_toggle', '/command-scheduler/action/toggle/{id}')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\ListController::toggleAction']);

    $routingConfigurator->add('dukecity_command_scheduler_action_remove', '/command-scheduler/action/remove/{id}')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\ListController::removeAction']);

    $routingConfigurator->add('dukecity_command_scheduler_action_execute', '/command-scheduler/action/execute/{id}')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\ListController::executeAction']);

    $routingConfigurator->add('dukecity_command_scheduler_action_unlock', '/command-scheduler/action/unlock/{id}')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\ListController::unlockAction']);

    $routingConfigurator->add('dukecity_command_scheduler_detail_index', '/command-scheduler/detail/view/')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\DetailController::indexAction']);

    $routingConfigurator->add('dukecity_command_scheduler_detail_edit', '/command-scheduler/detail/edit/{scheduledCommandId}')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\DetailController::initEditScheduledCommandAction']);

    $routingConfigurator->add('dukecity_command_scheduler_detail_new', '/command-scheduler/detail/new')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\DetailController::initNewScheduledCommandAction']);

    $routingConfigurator->add('dukecity_command_scheduler_detail_save', '/command-scheduler/detail/save')
        ->defaults(['_controller' => 'Dukecity\CommandSchedulerBundle\Controller\DetailController::saveAction']);
};
