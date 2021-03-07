<?php

namespace Dukecity\CommandSchedulerBundle\Tests\Command;

use Dukecity\CommandSchedulerBundle\Command\AddCommand;
use Dukecity\CommandSchedulerBundle\Entity\ScheduledCommand;
use Dukecity\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;

/**
 * Class AddCommandTest.
 */
class AddCommandTest extends AbstractCommandTest
{
    /**
     * Test scheduler:add with given command name.
     */
    public function testAdd()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        // Add command
        $output = $this->executeCommand(
            AddCommand::class,
            [
                'name' => 'myCommand',
                'cmd' => 'debug:router',
                'arguments' => '',
                'cronExpression' => '@daily',
                'priority' => 10,
                'logFile' => 'mycommand.log',
                'executeImmediately' => false,
                'disabled' => false,
            ]
        )->getDisplay();
        $this->assertStringContainsString('successfully', $output);

        // Check if in DB
        $cmd_check = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'myCommand']);
        self::assertIsScalar(10, $cmd_check->getPriority());
        //$this->assertInstanceOf($cmd_check, ScheduledCommand);

        // Fails now
        $output = $this->executeCommand(AddCommand::class, [
            'name' => 'myCommand',
            'cmd' => 'debug:router',
            'arguments' => '',
            'cronExpression' => '@daily',
        ])->getDisplay();
        $this->assertStringContainsString('Could not', $output);
    }
}
