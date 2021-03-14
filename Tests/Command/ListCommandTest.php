<?php

namespace Dukecity\CommandSchedulerBundle\Tests\Command;

use Dukecity\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Dukecity\CommandSchedulerBundle\Command\ListCommand;

/**
 * Class ListCommandTest.
 */
class ListCommandTest extends AbstractCommandTest
{
    /**
     * Test scheduler:list
     */
    public function testListCommand()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $output = $this->executeCommand(ListCommand::class, [])->getDisplay();

        // all
        $this->assertStringContainsString('one', $output);
        $this->assertStringContainsString('two', $output);
        $this->assertStringContainsString('three', $output);
        $this->assertStringContainsString('four', $output);
    }
}
