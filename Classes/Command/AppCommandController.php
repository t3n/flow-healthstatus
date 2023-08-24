<?php

declare(strict_types=1);

namespace t3n\Flow\HealthStatus\Command;

/**
 * This file is part of the t3n.Flow.HealthStatus package.
 *
 * (c) 2018 yeebase media GmbH
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Error\Messages\Error;
use Neos\Error\Messages\Result;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Log\ThrowableStorageInterface;
use t3n\Flow\HealthStatus\Service\LivenessTestRunner;
use t3n\Flow\HealthStatus\Service\ReadyTaskRunner;
use t3n\Flow\HealthStatus\Service\TestRunner;
use t3n\Flow\HealthStatus\Task\TaskInterface;
use t3n\Flow\HealthStatus\Test\TestInterface;

/**
 * @Flow\Scope("singleton")
 */
class AppCommandController extends CommandController
{
    /**
     * @Flow\Inject
     *
     * @var ThrowableStorageInterface
     */
    protected $throwableStorage;

    protected function runReadyTasks(): Result
    {
        $taskRunner = new ReadyTaskRunner();
        $taskRunner->onBeforeTask(function (TaskInterface $task): void {
            $this->output('Executing %s... ', [$task->getName()]);
        });
        $taskRunner->onTaskResult(function (TaskInterface $task, Result $result): void {
            if ($result->hasErrors()) {
                $this->outputFormatted('<error>%s</error>', [$task->getErrorLabel()]);
            } elseif ($result->hasNotices()) {
                $this->outputFormatted('<comment>%s</comment>', [$task->getNoticeLabel()]);
            } else {
                $this->outputFormatted('<success>%s</success>', [$task->getSuccessLabel()]);
            }
        });

        try {
            return $taskRunner->run();
        } catch (\Throwable $exception) {
            $this->output->output('<error>%s</error>', [$exception->getMessage()]);
            $this->throwableStorage->logThrowable($exception);
            $result = new Result();
            $result->addError(new Error('Chain failed'));
            return $result;
        }
    }

    protected function runTests(): Result
    {
        $testRunner = new TestRunner();

        $testRunner->onBeforeTest(function (TestInterface $test): void {
            $this->output->output('Testing %s... ', [$test->getName()]);
        });
        $testRunner->onTestResult(function (TestInterface $test, Result $result): void {
            if ($result->hasErrors()) {
                $this->output->outputLine('<error>%s</error>', [$test->getErrorLabel()]);
            } elseif ($result->hasNotices()) {
                $this->output->outputLine('<commment>%s</commment>', [$test->getNoticeLabel()]);
            } else {
                $this->output->outputLine('<info>%s</info>', [$test->getSuccessLabel()]);
            }
        });

        return $testRunner->run();
    }

    protected function runLivenessTests(): Result
    {
        $testRunner = new LivenessTestRunner();
        $testRunner->onBeforeTest(function (TestInterface $test): void {
            $this->output->output('Testing %s... ', [$test->getName()]);
        });
        $testRunner->onTestResult(function (TestInterface $test, Result $result): void {
            if ($result->hasErrors()) {
                $this->output->outputLine('<error>%s</error>', [$test->getErrorLabel()]);
            } elseif ($result->hasNotices()) {
                $this->output->outputLine('<commment>%s</commment>', [$test->getNoticeLabel()]);
            } else {
                $this->output->outputLine('<info>%s</info>', [$test->getSuccessLabel()]);
            }
        });

        return $testRunner->run();
    }

    /**
     * Checks the readiness of the application
     */
    public function isReadyCommand(): void
    {
        $this->outputLine();
        $this->outputLine('<info>Running tests...</info>');
        $this->outputLine();
        $testResult = $this->runTests();
        $this->outputLine();
        if (! $testResult->hasErrors()) {
            $this->outputLine();
            $this->outputLine('<info>All checks passed, executing ready tasks...</info>');
            $this->outputLine();
            $readyResult = $this->runReadyTasks();
            $this->outputLine();

            if (! $readyResult->hasErrors()) {
                $this->outputLine('<success>Application is ready</success>');
                $this->outputLine();
                $this->outputLine();
                $this->quit(0);
            } else {
                $this->outputLine('<error>Application did not pass all ready tasks and is not ready</error>');
                $this->outputLine();
                $this->outputLine();
                $this->quit(1);
            }
        } else {
            $this->outputLine();
            $this->outputLine('<error>Application did not pass all checks and is not ready</error>');
            $this->outputLine();
            $this->quit(1);
        }
    }

    /**
     * Checks the liveness of the application
     */
    public function isAliveCommand(): void
    {
        $this->outputLine();
        $this->outputLine('<info>Running liveness tests...</info>');
        $this->outputLine();
        $testResult = $this->runLivenessTests();
        $this->outputLine();

        if (! $testResult->hasErrors()) {
            $this->outputLine('<success>Application is alive</success>');
            $this->outputLine();
            $this->quit(0);
        } else {
            $this->outputLine('<error>Application is dead</error>');
            $this->outputLine();
            $this->quit(1);
        }
    }
}
