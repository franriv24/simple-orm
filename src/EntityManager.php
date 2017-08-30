<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm;

use Rchdev\SimpleOrm\Exception;
use Rchdev\SimpleOrm\Repository\Repository;
use Rchdev\SimpleOrm\Metadata\MetadataInterface;
use Rchdev\SimpleOrm\Metadata\MetadataHandler;
use Rchdev\SimpleOrm\Metadata\Extractor;
use Rchdev\SimpleOrm\Metadata\EntityMetadata;
use Rchdev\SimpleOrm\DataWorker;

class EntityManager
{
    /**
     *
     * @var MetadataInterface
     *
     */
    protected $metaHandler  ;

    /**
     *
     * @var DataWorker
     *
     */
    protected $worker      ;

    /**
     *
     * @var array
     *
     */
    protected $repositories;

    /**
     * Create a OrmService
     *
     * @param DataWorker        $adapter
     * @param MetadataInterface $metadata
     */
    public function __construct       (DataWorker $worker, MetadataInterface $metadata = null)
    {
        $this->worker      = $worker->setEntityManager($this);

        if ( $metadata === null ) {
            $metadata = new MetadataHandler(new Extractor\Entity());
        }
        $this->metaHandler = $metadata;
    }

    /**
     * 
     *
     * @param  string    $repositoryId Class entity name
     * @param  array|int $id           Entity id
     * @return Repository|\Rchdev\SimpleOrm\Proxy\EntityProxy
     */
    public function get               ($repositoryId, $id = null)
    {
        $repository = $this->getRepository($repositoryId);

        if ( $id === null ) {
            return $repository;
        }

        return $repository->find($id);
    }

    /**
     * Register and retrieve a repository
     *
     * @param  string $repositoryId Class entity name
     * @return Repository
     */
    public function getRepository     ($repositoryId)
    {
        if ( isset($this->repositories[$repositoryId]) ) {
            return $this->repositories[$repositoryId];
        }

        if ( ! class_exists($repositoryId) ) {
            throw new Exception\InvalidArgumentException(
                sprintf('%s: repository class name \'%s\' does not exist', __CLASS__, $repositoryId)
            );
        }

        return $this->repositories[$repositoryId] = $this->_getRepository($repositoryId);
    }

    /**
     * Retrieves the adapter
     *
     * @return \Zend\Db\Adapter\AdapterInterface
     */
    public function getAdapter        ()
    {
        return $this->worker->getAdapter();
    }

    /**
     * Retrieves DataWorker object
     *
     * @return DataWorker
     */
    public function getWorker         ()
    {
        return $this->worker;
    }

    /**
     * Extract metadata from target entity
     *
     * @param  string $repositoryId Class entity name
     * @return EntityMetadata
     */
    public function getEntityMetadata($repositoryId)
    {
        $this->metaHandler->getExtractor()->setTarget($repositoryId);

        $entityMetadata = EntityMetadata::factory($this->metaHandler->toArray());
        $entityMetadata->setEntityManager($this);

        return $entityMetadata->initialize();
    }

    /**
     * Create a repository
     *
     * @param  string $repositoryId Class entity name
     * @return Repository
     */
    private function _getRepository   ($repositoryId)
    {
        return Repository::factory($this->getEntityMetadata($repositoryId), $this);
    }
}
