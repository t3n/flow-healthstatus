<?php

declare(strict_types=1);

namespace Yeebase\Readiness\LivenessTest;

/**
 * This file is part of the Yeebase.XY package.
 *
 * (c) 2019 yeebase media GmbH
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;

class StatusCodeTest extends AbstractLivenessTest
{
    /**
     * @Flow\InjectConfiguration(path="http.baseUri", package="Neos.Flow")
     *
     * @var ?string
     */
    protected $baseUri;

    public function test(): bool
    {
        $browser = new Browser();

        $browser->setRequestEngine(new CurlEngine());
        $browser->setFollowRedirects($this->options['followRedirects'] ?? true);

        $uri = $this->options['uri'] ?? $this->baseUri ?? 'http://localhost';
        if ($uri[0] === '/') {
            $uri = 'http://localhost' . $uri;
        }

        $response = $browser->request(
            $uri,
            $this->options['method'] ?? 'GET',
            $this->options['arguments'] ?? []
        );

        return $response->getStatusCode() === ($this->options['statusCode'] ?? 200);
    }
}
