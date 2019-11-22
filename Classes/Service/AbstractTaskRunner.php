<?php

declare(strict_types=1);

namespace t3n\Flow\HealthStatus\Service;

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
use Neos\Error\Messages\Notice;
use Neos\Error\Messages\Result;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\PositionalArraySorter;
use t3n\Flow\HealthStatus\Task\TaskInterface;

abstract class AbstractTaskRunner
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Closure
     */
    protected $onTaskResultClosure;

    /**
     * @var \Closure
     */
    protected $onBeforeTaskClosure;

    /**
     * @var string
     */
    protected $defaultTaskClassName = 't3n\Flow\HealthStatus\Task\%sTask';


    /**
     * @var string
     */
    protected $defaultCondition;

    /**
     * @var mixed[]
     */
    protected $chain = [];

    /**
     * @var string
     */
    protected $context;

    /**
     * @var Result
     */
    protected $chainResult;

    /**
     * @Flow\Inject
     *
     * @var EelRuntime
     */
    protected $runtime;

    /**
     * @var bool
     */
    protected $stopOnFail = false;

    public function injectObjectManager(ObjectManagerInterface $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    public function __construct()
    {
        $this->onTaskResultClosure = static function (): void {
        };
        $this->onBeforeTaskClosure = static function (): void {
        };
    }

    /**
     * @return $this
     */
    public function onTaskResult(\Closure $onTaskResultClosure)
    {
        $this->onTaskResultClosure = $onTaskResultClosure;
        return $this;
    }

    /**
     * @return $this
     */
    public function onBeforeTask(\Closure $onBeforeTaskClosure)
    {
        $this->onBeforeTaskClosure = $onBeforeTaskClosure;
        return $this;
    }

    protected function resolveTaskClassName(string $objectName): string
    {
        if ($this->objectManager->isRegistered($objectName)) {
            return $objectName;
        }

        return sprintf($this->defaultTaskClassName, ucfirst($objectName));
    }

    /**
     * @param mixed[] $configuration
     */
    protected function shouldSkipTask(TaskInterface $task, array $configuration): bool
    {
        return ! $this->runtime->evaluate($configuration['condition'] ?? $this->defaultCondition);
    }

    /**
     * @param mixed[] $configuration
     */
    protected function afterTaskInvocation(TaskInterface $task, array $configuration): void
    {
        if (isset($configuration['afterInvocation'])) {
            $this->runtime->evaluate($configuration['afterInvocation']);
        }
    }

    /**
     * @param mixed[] $configuration
     *
     * @throws InvalidConfigurationException
     */
    protected function runTask(string $name, array $configuration): Result
    {
        $implementationClassName = $configuration[$this->context];
        $className = $this->resolveTaskClassName($implementationClassName);

        $task = new $className($name, $configuration['options'] ?? []);

        if (! ($task instanceof TaskInterface)) {
            $message = sprintf('%s does not implement \t3n\Flow\HealthStatus\Task\TaskInterface', get_class($task));
            throw new InvalidConfigurationException($message, 1502699058);
        }

        $onBeforeTask = $this->onBeforeTaskClosure;
        $onBeforeTask($task);

        $result = $task->getResult();
        try {
            if ($this->shouldSkipTask($task, $configuration)) {
                $result->addNotice(new Notice('Skipped'));
            } else {
                $task->run();
                $this->afterTaskInvocation($task, $configuration);
            }
        } catch (\Throwable $exception) {
            $result->addError(new Error($exception->getMessage(), $exception->getCode()));
        }

        $onTaskResult = $this->onTaskResultClosure;
        $onTaskResult($task, $result);

        return $result;
    }

    public function run(): Result
    {
        $this->chainResult = new Result();
        $this->runtime->setTaskContext($this->context);
        $this->runtime->setChainResult($this->chainResult);

        $sorter = new PositionalArraySorter($this->chain);

        foreach ($sorter->toArray() as $key => $task) {
            $result = $this->runTask($task['name'] ?? ucfirst($key), $task);
            $this->chainResult->merge($result);
            if ($result->hasErrors() && $this->stopOnFail) {
                break;
            }
        }

        return $this->chainResult;
    }
}
