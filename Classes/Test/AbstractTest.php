<?php
namespace Yeebase\Readiness\Test;

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
use Yeebase\Readiness\Task\AbstractTask;

abstract class AbstractTest extends AbstractTask implements TestInterface
{
    /**
     * @return void
     */
    final public function run()
    {
        $passed = $this->test();
        if (!$passed) {
            $this->result->addError(new Error($this->getErrorLabel()));
        }
    }

    /**
     * @return string
     */
    public function getSuccessLabel(): string
    {
        return 'Ready';
    }
}
