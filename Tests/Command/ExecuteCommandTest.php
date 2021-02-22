<?php /** @noinspection ALL */

namespace JMose\CommandSchedulerBundle\Tests\Command;

use JMose\CommandSchedulerBundle\Command\ExecuteCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ExecuteCommandTest.
 */
class ExecuteCommandTest extends AbstractCommandTest
{
    /**
     * Test scheduler:execute without option.
     */
    public function testExecute()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $output = $this->executeCommand(ExecuteCommand::class, [])->getDisplay();

        $this->assertStringStartsWith('Start : Execute all scheduled command', $output);
        $this->assertMatchesRegularExpression('/debug:container should be executed/', $output);
        $this->assertMatchesRegularExpression('/Execute : debug:container --help/', $output);
        $this->assertMatchesRegularExpression('/Immediately execution asked for : debug:router/', $output);
        $this->assertMatchesRegularExpression('/Execute : debug:router/', $output);

        $output = $this->executeCommand(ExecuteCommand::class)->getDisplay();
        $this->assertMatchesRegularExpression('/Nothing to do/', $output);
    }

    /**
     * Test scheduler:execute without option.
     */
    public function testExecuteWithNoOutput()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $output = $this->executeCommand(ExecuteCommand::class, ['--no-output' => true])->getDisplay();

        $this->assertEquals('', $output);

        $output = $this->executeCommand(ExecuteCommand::class)->getDisplay();
        $this->assertMatchesRegularExpression('/Nothing to do/', $output);
    }

    /**
     * Test scheduler:execute with --dump option.
     */
    public function testExecuteWithDump()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $output = $this->executeCommand(ExecuteCommand::class, ['--dump' => true])->getDisplay();

        $this->assertStringStartsWith('Start : Dump all scheduled command', $output);
        $this->assertMatchesRegularExpression('/Command debug:container should be executed/', $output);
        $this->assertMatchesRegularExpression('/Immediately execution asked for : debug:router/', $output);
    }
}
