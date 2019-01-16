<?php

declare(strict_types=1);

namespace Yeebase\Readiness\Task;

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
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\Log\SystemLoggerInterface;
use Yeebase\Readiness\Service\EelRuntime;

class EelTask extends AbstractTask
{
    /**
     * @Flow\Inject
     *
     * @var EelRuntime
     */
    protected $runtime;

    /**
     * @Flow\Inject
     *
     * @var SystemLoggerInterface
     */
    protected $systemLoggerInterface;

    /**
     * @param mixed[] $options
     *
     * @throws InvalidConfigurationException
     */
    protected function validateOptions(array $options): void
    {
        if (! isset($options['expression'])) {
            throw new InvalidConfigurationException('"expression" not set', 1502701561);
        }
    }

    public function run(): void
    {
        $this->runtime->evaluate($this->options['expression']);
    }
}
