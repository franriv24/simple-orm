<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Metadata;

interface MetadataInterface
{
    /**
     *
     * @return array
     */
    public function toArray     ();

    /**
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function set         ($key, $value);

    /**
     *
     * @param  string $key
     * @return mixed
     */
    public function get         ($key);

    /**
     *
     * @return ExtractorInterface
     */
    public function getExtractor();
}
