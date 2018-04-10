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

use Neos\Cache\Frontend\FrontendInterface;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cache\CacheManager;
use Yeebase\Readiness\Service\EelRuntime;

class LockHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\InjectConfiguration("lockPrefix")
     * @var string
     */
    protected $lockPrefix;

    /**
     * @Flow\InjectConfiguration("defaultCacheName")
     * @var string
     */
    protected $defaultCacheName;

    /**
     * @var string
     */
    protected $overrideCacheName;

    /**
     * @Flow\Inject
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @Flow\Inject
     * @var EelRuntime
     */
    protected $runtime;

    /**
     * @param string $name
     * @return string
     */
    protected function getEntryIdentifier(string $name): string
    {
        return (!empty($this->lockPrefix) ? $this->lockPrefix . '_' : '') . $name;
    }

    /**
     * @return FrontendInterface
     */
    protected function getCache(): FrontendInterface
    {
        try {
            $cache = $this->cacheManager->getCache($this->overrideCacheName ?? $this->defaultCacheName);
        } finally {
            $this->overrideCacheName = null;
        }

        return $cache;
    }

    /**
     * @param string $cacheName
     * @return $this
     */
    public function withCache(string $cacheName)
    {
        $this->overrideCacheName = $cacheName;
        return $this;
    }

    /**
     * @param string $lockName
     * @param string $value
     */
    public function set(string $lockName, string $value = '1')
    {
        $this->getCache()->set($this->getEntryIdentifier($lockName), $value, [], 0);
    }

    /**
     * @param string $lockName
     */
    public function unset(string $lockName)
    {
        $this->getCache()->remove($this->getEntryIdentifier($lockName));
    }

    /**
     * @param string $lockName
     * @return bool
     */
    public function isSet(string $lockName)
    {
        return $this->getCache()->has($this->getEntryIdentifier($lockName));
    }

    /**
     * @param string $lockName
     * @return bool
     */
    public function isUnset(string $lockName)
    {
        return !$this->isSet($lockName);
    }

    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return substr($methodName, 0, 2) === 'is' || $this->runtime->isTaskContext();
    }
}
