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

use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Pheanstalk\Pheanstalk;

class BeanstalkTest extends AbstractTest
{
    /**
     * @param array $options
     * @throws InvalidConfigurationException
     */
    protected function validateOptions(array $options)
    {
        if (!isset($options['hostname'])) {
            throw new InvalidConfigurationException('Beanstalk readiness test needs a "hostname" option', 1502701630);
        }
    }

    /**
     * @return bool
     */
    public function test(): bool
    {
        $beanstalkClient = new Pheanstalk($this->options['hostname']);
        return $beanstalkClient->getConnection()->isServiceListening();
    }
}
