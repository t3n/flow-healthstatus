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

use Yeebase\Readiness\Task\TaskInterface;

interface TestInterface extends TaskInterface
{
    /**
     * @return bool
     */
    public function test(): bool;
}
