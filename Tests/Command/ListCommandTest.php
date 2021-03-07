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
        $this->assertMatchesRegularExpression('/one/', $output);
        $this->assertMatchesRegularExpression('/two/', $output);
        $this->assertMatchesRegularExpression('/three/', $output);
        $this->assertMatchesRegularExpression('/four/', $output);
    }
}
