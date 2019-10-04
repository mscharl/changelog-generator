<?php

namespace MScharl\Changelog\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class EntryConfiguration implements ConfigurationInterface
{

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $entryName;

    public function __construct(string $entryFile)
    {
        $this->entryName = basename($entryFile, '.yaml');
        $entry = Yaml::parseFile($entryFile);

        $entryProcessor = new Processor();

        $this->config = $entryProcessor->processConfiguration(
            $this,
            [$entry]
        );
    }

    public function getData(): array
    {
        return $this->config;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder($this->entryName);

        $treeBuilder
            ->getRootNode()
            ->children()
            ->scalarNode('type')->end()
            ->scalarNode('title')->end()
            ->scalarNode('ticket_id')->end()
            ->scalarNode('ticket_url')->end()
            ->scalarNode('merge_request_id')->end()
            ->arrayNode('other')
            ->variablePrototype()->end()
            ->end();

        return $treeBuilder;
    }
}
