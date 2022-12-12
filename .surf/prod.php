<?php

namespace Cyclear;

use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask;
use TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask;
use TYPO3\Surf\Task\ShellTask;

//$node = new Node('cloud-04.veggatron.nl'); ENV_VAR from cli
//$node = new Node('test.cyclear.nl'); ENV_VAR from cli
$node = new Node('cyclear5.cyclear.nl');
$node->setHostname($node->getName());
$node->setOption('username', 'flat'); // ENV_VAR from cli
$node->setDeploymentPath('/home/flat/cyclear5'); // ENV_VAR from cli
$node->setOption('phpBinaryPathAndFilename', '/usr/bin/php');

/** @var Deployment $deployment */
$deployment->setWorkspacesBasePath(__DIR__ . '/builds');

$application = new BaseApplication('cyclear');
$application->addNode($node);
$application->setOption('repositoryUrl', 'git@github.com:ErikTrapman/cyclear.git');
//$application->setOption('branch', 'release/5.x');
$application->setOption('branch', 'feature/deploy');
$application->setOption('composerCommandPath', 'composer');
$application->setOption('baseUrl', 'http://cyclear5.cyclear.nl');
$deployment->addApplication($application);

$deployment->onInitialize(
    function () use ($deployment, $application) {
        $workflow = $deployment->getWorkflow();

        $workflow->defineTask('CopyEnvFileTask', ShellTask::class, [
            'command' => [
                'cp {sharedPath}/.env.local.php {releasePath}/.env.local.php',
            ],
        ]);

        $workflow
            ->beforeStage('transfer', WebOpcacheResetCreateScriptTask::class, $application)
            ->afterStage('transfer', 'CopyEnvFileTask', $application)
            ->afterStage('switch', WebOpcacheResetExecuteTask::class, $application);
    }
);
