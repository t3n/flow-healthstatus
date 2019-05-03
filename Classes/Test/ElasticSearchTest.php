<?php

declare(strict_types=1);

namespace t3n\Flow\HealthStatus\Test;

/**
 * This file is part of the t3n.Flow.HealthStatus package.
 *
 * (c) 2018 yeebase media GmbH
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\Domain\Factory\ClientFactory as ElasticSearchFactory;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;

class ElasticSearchTest extends AbstractTest
{
    /**
     * @Flow\Inject
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @throws InvalidConfigurationException
     */
    public function test(): bool
    {
        if (! class_exists(ElasticSearchFactory::class)) {
            throw new InvalidConfigurationException('ElasticSearchTest depends on FlowPack\ElasticSearch', 1502795288);
        }

        /** @var ElasticSearchFactory $elasicSearchFactory */
        $elasticSearchFactory = $this->objectManager->get(ElasticSearchFactory::class);
        $elasticSearchClient = $elasticSearchFactory->create();
        $response = $elasticSearchClient->request('GET', '/_cluster/health');
        return $response->getTreatedContent()['status'] !== 'red';
    }
}
