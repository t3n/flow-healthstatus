<?php

declare(strict_types=1);

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

use Neos\Eel\InterpretedEvaluator;
use Neos\Eel\Utility as EelUtility;
use Neos\Error\Messages\Result;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class EelRuntime
{
    public const CONTEXT_TEST = 'test';
    public const CONTEXT_TASK = 'task';

    /**
     * @Flow\InjectConfiguration("defaultContext")
     *
     * @var mixed[]
     */
    protected $eelContext;

    /**
     * @var mixed[]
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

    public function setTaskContext(string $taskContext): void
    {
        $this->taskContext = $taskContext;
    }

    public function isTaskContext(): bool
    {
        return $this->taskContext === self::CONTEXT_TASK;
    }

    public function isTestContext(): bool
    {
        return $this->taskContext === self::CONTEXT_TEST;
    }

    public function setChainResult(Result $chainResult): void
    {
        $this->chainResult = $chainResult;
    }

    public function getChainResult(): Result
    {
        return $this->chainResult;
    }

    /**
     * @return mixed[]
     */
    protected function getDefaultContextVariables(): array
    {
        if ($this->defaultContextVariables === null) {
            $this->defaultContextVariables = [];
            $this->defaultContextVariables = EelUtility::getDefaultContextVariables($this->eelContext);
        }
        return $this->defaultContextVariables;
    }

    /**
     * @return mixed
     */
    public function evaluate(string $expression)
    {
        $evaluator = new InterpretedEvaluator();
        return EelUtility::evaluateEelExpression($expression, $evaluator, $this->getDefaultContextVariables());
    }
}
