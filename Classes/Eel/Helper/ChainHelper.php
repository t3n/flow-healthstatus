<?php

declare(strict_types=1);

namespace t3n\Flow\HealthStatus\Eel\Helper;

/**
 * This file is part of the t3n.Flow.HealthStatus package.
 *
 * (c) 2018 yeebase media GmbH
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Error\Messages\Error;
use Neos\Flow\Annotations as Flow;
use t3n\Flow\HealthStatus\Service\EelRuntime;

class ChainHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     *
     * @var EelRuntime
     */
    protected $runtime;

    public function isValid(): bool
    {
        $result = $this->runtime->getChainResult();
        return ! $result->hasErrors();
    }

    public function isInvalid(): bool
    {
        $result = $this->runtime->getChainResult();
        return $result->hasErrors();
    }

    public function getCombinedErrorMessages(): string
    {
        $result = $this->runtime->getChainResult();
        $messages = array_map(static function (Error $error) {
            return $error->getMessage();
        }, $result->getErrors());

        return implode(PHP_EOL, $messages);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return $this->runtime->isTaskContext();
    }
}
