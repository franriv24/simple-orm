<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Repository;

use Zend\Db\Adapter\AdapterInterface;
use Rchdev\SimpleOrm\Proxy\EntityProxy;
use Rchdev\SimpleOrm\Metadata\EntityMetadata;

/**
 * Abstract class for Repositories
 */
abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var EntityMetadata
     */
    protected $metadata             = null;

    /**
     * @var \Rchdev\SimpleOrm\EntityManager
     */
    protected $em                   = null;

    /**
     *
     * @var EntityProxy
     */
    protected $entityProxyPrototype = null;

    /**
     *
     * @var \Zend\Hydrator\HydratorInterface
     */
    protected $hydrator             = null;

    /**
     *
     * @var Collection
     */
    protected $collectionPrototype  = null;

    /**
     * Save entity data
     *
     * @param  EntityProxy $entity
     * @return bool
     */
    public function save  (EntityProxy $entity)
    {
        $data  = $this->hydrator->extract($entity);
        $keys  = $this->metadata->getPrimaryKey();
        $table = $this->metadata->getTableName ();

        // set identifier values from related entities
        if ( $this->metadata->hasJoinTables() ) {
            $data = array_merge($data, $this->getIndentifierValuesEntitiesFrom($entity));
        }

        // if object data was injected perform an update
        if ( $entity->__isInitialized() ) {
            $where = [];
            foreach ($keys as $key) {
                $where[$key] = $data[$key];
            }
            return $this->em->getWorker()->doUpdate($table, $data, $where);
        }

        // doing insert and setting generated id
        $result = $this->em->getWorker()->doInsert ($table, $data);

        if ( method_exists($entity, 'setId') ) {
            $entity->setId($result);
        }

        return true;
    }

    /**
     * Returns a clean cloned proxy entity from repository
     * 
     * @return EntityProxy
     */
    public function getNew   ()
    {
        if ( $this->entityProxyPrototype == null ) {
            $entityInstance = $this->metadata->getEntityInstance();
            $entityInstance->__setlazyProperties($this->metadata->getLazyProperties());
            $this->entityProxyPrototype = $entityInstance;
        }

        return clone $this->entityProxyPrototype;
    }

    /**
     * Retrieve an entity by identifier
     *
     * @param  mixed $id
     * @return EntityProxy
     */
    public function find     ($id)
    {
        $class  = $this->metadata->getEntityClassName();
        $keys   = $this->metadata->getPrimaryKey();
        $values = is_array($id) ? array_values($id) : [$id];
        $where  = [];

        if ( $this->metadata->primarykeyIsComposite() ) {
            if ( ! is_array($id) || (count($id) != count($keys)) ) {
                throw new Exception\InvalidArgumentException('Missing keys for composite primary key');
            }
        }

        foreach ($keys as $index => $key) {
            $where[$key] = $values[$index];
        }

        return $this->em->getWorker()->getEntityInstanceById($class, $where);
    }

    /**
     * Find entities by any fields
     * 
     * @param  mixed $where
     * @return Collection
     */
    public function findBy   ($where)
    {
        return $this->findAll($where);
    }

    /**
     * Find one entity by any fields
     * 
     * @param  mixed $where
     * @return Collection
     */
    public function findOneBy($where)
    {
        return $this->findAll($where, null, 1, null)->current();
    }

    /**
     * Find all entities
     * 
     * @param  mixed $where
     * @param  mixed $order
     * @param  int   $limit
     * @param  int   $offset
     * @return Collection
     */
    public function findAll  ($where = null, $order = null, $limit = null, $offset = null)
    {
        $table  = $this->metadata->getTableName();
        $_query = $this->em->getWorker()->getQuery();
        $select = $_query->select($table);

        if ( $where  != null ) {
            $select->where ($where );
        }

        if ( $order  != null ) {
            $select->order ($order );
        }

        if ( $limit  != null ) {
            $select->limit ($limit );
        }

        if ( $offset != null ) {
            $select->offset($offset);
        }

        $collection = clone $this->collectionPrototype;

        return $collection
                ->setEntityMetadata($this->metadata)
                ->setQueryStatement($_query->prepareStatementForSqlObject($select));
    }

    /**
     * Delete a record from database
     * 
     * @param  mixed $where
     * @return int
     */
    public function delete($where = null)
    {
        return $this->em->getWorker()->doDelete($this->metadata->getTableName(), $where);
    }

    /**
     * Retrieve entity metadata object
     *
     * @return EntityMetadata
     */
    public function getEntityMetadata()
    {
        return $this->metadata;
    }

    /**
     * Retrieves identifier values from related entities
     * 
     * @param  EntityProxy $entity
     * @return array
     */
    private function getIndentifierValuesEntitiesFrom(EntityProxy $entity)
    {
        $joindata    = $this->metadata->getJoinTables();
        $identifiers = [];

        foreach ($joindata as $virtualProperty => $join) {
            $method = 'get' . ucfirst(strtolower($virtualProperty));

            if ( isset($join->joinColumn[0]) ) {
                $c = $join->joinColumn[0];
                $m = 'get'  . ucfirst(strtolower($c->to));
                $e = $entity->$method();
                if ( $e instanceof EntityProxy && method_exists($e, $m) ) {
                    $identifiers[$c->from] = $e->$m();
                }
            }
        }

        return $identifiers;
    }
}
