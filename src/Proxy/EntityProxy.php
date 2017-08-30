<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2017 RCHDEV Software. (http://www.rchdev.com.br)
 */

namespace Rchdev\SimpleOrm\Proxy;

use Rchdev\SimpleOrm\Exception;

class EntityProxy
{
    /**
     * @var bool
     */
    protected $__isInitialized__  = false;

    /**
     * @var array
     */
    protected $__lazyProperties__ = [];

    /**
     * @var array
     */
    protected $__originalSource__ = [];

    /**
     * Indicates if entity was initialized
     *
     * @return EntityProxy
     */
    public function __isInitialized    ()
    {
        return $this->__isInitialized__;
    }

    /**
     * Initialize entity with data source
     *
     * @param  array $datasource
     * @return EntityProxy
     */
    public function __initialize       (array $datasource  )
    {
        $this->__originalSource__  = $datasource;
        $this->__setInitialized(true);

        return $this;
    }

    /**
     * Set entity as initialized
     *
     * @param  boolean $initialized
     * @return EntityProxy
     */
    public function __setInitialized   ($initialized = true)
    {
        $this->__isInitialized__   = $initialized;

        return $this;
    }

    /**
     * Set lazy properties
     *
     * @param  array $properties
     * @return EntityProxy
     */
    public function __setlazyProperties(array $properties)
    {
        $this->__lazyProperties__  = $properties;

        return $this;
    }

    /**
     * Allows access to original data source
     *
     * @param  string $property
     * @return mixed
     */
    public function __get              ($property)
    {
        if ( ! array_key_exists($property, $this->__originalSource__) ) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s: property \'%s\' does not exist in original data source',
                    __CLASS__,
                    $property
                )
            );
        }

        return $this->__originalSource__[$property];
    }

    /**
     * Allows to set/get virtual properties
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call             ($method, $args)
    {
        if ( strpos($method, 'get') === 0 ) {
            $attribute = substr($method, 3);
            $attribute = strtolower($attribute);

            if ( ! array_key_exists($attribute, $this->__lazyProperties__) ) {
                throw new Exception\InvalidArgumentException(
                    sprintf('%s: method \'%s\' has not a lazy property', __CLASS__, $method)
                );
            }

            if ( $this->__lazyProperties__[$attribute]['value'] !== null ) {
                return $this->__lazyProperties__[$attribute]['value'];
            }

            $r = call_user_func_array($this->__lazyProperties__[$attribute]['loader'], [$this]);
            return $this->__lazyProperties__[$attribute]['value'] = $r;
        }
        else {
            if ( strpos($method, 'set') === 0 ) {
                $attribute = substr($method, 3);
                $attribute = strtolower($attribute);

                if ( array_key_exists($attribute, $this->__lazyProperties__) ) {
                    if ( count($args) > 0 && ($args[0] instanceof EntityProxy) ) {
                        $this->__lazyProperties__[$attribute]['value'] = $args[0];
                    }
                }
            }
        }
    }
}
