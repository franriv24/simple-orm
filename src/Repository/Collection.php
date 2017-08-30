<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Repository;

use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Adapter\Driver\StatementInterface;
use Rchdev\SimpleOrm\Metadata\EntityMetadata;
use Rchdev\SimpleOrm\Proxy\EntityProxy;

/**
 * Class for handle EntityProxy collection
 */
class Collection extends HydratingResultSet implements CollectionInterface
{

	/**
	 *
	 * @var EntityMetadata
	 */
	protected $entityMetadata;

	/**
	 *
	 * @var StatementInterface
	 */
	protected $queryStatement;

    /**
     * Set the EntityMetadata
     *
     * @return Collection
     */
    public function setEntityMetadata(EntityMetadata    $entityMetadata )
    {
        $this->entityMetadata = $entityMetadata;

        return $this;
    }

    /**
     * Set the Statement
     *
     * @param  StatementInterface $queryStatement
     * @return Collection
     */
    public function setQueryStatement(StatementInterface $queryStatement)
    {
        $this->queryStatement = $queryStatement;

        return $this;
    }

    /**
     * Iterator: is pointer valid?
     *
     * @return bool
     */
    public function valid()
    {
        $this->_initialize();

        return parent::valid();
    }

    public function buffer()
    {
        $this->_initialize();

        return parent::buffer();
    }

    public function isBuffered()
    {
        $this->_initialize();

        return parent::isBuffered();
    }

    /**
     * Retrieve count of fields in individual rows of the result set
     *
     * @return int
     */
    public function getFieldCount()
    {
        $this->_initialize();

        return parent::getFieldCount();
    }

    /**
     * Iterator: rewind
     *
     * @return void
     */
    public function rewind()
    {
        $this->_initialize();

        parent::rewind();
    }

    /**
     * Iterator: move pointer to next item
     *
     * @return void
     */
    public function next()
    {
        $this->_initialize();

        parent::next();
    }

    /**
     * Iterator: retrieve current key
     *
     * @return mixed
     */
    public function key()
    {
        $this->_initialize();

        return parent::key();
    }

    /**
     * Countable: return count of rows
     *
     * @return int
     */
    public function count()
    {
        $this->_initialize();

        return parent::count();
    }

    /**
     * Adds an entity to collection
     *
     * @param  EntityProxy $entity
     * @return Collection
     */
    public function add(EntityProxy $entity)
    {
        return $this;
    }

    /**
     * Iterator: get current item
     *
     * @return EntityProxy
     */
    public function current()
    {
        $this->_initialize();

        if ($this->buffer === null) {
            $this->buffer = -2;
        } elseif (is_array($this->buffer) && isset($this->buffer[$this->position])) {
            return $this->buffer[$this->position];
        }
        $data   = $this->dataSource->current();
        $object = is_array($data) ? $this->hydrator->hydrate($data, clone $this->objectPrototype) : false;

        if ($object instanceof EntityProxy) {
            $object->__initialize($data);
        }

        if (is_array($this->buffer)) {
            $this->buffer[$this->position] = $object;
        }

        return $object;
    }

    /**
     * Cast result set to array of arrays
     *
     * @return array
     * @throws Exception\RuntimeException if any row is not castable to an array
     */
    public function toArray()
    {
        $this->_initialize();

        return parent::toArray();
    }

    /**
     * Initialize collection with datasource
     * and buffer results
     *
     * @return void
     */
    private function _initialize()
    {
        if ( $this->getDataSource() === null ) {
            parent::initialize($this->queryStatement->execute());
            $this->buffer();
        }
    }
}
