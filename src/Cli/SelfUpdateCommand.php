<?php

namespace Dock\Cli;

use Herrera\Phar\Update\Manager;
use Symfony\Component\Console\Input\InputOption;
use Herrera\Json\Exception\FileException;
use Herrera\Phar\Update\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    const MANIFEST_FILE = 'http://inviqa.github.io/dock-cli/manifest.json';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription('Updates Dock CLI to the latest version')
            ->addOption('major', null, InputOption::VALUE_NONE, 'Allow major version update')
            ->addOption('disallow-unstable', null, InputOption::VALUE_NONE, 'Disallow unstable versions')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Looking for updates...');

        try {
            $manager = new Manager(Manifest::loadFile(self::MANIFEST_FILE));
        } catch (FileException $e) {
            $output->writeln('<error>Unable to search for updates</error>');

            return 1;
        }

        $currentVersion = $this->getApplication()->getVersion();
        $allowMajor = $input->getOption('major');
        $allowUnstableVersions = !$input->getOption('disallow-unstable');

        if ($manager->update($currentVersion, $allowMajor, $allowUnstableVersions)) {
            $output->writeln('<info>Updated to latest version</info>');
        } else {
            $output->writeln('<comment>Already up-to-date</comment>');
        }

        return 0;
    }
}
