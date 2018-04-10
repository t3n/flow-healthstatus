<?php
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

use Neos\Error\Messages\Result;

interface TaskInterface
{
    /**
     * @return void
     */
    public function run();

    /**
     * @return Result
     */
    public function getResult(): Result;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getSuccessLabel(): string;

    /**
     * @return string
     */
    public function getErrorLabel(): string;

    /**
     * @return string
     */
    public function getNoticeLabel(): string;

    /**
     * @return array
     */
    public function getOptions(): array;
}
