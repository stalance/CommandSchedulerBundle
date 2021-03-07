<?php

namespace Dukecity\CommandSchedulerBundle\Service;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class CommandChoiceList.
 *
 * @author Julien Guyon <julienguyon@hotmail.com>
 */
class CommandParser
{
    /**
     * CommandParser constructor.
     *
     * @param KernelInterface $kernel
     * @param array|null      $excludedNamespaces
     * @param array|null      $includedNamespaces
     */
    public function __construct(private KernelInterface $kernel,
        private array | null $excludedNamespaces = [],
        private array | null $includedNamespaces = [])
    {
        if (count($this->excludedNamespaces) > 0 && count($this->includedNamespaces) > 0) {
            throw new \InvalidArgumentException('Cannot combine excludedNamespaces with includedNamespaces');
        }
    }

    /**
     * Execute the console command "list" with XML output to have all available command.
     *
     * @return array[]
     *
     * @throws Exception
     */
    public function getCommands(): array
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'list',
                '--format' => 'xml',
            ]
        );


        try {
            $output = new StreamOutput(fopen('php://memory', 'w+'));
            $application->run($input, $output);

            rewind($output->getStream());

            return $this->extractCommandsFromXML(stream_get_contents($output->getStream()));
        } catch (\Throwable) {
            throw new Exception('Listing of commands could not be read');
        }
    }

    /**
     * Extract an array of available Symfony command from the XML output.
     *
     * @param string $xml
     *
     * @return array
     */
    private function extractCommandsFromXML(string $xml): array
    {
        if ('' === $xml) {
            return [];
        }

        $commandsList = [];

        try {
            $node = new \SimpleXMLElement($xml);

            foreach ($node->namespaces->namespace as $namespace) {
                $namespaceId = (string) $namespace->attributes()->id;

                if ((count($this->excludedNamespaces) > 0 && in_array($namespaceId, $this->excludedNamespaces))
                ||
                (count($this->includedNamespaces) > 0 && !in_array($namespaceId, $this->includedNamespaces))
                ) {
                    continue;
                }

                foreach ($namespace->command as $command) {
                    $commandsList[$namespaceId][(string) $command] = (string) $command;
                }
            }
        } catch (Exception) {
            // return an empty CommandList
            $commandsList = ['ERROR: please check php bin/console list --format=xml' => 'error'];
        }

        return $commandsList;
    }
}
