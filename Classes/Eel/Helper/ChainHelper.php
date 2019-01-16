<?php

declare(strict_types=1);

namespace Yeebase\Readiness\Eel\Helper;

/**
 * This file is part of the Yeebase.XY package.
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
use Yeebase\Readiness\Service\EelRuntime;

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

    public function allowsCallOfMethod(string $methodName): bool
    {
        return $this->runtime->isTaskContext();
    }
}
