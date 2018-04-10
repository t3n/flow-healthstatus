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

abstract class AbstractTask implements TaskInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Result
     */
    protected $result;


    /**
     * @param string $name
     * @param array $options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
        $this->result = new Result();
        $this->validateOptions($options);
    }

    /**
     * @param array $options
     */
    protected function validateOptions(array $options)
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSuccessLabel(): string
    {
        return 'Done';
    }

    /**
     * @return string
     */
    public function getErrorLabel(): string
    {
        $error = $this->result->getFirstError();
        return $error ? $error->getMessage() : 'Failed';
    }

    /**
     * @return string
     */
    public function getNoticeLabel(): string
    {
        return $this->result->getFirstNotice()->getMessage();
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return Result
     */
    public function getResult(): Result
    {
        return $this->result;
    }
}
