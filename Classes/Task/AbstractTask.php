<?php

declare(strict_types=1);

namespace t3n\Flow\HealthStatus\Task;

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

abstract class AbstractTask implements TaskInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var mixed[]
     */
    protected $options;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @param mixed[] $options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
        $this->result = new Result();
        $this->validateOptions($options);
    }

    /**
     * @param mixed[] $options
     */
    protected function validateOptions(array $options): void
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSuccessLabel(): string
    {
        return 'Done';
    }

    public function getErrorLabel(): string
    {
        $error = $this->result->getFirstError();
        return $error ? $error->getMessage() : 'Failed';
    }

    public function getNoticeLabel(): string
    {
        return $this->result->getFirstNotice()->getMessage();
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getResult(): Result
    {
        return $this->result;
    }
}
