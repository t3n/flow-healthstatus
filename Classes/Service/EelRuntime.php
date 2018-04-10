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

use Neos\Error\Messages\Result;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\InterpretedEvaluator;
use Neos\Eel\Utility as EelUtility;

/**
 * @Flow\Scope("singleton")
 */
class EelRuntime
{
    const CONTEXT_TEST = 'test';
    const CONTEXT_TASK = 'task';

    /**
     * @Flow\InjectConfiguration("defaultContext")
     * @var array
     */
    protected $eelContext;

    /**
     * @var array
     */
    protected $defaultContextVariables;

    /**
     * @var string
     */
    protected $taskContext;

    /**
     * @var Result
     */
    protected $chainResult;

    /**
     * @param string $taskContext
     */
    public function setTaskContext(string $taskContext)
    {
        $this->taskContext = $taskContext;
    }

    /**
     * @return bool
     */
    public function isTaskContext(): bool
    {
        return $this->taskContext === self::CONTEXT_TASK;
    }

    /**
     * @return bool
     */
    public function isTestContext(): bool
    {
        return $this->taskContext === self::CONTEXT_TEST;
    }

    /**
     * @param Result $chainResult
     */
    public function setChainResult(Result $chainResult)
    {
        $this->chainResult = $chainResult;
    }

    /**
     * @return Result
     */
    public function getChainResult(): Result
    {
        return $this->chainResult;
    }

    /**
     * @return array
     */
    protected function getDefaultContextVariables()
    {
        if ($this->defaultContextVariables === null) {
            $this->defaultContextVariables = array();
            $this->defaultContextVariables = EelUtility::getDefaultContextVariables($this->eelContext);
        }
        return $this->defaultContextVariables;
    }

    /**
     * @param string $expression
     * @return mixed
     */
    public function evaluate(string $expression)
    {
        $evaluator = new InterpretedEvaluator();
        return EelUtility::evaluateEelExpression($expression, $evaluator, $this->getDefaultContextVariables());
    }
}
