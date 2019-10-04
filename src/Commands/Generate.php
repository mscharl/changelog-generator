<?php

namespace MScharl\Changelog\Commands;

use MScharl\Changelog\Configuration\EntryConfiguration;
use SplFileObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tightenco\Collect\Support\Collection;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class Generate extends BaseCommand
{

    /**
     * @var SymfonyStyle
     */
    protected $io;

    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Adds all unreleased changes to the changelog file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $file = new SplFileObject($this->config->getChangesFilePath());
        $changelog = [];

        $lastHeadline = null;
        $unreleasedHeadline = null;
        while (!$file->eof()) {
            $line = $file->fgets();

            if (preg_match('/^#+/', $line)) {
                $lastHeadline = trim($line);
                $changelog[$lastHeadline] = [];
                if (preg_match('/unreleased/i', $lastHeadline)) {
                    $unreleasedHeadline = $lastHeadline;
                }
            } else {
                $changelog[$lastHeadline][] = trim($line);
            }
        }

        $changelog[$unreleasedHeadline] = array_filter(
            $changelog[$unreleasedHeadline],
            function ($value) {
                return !empty(trim($value));
            }
        );

        $changelog[$unreleasedHeadline] = array_merge($this->getChanges(), $changelog[$unreleasedHeadline]);

        $changelog = array_map(
            function (array $lines) {
                return implode("\n", $lines);
            },
            $changelog
        );

        $output = array_reduce(
            array_keys($changelog),
            function (string $carry, string $headline) use ($changelog) {
                $lines = $changelog[$headline];

                return implode("\n", [$carry, $headline, $lines]);
            },
            ''
        );
        var_dump($output);
    }

    /**
     * Load all unreleased entries and compile them to usable lines.
     *
     * @return array
     */
    private function getChanges()
    {
        // Create the Twig renderer.
        $loader = new ArrayLoader(['entry' => $this->config->getEntryTemplate()]);
        $twig = new Environment($loader);

        // Fetch all change entries.
        $path = $this->config->getUnreleasedDirPath();
        $entries = Collection::make(scandir($path));

        return $entries
            ->map(function (string $filename) use ($path) {
                return $path . '/' . $filename;
            })
            ->filter(function (string $filePath) {
                return is_file($filePath);
            })
            ->map(function (string $entryFile) use ($twig) {
                $entry = new EntryConfiguration($entryFile);

                return trim(
                    $twig->render('entry', $entry->getData())
                );
            })
            ->values()
            ->toArray();
    }
}
