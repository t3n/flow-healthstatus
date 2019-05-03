<?php

declare(strict_types=1);

namespace t3n\Flow\HealthStatus\Eel\Helper;

/**
 * This file is part of the t3n.Flow.HealthStatus package.
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
use t3n\Flow\HealthStatus\Service\EelRuntime;

class LockHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\InjectConfiguration("lockPrefix")
     *
     * @var string
     */
    protected $lockPrefix;

    /**
     * @Flow\InjectConfiguration("defaultCacheName")
     *
     * @var string
     */
    protected $defaultCacheName;

    /**
     * @var string
     */
    protected $overrideCacheName;

    /**
     * @Flow\Inject
     *
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @Flow\Inject
     *
     * @var EelRuntime
     */
    protected $runtime;

    protected function getEntryIdentifier(string $name): string
    {
        return (! empty($this->lockPrefix) ? $this->lockPrefix . '_' : '') . $name;
    }

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
     * @return $this
     */
    public function withCache(string $cacheName)
    {
        $this->overrideCacheName = $cacheName;
        return $this;
    }

    public function set(string $lockName, string $value = '1'): void
    {
        $this->getCache()->set($this->getEntryIdentifier($lockName), $value, [], 0);
    }

    public function unset(string $lockName): void
    {
        $this->getCache()->remove($this->getEntryIdentifier($lockName));
    }

    public function isSet(string $lockName): bool
    {
        return $this->getCache()->has($this->getEntryIdentifier($lockName));
    }

    public function isUnset(string $lockName): bool
    {
        return ! $this->isSet($lockName);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return substr($methodName, 0, 2) === 'is' || $this->runtime->isTaskContext();
    }
}
