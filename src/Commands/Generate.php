<?php

namespace MScharl\Changelog\Commands;

use Exception;
use MScharl\Changelog\Configuration\EntryConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tightenco\Collect\Support\Collection;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class Generate extends BaseCommand
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Adds all unreleased changes to the changelog file.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Load the current changes file.
        $file = file_get_contents($this->config->getChangesFilePath());

        // Get the exact changes headline.
        $unreleasedMatch = [];
        preg_match('/#+\s*unreleased\s*/im', $file, $unreleasedMatch);

        // Split the log and store the head, changes headline and previous changes.
        $unreleasedHeadline = $unreleasedMatch[0];
        list($head, $previousChanges) = preg_split('/#+\s*unreleased\s*/im', $file);

        if (preg_match('/^#/', $previousChanges)) {
            $previousChanges = "\n\n" . $previousChanges;
        } else {
            $previousChanges = "\n" . $previousChanges;
        }

        $changes = $this->getChanges();

        if (count($changes) > 0) {
            // Concatenate the parts of the changelog.
            $changelog = $head . $unreleasedHeadline . implode("\n", $changes) . $previousChanges;

            file_put_contents($this->config->getChangesFilePath(), $changelog);
        }

        $io->block(
            'The Changes file was updated with ' . count($changes) . ' new entries.',
            '✔︎',
            'fg=black;bg=green',
            ' ',
            true
        );
    }

    /**
     * Load all unreleased entries and compile them to usable lines.
     *
     * @return array
     *
     * @throws Exception
     */
    private function getChanges()
    {
        // Create the Twig renderer.
        $loader = new ArrayLoader(['entry' => $this->config->getEntryTemplate()]);
        $twig = new Environment($loader);

        // Fetch all change entries.
        $path = $this->config->getUnreleasedDirPath();
        if (!is_dir($path)) {
            throw new Exception(sprintf('"%s" is not a directory.', getcwd() . '/' . $path));
        }

        $entries = Collection::make(scandir($path));

        return $entries
            ->map(function (string $filename) use ($path) {
                return $path . '/' . $filename;
            })
            ->filter(function (string $filePath) {
                return !preg_match('/^\./', basename($filePath)) && is_file($filePath);
            })
            ->map(function (string $entryFile) use ($twig) {
                $entry = new EntryConfiguration($entryFile);

                // Remove the processed file.
                unlink($entryFile);

                return trim(
                    $twig->render('entry', $entry->getData())
                );
            })
            ->values()
            ->toArray();
    }
}
