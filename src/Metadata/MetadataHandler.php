<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Metadata;

use Rchdev\SimpleOrm\Exception;
use Rchdev\SimpleOrm\Metadata\Extractor;

class MetadataHandler implements MetadataInterface
{
    protected $extractor;

    /**
     * Create a Metadata Handler
     *
     */
    public function __construct(Extractor\ExtractorInterface $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return $this->extractor->extract();
    }

    /**
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function set($key, $value)
    {

    }

    /**
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {

    }

    /**
     *
     * @return ExtractorInterface
     */
    public function getExtractor()
    {
        return $this->extractor;
    }
}
