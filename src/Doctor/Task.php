<?php

namespace Dock\Doctor;

use Dock\Installer\Installable;
use Dock\IO\ProcessRunner;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Task
{
    /**
     * @var ProcessRunner
     */
    protected $processRunner;

    /**
     * @param ProcessRunner $processRunner
     * @param string $taskName Name of the doctor task
     * @param string $command Command to check whether a problem exists
     * @param string $problem Problem description
     * @param string $suggestedSolution Suggested solution
     * @param Installable $installable Task to fix the problem
     */
    public function __construct(
        $processRunner,
        $taskName,
        $command,
        $problem,
        $suggestedSolution,
        Installable $fixer)
    {
        $this->processRunner = $processRunner;
        $this->taskName = $taskName;
        $this->command = $command;
        $this->problem = $problem;
        $this->suggestedSolution = $suggestedSolution;
        $this->fixer = $fixer;
    }

    /**
     * @param bool $dryRun Try to fix the problem?
     */
    public function run($dryRun)
    {
        if (! $this->testCommand()) {
            if ($dryRun) {
                throw new CommandFailedException("Command {$this->command} failed."
                    . " {$this->problem}\n{$this->suggestedSolution}");
            } else {
                $this->fixer->run();
                $this->run(true);
            }
        }
    }

    /**
     * @return bool Did the command succeed?
     */
    protected function testCommand()
    {
        try {
            $this->processRunner->run($this->command);
        } catch (ProcessFailedException $e) {
            return false;
        }

        return true;
    }
}
