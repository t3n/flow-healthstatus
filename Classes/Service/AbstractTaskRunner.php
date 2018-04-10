<?php
namespace Yeebase\Readiness\Service;

/**
 * This file is part of the Yeebase.XY package.
 *
 * (c) 2018 yeebase media GmbH
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Error\Messages\Error;
use Neos\Error\Messages\Notice;
use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Result;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Utility\PositionalArraySorter;
use Yeebase\Readiness\Task\TaskInterface;

/**
 *
 */
abstract class AbstractTaskRunner
{
    /**
     * @Flow\Inject
     * @var SystemLoggerInterface
     */
    protected $systemLogger;

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
    protected $defaultTaskClassName = 'Yeebase\Readiness\Task\%sTask';


    /**
     * @var string
     */
    protected $defaultCondition;

    /**
     * @var array
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
     * @var EelRuntime
     */
    protected $runtime;

    /**
     * @var bool
     */
    protected $stopOnFail = false;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     *
     */
    public function __construct()
    {
        $this->onTaskResultClosure = function () {
        };
        $this->onBeforeTaskClosure = function () {
        };
    }

    /**
     * @param \Closure $onTaskResultClosure
     * @return $this
     */
    public function onTaskResult(\Closure $onTaskResultClosure)
    {
        $this->onTaskResultClosure = $onTaskResultClosure;
        return $this;
    }

    /**
     * @param \Closure $onBeforeTaskClosure
     * @return $this
     */
    public function onBeforeTask(\Closure $onBeforeTaskClosure)
    {
        $this->onBeforeTaskClosure = $onBeforeTaskClosure;
        return $this;
    }

    /**
     * @param string $objectName
     * @return string
     */
    protected function resolveTaskClassName(string $objectName): string
    {
        if ($this->objectManager->isRegistered($objectName)) {
            return $objectName;
        }

        return sprintf($this->defaultTaskClassName, ucfirst($objectName));
    }

    /**
     * @param TaskInterface $task
     * @param array $configuration
     * @return bool
     */
    protected function shouldSkipTask(TaskInterface $task, array $configuration): bool
    {
        return !$this->runtime->evaluate($configuration['condition'] ?? $this->defaultCondition);
    }


    /**
     * @param TaskInterface $task
     * @param array $configuration
     */
    protected function afterTaskInvocation(TaskInterface $task, array $configuration)
    {
        if (isset($configuration['afterInvocation'])) {
            $this->runtime->evaluate($configuration['afterInvocation']);
        }
    }

    /**
     * @param string $name
     * @param array $configuration
     * @return Result
     * @throws InvalidConfigurationException
     */
    protected function runTask(string $name, array $configuration): Result
    {
        $implementationClassName = $configuration[$this->context];
        $className = $this->resolveTaskClassName($implementationClassName);

        $task = new $className($name, $configuration['options'] ?? []);

        if (!($task instanceof TaskInterface)) {
            throw new InvalidConfigurationException(sprintf('%s does not implement \Yeebase\Readiness\Task\TaskInterface', get_class($task)), 1502699058);
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
        } catch (\Exception $exception) {
            $result->addError(new Error($exception->getMessage(), $exception->getCode()));
            $this->systemLogger->logException($exception);
        }

        $onTaskResult = $this->onTaskResultClosure;
        $onTaskResult($task, $result);

        return $result;
    }

    /**
     * @return Result
     */
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
