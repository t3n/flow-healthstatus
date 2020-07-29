<?php

declare(strict_types=1);

namespace t3n\Flow\HealthStatus\Test;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\Core\Booting\Scripts;
use Neos\Flow\Utility\Environment;

/**
 * This file is part of the t3n.Flow.HealthStatus package.
 *
 * (c) 2018 yeebase media GmbH
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
class SubCommandTest extends AbstractTest
{
    /**
     * @Flow\Inject
     *
     * @var Environment
     */
    protected $environment;

    /**
     * @Flow\InjectConfiguration(package="Neos.Flow", path="core.phpBinaryPathAndFilename")
     *
     * @var string
     */
    protected $phpBinaryPathAndFilename;

    /**
     * @Flow\InjectConfiguration(package="Neos.Flow")
     *
     * @var string[][]
     */
    protected $flowSettings;

    /**
     * @param mixed[] $options
     *
     * @throws InvalidConfigurationException
     */
    protected function validateOptions(array $options): void
    {
        if (! isset($options['commandIdentifier'])) {
            throw new InvalidConfigurationException('The command identifier of the sub-command to execute must be provided', 1596028979);
        }
    }

    /**
     * @throws \Neos\Flow\Core\Booting\Exception\SubProcessException
     */
    public function test(): bool
    {
        return $this->dispatchCommand(
            $this->options['commandIdentifier'],
            $this->options['commandArguments'] ?? [],
            $this->options['commandContext'] ?? ''
        );
    }

    /**
     * @param string $commandIdentifier the identifier of the flow command
     * @param string[] $commandArguments
     * @param string $commandContext the context the sub command runs in
     *
     * @throws \Neos\Flow\Core\Booting\Exception\SubProcessException
     */
    private function dispatchCommand(string $commandIdentifier, array $commandArguments = [], string $commandContext = ''): bool
    {
        $this->flowSettings['core']['context'] = $commandContext === '' ? $this->environment->getContext() : $commandContext;
        $this->flowSettings['core']['phpBinaryPathAndFilename'] = $this->phpBinaryPathAndFilename;

        return Scripts::executeCommand($commandIdentifier, $this->flowSettings, true, $commandArguments);
    }
}
