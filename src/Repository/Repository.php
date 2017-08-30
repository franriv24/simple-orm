<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Repository;

use Rchdev\SimpleOrm\Exception;
use Rchdev\SimpleOrm\Metadata\EntityMetadata;
use Rchdev\SimpleOrm\EntityManager;

class Repository extends AbstractRepository
{
    /**
     * Factory for creating repositories
     *
     * @param  EntityMetadata $metadata
     * @return Repository
     */
    public static function factory(EntityMetadata $metadata, EntityManager $entityManager)
    {
        return new static($metadata, $entityManager);
    }

    /**
     * Create a Repository
     *
     * @param EntityMetadata $metadata
     * @param EntityManager  $entityManager
     */
    public function __construct   (EntityMetadata $metadata, EntityManager $entityManager)
    {
        $this->metadata            = $metadata;
        $this->em                  = $entityManager;
        $this->hydrator            = $metadata->getHydratorInstance();
        $this->collectionPrototype = new Collection(
            $this->hydrator,
            $this->metadata->getEntityInstance()->__setlazyProperties(
                $this->metadata->getLazyProperties()
            )
        );
    }
}
