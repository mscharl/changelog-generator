<?php

namespace MScharl\Changelog\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ChangelogConfiguration implements ConfigurationInterface
{
    /**
     * Holds the config tree.
     * @var array
     */
    protected $config;

    public function __construct(string $configFile)
    {
        $configYaml = Yaml::parseFile($configFile);

        $configProcessor = new Processor();

        $this->config = $configProcessor->processConfiguration(
            $this,
            [$configYaml]
        );
    }

    /**
     * The changes file located relative to the project dir (cwd).
     *
     * @return string
     */
    public function getChangesFilePath(): string
    {
        return $this->config['paths']['changes_file'];
    }

    /**
     * The directory of unreleased entries located relative to the project dir (cwd).
     *
     * @return string
     */
    public function getUnreleasedDirPath(): string
    {
        return $this->config['paths']['unreleased_dir'];
    }

    /**
     * The template for a single changelog entry..
     *
     * @return string
     */
    public function getEntryTemplate(): string
    {
        return $this->config['entry_template'];
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('changelog');

        // @formatter:off
        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('paths')
                    ->children()
                        ->scalarNode('unreleased_dir')->end()
                        ->scalarNode('changes_file')->end()
                    ->end()
                ->end()
                ->scalarNode('entry_template')->end()
            ->end()
        ;
        // @formatter:on

        return $treeBuilder;
    }
}
