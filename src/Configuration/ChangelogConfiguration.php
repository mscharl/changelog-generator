<?php

namespace MScharl\Changelog\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ChangelogConfiguration implements ConfigurationInterface
{
    const DEFAULTS = [
        'unreleased_dir' => 'changelog/unreleased',
        'changes_file' => 'CHANGELOG.md',
        'entry_template' => __DIR__ . '/../../templates/entry.md.twig',
    ];

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
            [
                self::DEFAULTS,
                $configYaml,
            ]
        );
    }

    /**
     * The changes file located relative to the project dir (cwd).
     *
     * @return string
     */
    public function getChangesFilePath(): string
    {
        return $this->config['changes_file'];
    }

    /**
     * The directory of unreleased entries located relative to the project dir (cwd).
     *
     * @return string
     */
    public function getUnreleasedDirPath(): string
    {
        return $this->config['unreleased_dir'];
    }

    /**
     * The template for a single changelog entry..
     *
     * @return string
     */
    public function getEntryTemplate(): string
    {
        $value = $this->config['entry_template'];
        if (file_exists($value)) {
            return file_get_contents($value);
        }

        return $value;
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
                ->scalarNode('unreleased_dir')->end()
                ->scalarNode('changes_file')->end()
                ->scalarNode('entry_template')->end()
            ->end()
        ;
        // @formatter:on

        return $treeBuilder;
    }
}
