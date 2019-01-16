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

use Neos\Error\Messages\Result;

interface TaskInterface
{
    public function run(): void;

    public function getResult(): Result;

    public function getName(): string;

    public function getSuccessLabel(): string;

    public function getErrorLabel(): string;

    public function getNoticeLabel(): string;

    /**
     * @return mixed[]
     */
    public function getOptions(): array;
}
