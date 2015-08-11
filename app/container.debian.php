<?php

use Dock\Doctor;
use Dock\Installer\DNS;
use Dock\Installer\Docker;
use Dock\Installer\System;
use Dock\Installer\TaskProvider;
use Dock\System\Linux\ShellCreator;

$container['system.shell_creator'] = function() {
    return new ShellCreator();
};

$container['installer.dns.dnsdock'] = function($c) {
    return new Dns\Linux\DnsDock($c['console.user_interaction'], $c['process.interactive_runner']);
};
$container['installer.dns.docker_routing'] = function($c) {
    return new Dns\Linux\Debian\DockerRouting($c['console.user_interaction'], $c['process.interactive_runner']);
};

$container['installer.task_provider'] = function ($c) {
    return new TaskProvider([
        new System\Linux\Debian\NoSudo($c['console.user_interaction'], $c['process.interactive_runner']),
        new System\Linux\Docker($c['console.user_interaction'], $c['process.interactive_runner']),
        new System\Linux\DockerCompose($c['console.user_interaction'], $c['process.interactive_runner']),
        $c['installer.dns.dnsdock'],
        $c['installer.dns.docker_routing'],
    ]);
};

$container['doctor.tasks'] = function($c) {
    return [
        new Doctor\Task(
            $c['process.interactive_runner'],
            "Check docker version",
            "docker -v",
            "It seems docker is not installed.",
            "Install docker with `dock-cli docker:install`",
            $c['installer.docker']
        ),
        new Doctor\Task(
            $c['process.interactive_runner'],
            "Check docker info",
            "docker info",
            "It seems docker daemon is not running.",
            "Start it with `sudo service docker start`",
            $c['installer.docker']
        ),
        new Doctor\Task(
            $c['process.interactive_runner'],
            "Ping docker network interface",
            "ping -c1 172.17.42.1",
            "We can't reach docker.",
            "Install and start docker by running: `dock-cli docker:install`",
            $c['installer.docker']
        ),
    ];
};
