<?php

namespace JMose\CommandSchedulerBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use JMose\CommandSchedulerBundle\Entity\ScheduledCommand;
use JMose\CommandSchedulerBundle\Fixtures\ORM\LoadScheduledCommandData;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListControllerTest.
 */
class ListControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;
    private EntityManager $em;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Test list display.
     */
    public function testIndex()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $crawler = $this->client->request('GET', '/command-scheduler/list');
        $this->assertEquals(4, $crawler->filter('a[href^="/command-scheduler/action/toggle/"]')->count());
    }

    /**
     * Test permanent deletion on command.
     */
    public function testRemove()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        //toggle off
        $crawler = $this->client->request('GET', '/command-scheduler/action/remove/1');

        $this->assertEquals(3, $crawler->filter('a[href^="/command-scheduler/action/toggle/"]')->count());
    }

    /**
     * Test On/Off toggle on list.
     */
    public function testToggle()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        //toggle off
        $crawler = $this->client->request('GET', '/command-scheduler/action/toggle/1');
        $this->assertEquals(1, $crawler->filter(
            'a[href="/command-scheduler/action/toggle/1"] > span[class="text-danger glyphicon glyphicon-off"]')
            ->count());

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
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        //call execute now button
        $crawler = $this->client->request('GET', '/command-scheduler/action/execute/1');

        #
        $this->assertStringContainsString('Command -one- will be executed during the next', $this->client->getResponse()->getContent());

        //$this->assertEquals(1, $crawler->filter('a[data-href="/command-scheduler/action/execute/1"] > span[class="text-muted glyphicon glyphicon-play"]')->count());
        //$this->assertEquals(1, $crawler->filterXPath('//div[contains("Command will be executed during the next")')->count());
    }

    /**
     * Test unlock button on list.
     */
    public function testUnlock()
    {
        // DataFixtures create 4 records
        $this->loadFixtures([LoadScheduledCommandData::class]);

        $this->client->followRedirects(true);

        // One command is locked in fixture (2)
        $crawler = $this->client->request('GET', '/command-scheduler/list');
        $this->assertEquals(1, $crawler->filter('a[href="/command-scheduler/action/unlock/2"]')->count());

        $crawler = $this->client->request('GET', '/command-scheduler/action/unlock/2');
        $this->assertEquals(0, $crawler->filter('a[href="/command-scheduler/action/unlock/2"]')->count());
    }
}
