<?php

namespace Dock\Installer\System\Linux;

use Dock\Installer\InstallerTask;
use Dock\IO\ProcessRunner;
use Dock\IO\UserInteraction;
use SRIO\ChainOfResponsibility\DependentChainProcessInterface;

class NoSudo extends InstallerTask implements DependentChainProcessInterface
{
    /**
     * @var ProcessRunner
     */
    private $processRunner;
    /**
     * @var UserInteraction
     */
    private $userInteraction;

    /**
     * @param UserInteraction $userInteraction
     * @param ProcessRunner $processRunner
     */
    public function __construct(UserInteraction $userInteraction, ProcessRunner $processRunner)
    {
        $this->userInteraction = $userInteraction;
        $this->processRunner = $processRunner;
    }

    /**
     * {@inheritdoc}
     */
    public function dependsOn()
    {
        return ['docker'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'noSudo';
    }

    public function run()
    {
        if (! $this->isCurrentUserInDockerGroup()) {
            $this->userInteraction->writeTitle('Making docker work without sudo');
            $this->processRunner->run('sudo usermod -a -G docker $USER');
        }
    }

    private function isCurrentUserInDockerGroup()
    {
        return $this->processRunner->run('groups | grep docker', false)->isSuccessful();
    }
}
