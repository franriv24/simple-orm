<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Metadata\Extractor;

use Rchdev\SimpleOrm\Exception;

class Entity implements ExtractorInterface
{
    /**
     *
     * @var string
     *
     */
    protected $target;

    /**
     *
     * @param  mixed $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->target = (string) $target;

        return $this;
    }

    /**
     * Extract data from entity
     * @return array
     */
    public function extract  ()
    {
        if ( $this->target === null ) {
            throw new Exception\InvalidArgumentException(
                sprintf('%s requires a valid target before extract', __CLASS__)
            );
        }

        $driver = new Driver\Annotations($this->target);
        $array  = $driver->toArray();

        return [
            'entityId'   => $this->target,
            'tableName'  => $array['class']->get('tableName' ),
            'primaryKey' => $array['class']->get('primaryKey'),
            'joinTables' => $array['class']->useNamespace('joinTables')->toArray(),
        ];
    }
}
