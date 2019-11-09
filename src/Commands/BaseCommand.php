<?php

namespace MScharl\Changelog\Commands;

use MScharl\Changelog\Configuration\ChangelogConfiguration;
use Symfony\Component\Console\Command\Command;

abstract class BaseCommand extends Command
{
    const SETTINGS_FILE_NAME = '.changelog.yaml';

    /**
     * @var ChangelogConfiguration
     */
    protected $config;

    /**
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        $this->config = new ChangelogConfiguration(getcwd() . '/' . self::SETTINGS_FILE_NAME);
        parent::__construct($name);
    }
}
