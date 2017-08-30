<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Repository;

use Zend\Db\Adapter\Driver\StatementInterface;
use Rchdev\SimpleOrm\Metadata\EntityMetadata;
use Rchdev\SimpleOrm\Proxy\EntityProxy;

interface CollectionInterface
{
    /**
     * Count items
     *
     * @return int
     */
    public function count            ();

    /**
     * Adds an entity to collection
     *
     * @param  EntityProxy $entity
     * @return Collection
     */
    public function add              (EntityProxy       $entity         );

    /**
     * Iterator: get current item
     *
     * @return EntityProxy
     */
    public function current          ();

    /**
     * Set the EntityMetadata
     *
     * @return Collection
     */
    public function setEntityMetadata(EntityMetadata    $entityMetadata );

    /**
     * Set the Statement
     *
     * @param  StatementInterface $queryStatement
     * @return Collection
     */
    public function setQueryStatement(StatementInterface $queryStatement);
}
