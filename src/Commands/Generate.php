<?php

namespace MScharl\Changelog\Commands;

use SplFileObject;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use Tightenco\Collect\Support\Collection;

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

    private function getChanges()
    {
        $path = $this->config->getUnreleasedDirPath();

        return Collection::make(scandir($path))
            ->filter(
                function ($filename) {
                    return $filename !== '.' && $filename !== '..';
                }
            )
            ->map(
                function ($filename) use ($path) {
                    return Yaml::parseFile($path.'/'.$filename);
                }
            )
            ->map(
                function ($data) {
                    $data['type'] = mb_convert_case($data['type'], MB_CASE_UPPER);
                    $other = $data['other'];
                    unset($data['other']);

                    $keys = array_map(
                        function ($key) {
                            return '{'.$key.'}';
                        },
                        array_keys($data)
                    );

                    return "\n".implode(
                            "\n",
                            array_merge(
                                [
                                    str_replace($keys, array_values($data), $this->config->getEntryTemplate()),
                                ],
                                array_map(
                                    function ($otherLine) {
                                        return '    - '.$otherLine;
                                    },
                                    array_values($other)
                                )
                            )
                        )."\n";
                }
            )->values()->toArray();
    }
}
