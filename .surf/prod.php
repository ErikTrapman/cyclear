<?php

namespace Cyclear;

require_once dirname(__DIR__) . '/vendor/autoload_runtime.php';

use Symfony\Component\Dotenv\Dotenv;
use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Task\Composer\InstallTask;
use TYPO3\Surf\Task\LocalShellTask;
use TYPO3\Surf\Task\Package\GitTask;
use TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask;
use TYPO3\Surf\Task\Php\WebOpcacheResetExecuteTask;
use TYPO3\Surf\Task\ShellTask;

$env = new Dotenv();
$env->bootEnv(__DIR__ . '/../.env');

$node = new Node($_ENV['SURF_PROD_HOST']); // ENV_VAR from cli
$node->setHostname($node->getName());
$node->setOption('username', $_ENV['SURF_PROD_USERNAME']); // ENV_VAR from cli
$node->setDeploymentPath($_ENV['SURF_PROD_PATH']); // ENV_VAR from cli
$node->setOption('phpBinaryPathAndFilename', '/usr/bin/php');
$node->setOption('privateKeyFile', $_ENV['SURF_PROD_PRIVATE_KEY']);
$node->setOption('rsyncExcludes', ['var']);

/** @var Deployment $deployment */
$deployment->setWorkspacesBasePath(__DIR__ . '/builds');

$application = new BaseApplication('cyclear');
$application->addNode($node);
// $application->setOption('repositoryUrl', 'git@github.com:ErikTrapman/cyclear.git');
// $application->setOption('branch', 'release/5.x');
$application->setOption('composerCommandPath', 'composer');
$application->setOption('baseUrl', $_ENV['SURF_PROD_BASEURL']);
$deployment->addApplication($application);

$deployment->onInitialize(
    function () use ($deployment, $application) {
        $workflow = $deployment->getWorkflow();
        $workflow->removeTask(GitTask::class);

        $workflow->defineTask('Cleanup', LocalShellTask::class, [
            'command' => 'rm -rf {workspacePath}',
        ]);
        $workflow->addTask('Cleanup', 'initialize', $application);

        $workflow->defineTask('GitClone', LocalShellTask::class, [
            'command' => 'git clone --quiet . {workspacePath}',
        ]);
        $workflow->addTask('GitClone', 'package', $application);
        $workflow->defineTask('ComposerInstall', LocalShellTask::class, [
            'command' => 'cd {workspacePath} && composer install --prefer-dist --no-dev -o --no-scripts',
        ]);
        $workflow->addTask('ComposerInstall', 'package', $application);

        $workflow->defineTask('CopyEnvFileTask', ShellTask::class, [
            'command' => [
                'cp {sharedPath}/.env.local.dist {releasePath}/.env.local.php',
            ],
        ]);

        $workflow->defineTask('RemoteTasks', ShellTask::class, [
            'command' => [
                'cd {releasePath} && bin/console cache:clear',
                'cd {releasePath} && chmod 777 var/ -R',
                'cd {releasePath} && bin/console assets:install --relative public',
                'cd {releasePath} && bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration',
            ],
        ]);

        $workflow
            ->beforeStage('transfer', WebOpcacheResetCreateScriptTask::class, $application)
            ->afterStage('transfer', 'CopyEnvFileTask', $application)
            ->forStage('migrate', 'RemoteTasks')
            ->afterStage('switch', WebOpcacheResetExecuteTask::class, $application);
    }
);
