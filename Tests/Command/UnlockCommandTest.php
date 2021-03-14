<?php

namespace Dukecity\CommandSchedulerBundle\Tests\Command;

use Doctrine\Persistence\Mapping\MappingException;
use Dukecity\CommandSchedulerBundle\Command\UnlockCommand;
use Dukecity\CommandSchedulerBundle\Entity\ScheduledCommand;
use Dukecity\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;

/**
 * Class UnlockCommandTest.
 */
class UnlockCommandTest extends AbstractCommandTest
{
    /**
     * Test scheduler:unlock without --all option.
     */
    public function testUnlockAll()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $output = $this->executeCommand(UnlockCommand::class, ['--all' => true])->getDisplay();

        $this->assertStringContainsString('two', $output);
        $this->assertStringNotContainsString('one', $output);
        $this->assertStringNotContainsString('three', $output);

        try {
            $this->em->clear();
        } catch (MappingException $e) {
            echo 'Error with Mapping '.$e->getMessage();
        }
        $two = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'two']);

        $this->assertFalse($two->isLocked());
    }

    /**
     * Test scheduler:unlock with given command name.
     */
    public function testUnlockByName()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        // One command is locked in fixture (2), another have a -1 return code as lastReturn (4)
        $output = $this->executeCommand(UnlockCommand::class, ['name' => 'two'])->getDisplay();

        $this->assertStringContainsString('two', $output);

        try {
            $this->em->clear();
        } catch (MappingException $e) {
            echo 'Error with Mapping '.$e->getMessage();
        }
        $two = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'two']);

        $this->assertFalse($two->isLocked());
    }

    /**
     * Test scheduler:unlock with given command name and timeout.
     */
    public function testUnlockByNameWithTimout()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        // One command is locked in fixture with last execution two days ago (2),
        // another have a -1 return code as lastReturn (4)
        $output = $this->executeCommand(
            UnlockCommand::class,
            ['name' => 'two', '--lock-timeout' => 3 * 24 * 60 * 60]
        )
            ->getDisplay();

        $this->assertStringContainsString('Skipping', $output);
        $this->assertStringContainsString('two', $output);

        try {
            $this->em->clear();
        } catch (MappingException $e) {
            echo 'Error with Mapping '.$e->getMessage();
        }
        $two = $this->em->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'two']);

        $this->assertTrue($two->isLocked());
    }
}
