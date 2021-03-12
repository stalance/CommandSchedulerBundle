<?php

namespace Dukecity\CommandSchedulerBundle\Service;

use Exception;
use JetBrains\PhpStorm\Pure;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class CommandParser
 *
 * @author Julien Guyon <julienguyon@hotmail.com>
 */
class CommandParser
{
    /**
     * CommandParser constructor.
     *
     * @param KernelInterface $kernel
     * @param array      $excludedNamespaces
     * @param array      $includedNamespaces
     */
    public function __construct(private KernelInterface $kernel,
        private array $excludedNamespaces = [],
        private array $includedNamespaces = [])
    {
        if (!$this->isNamespacingValid($excludedNamespaces, $includedNamespaces)) {
            throw new \InvalidArgumentException('Cannot combine excludedNamespaces with includedNamespaces');
        }
    }


    /**
     * There could be only whitelisting or blacklisting
     * @param array $excludedNamespaces
     * @param array $includedNamespaces
     * @return bool
     */
    #[Pure]
    public function isNamespacingValid(array $excludedNamespaces, array $includedNamespaces): bool
    {
        return !(
                count($excludedNamespaces) > 0 &&
                count($includedNamespaces) > 0
                );
    }

    /**
     * @param array $namespaces
     */
    public function setExcludedNamespaces(array $namespaces = [])
    {
        $this->excludedNamespaces = $namespaces;
    }

    /**
     * @param array $namespaces
     */
    public function setIncludedNamespaces(array $namespaces = [])
    {
        $this->includedNamespaces = $namespaces;
    }

    /**
     * Get all available commands from symfony
     *
     * @param string $format txt|xml|json|md
     * @param string $env test|dev|prod
     * @return string|array
     * @throws Exception
     */
    public function getAvailableCommands(string $format="xml", string $env="prod"): string|array
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(
            [
                'command' => 'list',
                '--format' => $format,
                '--env' => $env,
            ]
        );

        try {
            $output = new StreamOutput(fopen('php://memory', 'w+'));
            $application->run($input, $output);

            rewind($output->getStream());

            if($format=="xml")
            {return stream_get_contents($output->getStream());}

            if($format=="json")
            {return json_decode(
                stream_get_contents($output->getStream()),
                true,
                512,
                JSON_THROW_ON_ERROR
            );}

        } catch (\Throwable) {
            throw new Exception('Listing of commands could not be read');
        }
    }

    /**
     * Execute the console command "list" with XML output to have all available command.
     *
     * @return array[]
     *
     * @throws Exception
     */
    public function getCommands(string $env="prod"): array
    {
        if (!$this->isNamespacingValid($this->excludedNamespaces, $this->includedNamespaces)) {
            throw new \InvalidArgumentException('Cannot combine excludedNamespaces with includedNamespaces');
        }

        return $this->extractCommandsFromXML($this->getAvailableCommands("xml", $env));
    }


    /**
     * @param array $commands
     * @return array
     * @throws Exception
     */
    public function getCommandDetails(array $commands): array
    {
        $availableCommands = $this->getAvailableCommands("json", "prod");
        $result = [];
        #$command->getDefinition();

        foreach ($availableCommands["commands"] as $command)
        {
            #var_dump($command);
            if(in_array($command["name"], $commands))
            {
                $result[$command["name"]] = $command;
            }
        }

        if(count($result)==0)
        {throw new CommandNotFoundException('Cannot find a command with this names');}

        return $result;
    }


    /**
     * Extract an array of available Symfony commands from the XML output.
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

                # Blacklisting and Whitelisting
                if ((count($this->excludedNamespaces) > 0 && in_array($namespaceId, $this->excludedNamespaces))
                ||
                (count($this->includedNamespaces) > 0 && !in_array($namespaceId, $this->includedNamespaces))
                ) {
                    continue;
                }

                # Add Command Name to array
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
