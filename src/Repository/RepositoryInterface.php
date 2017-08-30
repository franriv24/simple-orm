<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Repository;

use Rchdev\SimpleOrm\Proxy\EntityProxy;

interface RepositoryInterface
{
    /**
     *
     * @param  EntityProxy $entity
     * @return int
     */
    public function save     (EntityProxy $entity);

    /**
     *
     * @param  mixed
     * @return object
     */
    public function find     ($id);

    /**
     *
     * @param  mixed $where
     * @return Result
     */
    public function findBy   ($where);

    /**
     *
     * @param  mixed $where
     * @return Result
     */
    public function findOneBy($where);

    /**
     *
     * @param  mixed $where
     * @param  mixed $order
     * @param  int   $limit
     * @param  int   $offset
     * @return Result
     */
    public function findAll  ($where = null, $order = null, $limit = null, $offset = null);

    /**
     *
     * @param  mixed $where
     * @return int
     */
    public function delete   ($where);
}
