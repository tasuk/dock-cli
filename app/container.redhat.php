<?php

use Dock\Installer\DNS;
use Dock\Installer\Docker;
use Dock\Installer\System;
use Dock\Installer\TaskProvider;
use Dock\System\Linux\ShellCreator;

$container['system.shell_creator'] = function() {
    return new ShellCreator();
};

$container['installer.task_provider'] = function ($c) {
    return new TaskProvider([
        new System\Linux\RedHat\NoSudo($c['console.user_interaction'], $c['process.interactive_runner']),
        new System\Linux\Docker($c['console.user_interaction'], $c['process.interactive_runner']),
        new System\Linux\DockerCompose($c['console.user_interaction'], $c['process.interactive_runner']),
        new Dns\Linux\DnsDock($c['console.user_interaction'], $c['process.interactive_runner']),
        //new Dns\Linux\RedHat\DockerRouting($c['console.user_interaction'], $c['process.interactive_runner']),
    ]);
};
