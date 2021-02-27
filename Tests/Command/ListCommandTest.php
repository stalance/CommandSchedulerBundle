<?php

namespace JMose\CommandSchedulerBundle\Tests\Command;

use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use JMose\CommandSchedulerBundle\Command\ListCommand;

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
