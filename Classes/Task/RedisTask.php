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

use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\Core\Booting\Exception\SubProcessException;
use Neos\Flow\Core\Booting\Scripts;

class RedisTask extends AbstractTask
{

    /**
     * @param array $options
     * @throws InvalidConfigurationException
     */
    protected function validateOptions(array $options)
    {
        if (!isset($options['hostname'])) {
            throw new InvalidConfigurationException('Redis readiness task needs a "hostname" option', 1502701659);
        }

        if (!isset($options['database'])) {
            throw new InvalidConfigurationException('Redis readiness task needs a "database" option', 1502701660);
        }

        if (!isset($options['command'])) {
            throw new InvalidConfigurationException('Redis readiness task needs a "command" option', 1502701661);
        }
    }


    /**
     * @throws SubProcessException
     */
    public function run()
    {
        $redis = new \Redis();
        $redis->connect($this->options['hostname']);
        $redis->select($this->options['database']);
        $success = $redis->rawCommand($this->options['command'], ...($this->options['arguments'] ?? []));

        if (!$success) {
            throw new \RedisException($redis->getLastError());
        }
    }
}
