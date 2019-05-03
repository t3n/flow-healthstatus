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

use Neos\Error\Messages\Result;
use Neos\Flow\Annotations as Flow;

class ReadyTaskRunner extends AbstractTaskRunner
{
    /**
     * @Flow\InjectConfiguration("readyChain")
     *
     * @var mixed[]
     */
    protected $chain;

    /**
     * @Flow\InjectConfiguration("defaultReadyTaskCondition")
     *
     * @var string
     */
    protected $defaultCondition;

    /**
     * @var string
     */
    protected $context = EelRuntime::CONTEXT_TASK;

    /**
     * @param mixed[] $configuration
     */
    protected function runTask(string $name, array $configuration): Result
    {
        if (isset($configuration['lockName'])) {
            $cacheOverride = isset($configuration['cacheName']) ? sprintf('.withCache("%s")', $configuration['cacheName']) : '';
            $configuration['condition'] = sprintf('${Lock%s.isUnset("%s")}', $cacheOverride, $configuration['lockName']);
            $configuration['afterInvocation'] = sprintf('${Lock%s.set("%s")}', $cacheOverride, $configuration['lockName']);
        }

        return parent::runTask($name, $configuration);
    }
}
