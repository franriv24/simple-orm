<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm;

use Rchdev\SimpleOrm\Exception;
use Rchdev\SimpleOrm\EntityManager;
use Rchdev\SimpleOrm\Proxy\EntityProxy;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql as SQLFactory;

class DataWorker
{
    /**
     * @var AdapterInterface
     */
    protected $adapter           = null;

    /**
     * @var EntityManager
     */
    protected $entityManager     = null;

    /**
     * SQL Factory prototype
     *
     * @var SQLFactory
     */
    protected $sqlPrototype      = null;

    /**
     * Map of processed entities
     *
     * @var array
     */
    protected $identityMap       = [];

    /**
     * Map of processed entities
     *
     * @var array
     */
    protected $entityIdentifiers = [];

    /**
     * Constructor
     *
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function setEntityManager     (EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Adds an entity to identity map
     *
     * @param  EntityProxy $entity
     * @return bool
     */
    public function addToIdentityMap     (EntityProxy $entity)
    {
        $className  = get_class($entity);
        $identifier = $this->entityIdentifiers[spl_object_hash($entity)];
        if (empty($identifier) || in_array(null, $identifier, true)) {
            throw new \InvalidArgumentException('The given entity has no identity.');
        }

        $idHash     = implode(' ', $identifier);
        if (isset($this->identityMap[$className][$idHash])) {
            return false;
        }
        $this->identityMap[$className][$idHash] = $entity;

        return true;
    }

    /**
     * Removes an entity from identity map
     *
     * @param  EntityProxy $entity
     * @return bool
     */
    public function removeFromIdentityMap(EntityProxy $entity)
    {
        $objHash    = spl_object_hash($entity);
        $className  = get_class($entity);
        $identifier = $this->entityIdentifiers[$objHash];
        if (empty($identifier) || in_array(null, $identifier, true)) {
            throw new \InvalidArgumentException('The given entity has no identity.');
        }

        $idHash     = implode(' ', $identifier);
        if (isset($this->identityMap[$className][$idHash])) {
            unset($this->identityMap[$className][$idHash]);

            if ( isset($this->entityIdentifiers[$objHash]) ) {
                unset ($this->entityIdentifiers[$objHash]);
            }

            return true;
        }

        return false;
    }

    /**
     * Indicates when an entity is in identity map
     *
     * @param  EntityProxy $entity
     * @return boolean
     */
    public function isInIdentityMap      (EntityProxy $entity)
    {
        $objHash    = spl_object_hash($entity);
        if ( ! isset($this->entityIdentifiers[$objHash])) {
            return false;
        }

        $identifier = $this->entityIdentifiers[$objHash];
        if (empty($identifier) || in_array(null, $identifier, true)) {
            return false;
        }

        $className = get_class($entity);
        $idHash    = implode(' ', $identifier);

        return isset($this->identityMap[$className][$idHash]);
    }

    /**
     * Retrieves an entity
     *
     * @param  string $className
     * @param  array  $id
     * @return EntityProxy
     */
    public function getEntityInstanceById($className, array $id)
    {
        $idHash = implode(' ', $id);
        if (empty($idHash)) {
            throw new \InvalidArgumentException(
                sprintf('%s: the given identity can not be empty.', __CLASS__)
            );
        }

        if ( isset($this->identityMap[$className][$idHash]) ) {
            return $this->identityMap[$className][$idHash];
        }

        $classMetadata  = $this->entityManager->get($className)->getEntityMetadata();
        $entityInstance = $this->entityManager->get($className)->getNew();

        if ( $entityInstance instanceof EntityProxy ) {
            $whereClause = [];
            foreach ($classMetadata->getPrimarykey() as $key) {
                $whereClause[$key] = $id[$key];
            }
            $entityData  = $this->doSelect($classMetadata->getTableName(), $whereClause)->current();

            if ( is_array($entityData) ) {
                // populate
                $classMetadata->getHydratorInstance()->hydrate($entityData, $entityInstance);

                // initialize with original data
                $entityInstance->__initialize($entityData);

                // register
                $objHash = spl_object_hash($entityInstance);
                $this->entityIdentifiers[$objHash] = array_values($id);
                $this->addToIdentityMap ($entityInstance);

                return $entityInstance;
            }
        }

        return false;
    }

    /**
     * Retrieve Adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Execute a select operation
     *
     * @param  string $table
     * @param  mixed  $where
     * @param  mixed  $order
     * @param  int    $limit
     * @param  int    $offset
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function doSelect($table, $where = null, $order = null, $limit = null, $offset = null)
    {
        $_query = $this->_query();
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

        $result = $_query->prepareStatementForSqlObject($select)->execute();

        return $result;
    }

    /**
     * Execute an insert operation
     *
     * @param  string $table
     * @param  array  $data
     * @return int
     */
    public function doInsert($table, array $data)
    {
        $_query    = $this->_query($table);
        $sqlObject = $_query->insert();
        $sqlObject->values($data);

        $statement = $_query->prepareStatementForSqlObject($sqlObject);
        $result    = $statement->execute();

        return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
    }

    /**
     * Execute an update operation
     *
     * @param  string $table
     * @param  array  $data
     * @param  array  $where
     * @return int
     */
    public function doUpdate($table, array $data, array $where)
    {
        $_query    = $this->_query($table);
        $sqlObject = $_query->update();
        $sqlObject->set  ($data );
        $sqlObject->where($where);

        $statement = $_query->prepareStatementForSqlObject($sqlObject);
        $result    = $statement->execute();

        return $result->getAffectedRows();
    }

    /**
     * Execute a delete operation
     *
     * @param  string $table
     * @param  mixed  $where
     * @return int
     */
    public function doDelete($table, $where = null)
    {
        $_query    = $this->_query($table);
        $delete    = $_query->delete();

        if ( $where != null ) {
            $delete->where($where);
        }

        $statement = $_query->prepareStatementForSqlObject($delete);
        $result    = $statement->execute();

        return $result->getAffectedRows();
    }

    /**
     * Retrieve SQL Factory prototype
     *
     * @param  string $table
     * @return SQLFactory
     */
    public function getQuery       ($table = null)
    {
        return $this->_query($table);
    }

    /**
     * Retrieve a SQLFactory prototype
     *
     * @return SQLFactory
     */
    protected function _query      ($table = null)
    {
        if ( $this->sqlPrototype == null ) {
            $this->sqlPrototype = new SQLFactory($this->adapter);
        }

        $prototype = clone $this->sqlPrototype;

        if ( $table ) {
            $prototype->setTable($table);
        }

        return $prototype;
    }
}