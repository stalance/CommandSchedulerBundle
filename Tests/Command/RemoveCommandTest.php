<?php

namespace JMose\CommandSchedulerBundle\Tests\Command;

use JMose\CommandSchedulerBundle\Command\RemoveCommand;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;

/**
 * Class RemoveCommandTest.
 */
class RemoveCommandTest extends AbstractCommandTest
{
    /**
     * Test scheduler:remove with given command name.
     */
    public function testRemove()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        // Remove command
        $output = $this->executeCommand(RemoveCommand::class, ['name' => 'two'])->getDisplay();
        $this->assertStringContainsString('successfully', $output);

        // Not in DB anymore
        $two = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'two']);
        $this->assertNull($two);

        // Fails now
        $output = $this->executeCommand(RemoveCommand::class, ['name' => 'two'])->getDisplay();
        $this->assertStringContainsString('Could not', $output);
    }
}
