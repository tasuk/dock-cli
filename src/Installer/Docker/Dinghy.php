<?php

namespace Dock\Installer\Docker;

use Dock\Dinghy\Boot2DockerCli;
use Dock\Dinghy\DinghyCli;
use Dock\Installer\InstallContext;
use Dock\Installer\InstallerTask;
use Dock\IO\ProcessRunner;
use Dock\IO\UserInteraction;
use SRIO\ChainOfResponsibility\DependentChainProcessInterface;
use Symfony\Component\Process\Process;

class Dinghy extends InstallerTask implements DependentChainProcessInterface
{
    /**
     * @var UserInteraction
     */
    private $userInteraction;
    /**
     * @var ProcessRunner
     */
    private $processRunner;

    /**
     * {@inheritdoc}
     */
    public function run(InstallContext $context)
    {
        $this->userInteraction = $context->getUserInteraction();
        $this->processRunner = $context->getProcessRunner();

        $boot2docker = new Boot2DockerCli($this->processRunner);
        if ($boot2docker->isInstalled()) {
            $this->userInteraction->writeTitle('Boot2Docker seems to be installed, removing it.');

            if (!$boot2docker->uninstall()) {
                $this->userInteraction->writeTitle('Something went wrong while uninstalling Boot2Docker, continuing anyway.');
            } else {
                $this->userInteraction->writeTitle('Successfully uninstalled boot2docker');
            }
        }

        $dinghy = new DinghyCli($context->getProcessRunner());
        if (!$dinghy->isInstalled()) {
            $this->userInteraction->writeTitle('Installing Dinghy');
            $this->installDinghy();
            $this->userInteraction->writeTitle('Successfully installed Dinghy');
        } else {
            $this->userInteraction->writeTitle('Dinghy already installed, skipping.');
        }

        $this->changeDinghyDnsResolverNamespace();

        $this->userInteraction->writeTitle('Starting up Dinghy');
        if (!$dinghy->isRunning()) {
            $dinghy->start();
            $this->userInteraction->writeTitle('Started Dinghy');
        } else {
            $this->userInteraction->writeTitle('Dinghy already started');
        }
    }

    private function installDinghy()
    {
        $this->processRunner->run(new Process('brew install https://github.com/codekitchen/dinghy/raw/latest/dinghy.rb'));
    }

    private function changeDinghyDnsResolverNamespace()
    {
        $process = new Process('dinghy version');
        $this->processRunner->run($process);
        $dinghyVersionOutput = $process->getOutput();
        $dinghyVersion = substr(trim($dinghyVersionOutput), strlen('Dinghy '));
        $dnsMasqConfiguration = '/usr/local/Cellar/dinghy/'.$dinghyVersion.'/cli/dinghy/dnsmasq.rb';

        $process = new Process('sed -i \'\' \'s/docker/zzz-dinghy/\' '.$dnsMasqConfiguration);
        $this->processRunner->run($process);
    }

    /**
     * {@inheritdoc}
     */
    public function dependsOn()
    {
        return ['homebrew', 'vagrant', 'virtualbox'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'dinghy';
    }
}
