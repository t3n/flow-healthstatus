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

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class TestRunner extends AbstractTaskRunner
{
    /**
     * @Flow\InjectConfiguration("testChain")
     * @var array
     */
    protected $chain;

    /**
     * @Flow\InjectConfiguration("defaultTestCondition")
     * @var string
     */
    protected $defaultCondition;

    /**
     * @var string
     */
    protected $defaultTaskClassName = 'Yeebase\Readiness\Test\%sTest';

    /**
     * @var bool
     */
    protected $stopOnFail = true;

    /**
     * @var string
     */
    protected $context = EelRuntime::CONTEXT_TEST;

    /**
     * @param \Closure $onBeforeTest
     */
    public function onBeforeTest(\Closure $onBeforeTest)
    {
        $this->onBeforeTask($onBeforeTest);
    }

    /**
     * @param \Closure $onTestResult
     */
    public function onTestResult(\Closure $onTestResult)
    {
        $this->onTaskResult($onTestResult);
    }
}
