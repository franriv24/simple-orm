<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Metadata;

use Rchdev\SimpleOrm\Exception;
use Rchdev\SimpleOrm\EntityManager;
use Rchdev\SimpleOrm\Proxy\EntityProxy;

class EntityMetadata
{
    /**
      *
      * @var array
      */
    protected $primaryKey    ;

    /**
      *
      * @var array
      */
    protected $metadata      ;

    /**
      *
      * @var EntityManager
      */
    protected $entityManager ;

    /**
      *
      * @var array
      */
    protected $lazyProperties;

    /**
      *
      * @var bool
      */
    protected $isInitialized = false;

    /**
      *
      * @var bool
      */
    protected $hasJoinTables = null ;

    /**
     * Hydrator for this entity
     *
     * @var \Zend\Hydrator\HydratorInterface
     */
    protected $hydrator      = null ;

    /**
     * Factory for creating entity metadata
     *
     * @param  array $metadata
     * @return EntityMetadata
     */
    public static function factory       (array $metadata)
    {
        if (! isset($metadata['entityId'  ]) ) {
            throw new Exception\InvalidArgumentException('Entity ID is not defined'  );
        }

        if (! isset($metadata['hydrator'  ]) || ! class_exists($metadata['hydrator']) ) {
            $metadata['hydrator'] = \Zend\Hydrator\ClassMethods::class;
        }

        if (! isset($metadata['tableName' ]) ) {
            throw new Exception\InvalidArgumentException('Table name is not defined' );
        }

        if (! isset($metadata['primaryKey']) ) {
            throw new Exception\InvalidArgumentException('Primary key is not defined');
        }

        $metadata['pkComposite'] = false;
        if ( is_array($metadata['primaryKey']) ) {
            $metadata['pkComposite'] = true;
        }

        return new static($metadata);
    }

    /**
     * Create an entity metadata
     *
     * @param array $metadata
     */
    public function __construct          (array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Initialize Entity Metadata
     *
     * @return EntityMetadata
     */
    public function initialize           ()
    {
        if ( $this->isInitialized ) {
            return;
        }

        // initialize lazy properties with loaders
        if ( $this->lazyProperties == null ) {
            $this->lazyProperties = [];
            if ( $this->entityManager !== null ) {
                $joinTables = $this->getJoinTables();
                foreach ($joinTables as $virtualProperty => $join) {
                    $this->lazyProperties[$virtualProperty] = [
                        'value'  => null,
                        'loader' => $this->getLazyLoader($join),
                    ];
                }
            }
        }

        $this->isInitialized = true;

        return $this;
    }

    /**
     * Retrieve primary key
     *
     * @return array
     */
    public function getPrimarykey        ()
    {
        if ( $this->primaryKey == null ) {
            $primaryKey = $this->metadata['primaryKey'];
            $primaryKey = $this->primarykeyIsComposite() ? $primaryKey : [$primaryKey];

            $this->primaryKey = $primaryKey;
        }

        return $this->primaryKey;
    }

    /**
     * Retrieve the table name
     *
     * @return string
     */
    public function getTableName         ()
    {
        return $this->metadata['tableName'];
    }

    /**
     * Retrieve the hydrator
     *
     * @return \Zend\Hydrator\HydratorInterface
     */
    public function getHydratorInstance  ()
    {
        if ( $this->hydrator === null ) {
            $this->hydrator = new $this->metadata['hydrator'];
        }
        return $this->hydrator;
    }

    /**
     * Retrieve a brand new entity
     *
     * @return EntityProxy
     */
    public function getEntityInstance    ()
    {
        return new $this->metadata['entityId'];
    }

    /**
     * Retrieve the entity class name
     *
     * @return string
     */
    public function getEntityClassName   ()
    {
        return $this->metadata['entityId'];
    }

    /**
     * Indicates primary key is composite
     *
     * @return bool
     */
    public function primarykeyIsComposite()
    {
        return $this->metadata['pkComposite'];
    }

    /**
     * Retrieves join tables information
     *
     * @return array
     */
    public function getJoinTables        ()
    {
        if ( $this->hasJoinTables() ) {
            return $this->metadata['joinTables'];
        }
        return [];
    }

    /**
     * Indicates if entity has join tables information
     *
     * @return bool
     */
    public function hasJoinTables        ()
    {
        if ( $this->hasJoinTables === null ) {
            $isArray = is_array($this->metadata['joinTables']);
            $count   = $isArray ? count($this->metadata['joinTables']) : 0;
            $this->hasJoinTables = $count > 0;
        }
        return $this->hasJoinTables;
    }

    /**
     * Retrieves lazy properties
     *
     * @return array
     */
    public function getLazyProperties    ()
    {
        return $this->lazyProperties;
    }

    /**
     * Set the entity manager
     *
     * @return EntityMetadata
     */
    public function setEntityManager     (EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    /**
     * Retrieves the entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager     ()
    {
        return $this->entityManager;
    }

    /**
     * Retrieves metadata as array
     *
     * @return array
     */
    public function toArray              ()
    {
        return $this->metadata;
    }

    /**
     * Retrieves a clousure for fetching data from related tables
     *
     * @param  object     $join Join metadata
     * @return \Clousure
     */
    private function getLazyLoader       ($join)
    {
        $em = $this->getEntityManager();

        return function(EntityProxy $entityInstance) use($join, $em) {
            $repository  = $em->getRepository($join->targetEntity);
            $whereclause = [];

            foreach ( $join->joinColumn as $column  ) {
                $entityColumnFrom = $entityInstance->{$column->from};
                if ( ! empty($entityColumnFrom) ) {
                    $whereclause[$column->to] = $entityColumnFrom;
                }
            }

            if ( empty($whereclause) ) {
                return null;
            }

            switch ( $join->relationType ) {
                case 'oneToOne' :
                case 'manyToOne':
                    return $repository->find   ($whereclause);
                break;
                case 'oneToMany':
                    return $repository->findAll($whereclause);
                break;
            }

            return null;
        };
    }
}
