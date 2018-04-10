<?php
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

use Neos\Error\Messages\Error;
use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Yeebase\Readiness\Service\EelRuntime;

class ChainHelper implements ProtectedContextAwareInterface
{

    /**
     * @Flow\Inject
     * @var EelRuntime
     */
    protected $runtime;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $result = $this->runtime->getChainResult();
        return !$result->hasErrors();
    }

    /**
     * @return bool
     */
    public function isInvalid(): bool
    {
        $result = $this->runtime->getChainResult();
        return $result->hasErrors();
    }

    /**
     * @return string
     */
    public function getCombinedErrorMessages(): string
    {
        $result = $this->runtime->getChainResult();
        $messages = array_map(function (Error $error) {
            return $error->getMessage();
        }, $result->getErrors());

        return implode(PHP_EOL, $messages);
    }

    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return $this->runtime->isTaskContext();
    }
}
