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

use Neos\Flow\Annotations as Flow;
use Flowpack\ElasticSearch\Domain\Factory\ClientFactory as ElasticSearchFactory;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;

class ElasticSearchTest extends AbstractTest
{

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @return bool
     * @throws InvalidConfigurationException
     */
    public function test(): bool
    {
        if (!class_exists(ElasticSearchFactory::class)) {
            throw new InvalidConfigurationException('ElasticSearchTest depends on FlowPack\ElasticSearch', 1502795288);
        }

        /** @var ElasticSearchFactory $elasicSearchFactory */
        $elasticSearchFactory = $this->objectManager->get(ElasticSearchFactory::class);
        $elasticSearchClient = $elasticSearchFactory->create();
        $response = $elasticSearchClient->request('GET', '/_cluster/health');
        return $response->getTreatedContent()['status'] !== 'red';
    }
}
