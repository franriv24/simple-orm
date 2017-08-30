<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Metadata\Extractor;

interface ExtractorInterface
{
    /**
     *
     * @param  mixed $target
     * @return array
     */
    public function setTarget($target);

    /**
     *
     * @return array
     */
    public function extract  ();
}
