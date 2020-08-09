<?php

declare(strict_types=1);

namespace t3n\Flow\HealthStatus\LivenessTest;

/**
 * This file is part of the t3n.Flow.HealthStatus package.
 *
 * (c) 2018 yeebase media GmbH
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

        $browser->setRequestEngine($this->buildCurlEngine());
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

    private function buildCurlEngine(): CurlEngine
    {
        $curlEngine = new CurlEngine();
        if (! isset($this->options['curlOptions']) || ! is_array($this->options['curlOptions'])) {
            return $curlEngine;
        }

        foreach ($this->options['curlOptions'] as $curlOptionKey => $curlOptionValue) {
            $curlOption = constant($curlOptionKey);
            if ($curlOption === null) {
                continue;
            }
            $curlEngine->setOption($curlOption, $curlOptionValue);
        }

        return $curlEngine;
    }
}
