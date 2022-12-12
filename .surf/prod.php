<?php

namespace Cyclear;

use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask;
use TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask;

$node = new Node('cyclear5.cyclear.nl');
$node->setHostname($node->getName());
$node->setOption('username', 'flat');
$node->setDeploymentPath('/home/flat/cyclear5');
$node->setOption('phpBinaryPathAndFilename', '/usr/bin/php');

/** @var Deployment $deployment */
$deployment->setWorkspacesBasePath(__DIR__ . '/builds');

$application = new BaseApplication('cyclear');
$application->addNode($node);
$application->setOption('repositoryUrl', 'git@github.com:ErikTrapman/cyclear.git');
$application->setOption('branch', 'release/5.x');
$application->setOption('composerCommandPath', 'composer');
$application->setOption('baseUrl', 'http://cyclear5.cyclear.nl');
$deployment->addApplication($application)->onInitialize(
    function () use ($deployment, $application) {
        $deployment->getWorkflow()
            ->beforeStage('transfer', WebOpcacheResetCreateScriptTask::class, $application)
            ->afterStage('switch', WebOpcacheResetExecuteTask::class, $application);
    }
);
