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

use Doctrine\ORM\EntityManagerInterface;

class DoctrineTest extends AbstractTest
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function injectEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function test(): bool
    {
        $databaseConnection = $this->entityManager->getConnection();
        return $databaseConnection->isConnected() || $databaseConnection->connect();
    }
}
