<?php

namespace Dukecity\CommandSchedulerBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Dukecity\CommandSchedulerBundle\Entity\ScheduledCommand;
use Dukecity\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListControllerTest.
 */
class ListControllerTest extends WebTestCase
{
    protected $databaseTool;
    private $client;
    private $em;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->followRedirects(true);

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->databaseTool = $this->client->getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * Test list display.
     */
    public function testIndex()
    {
        // DataFixtures create 4 records
        $this->databaseTool->loadFixtures([LoadScheduledCommandData::class]);

        $crawler = $this->client->request('GET', '/command-scheduler/list');
        $this->assertEquals(5, $crawler->filter('a[href^="/command-scheduler/action/toggle/"]')->count());
    }

    /**
     * Test permanent deletion on command.
     */
    public function testRemove()
    {
        // DataFixtures create 4 records
        $this->databaseTool->loadFixtures([LoadScheduledCommandData::class]);

        //toggle off
        $crawler = $this->client->request('GET', '/command-scheduler/action/remove/1');

        $this->assertEquals(4, $crawler->filter('a[href^="/command-scheduler/action/toggle/"]')->count());
    }

    /**
     * Test On/Off toggle on list.
     */
    public function testToggle()
    {
        // DataFixtures create 4 records
        $this->databaseTool->loadFixtures([LoadScheduledCommandData::class]);

        //toggle off
        $crawler = $this->client->request('GET', '/command-scheduler/action/toggle/1');
        $this->assertEquals(1, $crawler->filter('a[href="/command-scheduler/action/toggle/1"] > span[class="text-danger glyphicon glyphicon-off"]')->count());

        //toggle on
        $crawler = $this->client->request('GET', '/command-scheduler/action/toggle/1');
        $this->assertEquals(0, $crawler->filter('a[href="/command-scheduler/action/toggle/1"] > span[class="text-danger glyphicon glyphicon-off"]')->count());
    }

    /**
     * Test Execute now button on list.
     */
    public function testExecute()
    {
        // DataFixtures create 4 records
        $this->databaseTool->loadFixtures([LoadScheduledCommandData::class]);

        //call execute now button
        $crawler = $this->client->request('GET', '/command-scheduler/action/execute/1');
        $this->assertEquals(1, $crawler->filter('a[data-href="/command-scheduler/action/execute/1"] > span[class="text-muted glyphicon glyphicon-play"]')->count());
    }

    /**
     * Test unlock button on list.
     */
    public function testUnlock()
    {
        // DataFixtures create 4 records
        $this->databaseTool->loadFixtures([LoadScheduledCommandData::class]);

        // One command is locked in fixture (2)
        $crawler = $this->client->request('GET', '/command-scheduler/list');
        $this->assertEquals(1, $crawler->filter('a[data-href="/command-scheduler/action/unlock/2"]')->count());

        $crawler = $this->client->request('GET', '/command-scheduler/action/unlock/2');
        $this->assertEquals(0, $crawler->filter('a[data-href="/command-scheduler/action/unlock/2"]')->count());
    }
}
