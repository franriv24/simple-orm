<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Metadata\Extractor\Driver;

use Rchdev\SimpleOrm\Exception;

use Minime\Annotations\Reader;
use Minime\Annotations\Parser;
use Minime\Annotations\Cache ;

class Annotations implements DriverInterface
{
    protected $class_id    = null;
    protected $reader      = null;
    protected $cache_file  = 'data/cache/SimpleOrm/Metadata/';
    protected $annotations = [];

    protected $initialized = false;

    /**
     * Create an annotations strategy
     *
     * @param string $class_id
     */
    public function __construct($class_id)
    {
        if ( ! class_exists($class_id) ) {
            throw new Exception\InvalidArgumentException(
                __CLASS__ . ' requires a valid entity class'
            );
        }

        $this->class_id = $class_id;
        $this->reader   = new Reader(new Parser, new Cache\FileCache($this->cache_file));
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        if ( ! $this->initialized ) {
            $this->initialize();
        }

        return $this->annotations;
    }

    /**
     * Initialize object reading annotations from target class
     * @return void
     */
    protected function initialize()
    {
        // Get class annotations
        $class_annotations = $this->reader->getClassAnnotations($this->class_id);
        $this->annotations['class'] = $class_annotations;

        $this->initialized = true;
    }
}
