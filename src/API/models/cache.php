<?php

namespace Phramework\API\models;

/**
 * Default cache engine
 * 
 * Warning, function not completed yet
 * 
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @subpackage API
 * @category models
 * @todo Use prefix from the settings file
 */
class cache {

    private static $instance = null;
    private static $prefix = 'atlas_';

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return cache The *Singleton* instance.
     */
    public static function getInstance() {
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct( ) {
        try {
            if (!self::$instance && class_exists('Memcached')) {
                self::$instance = new Memcached();
                self::$instance->addServer('localhost', 11211);
            }
        } catch (exception $e) {
            self::$instance = FALSE;
            /* }finally{
             */
        }
    }

    /*
     * Access an memcached object using key
     * if object is not available returns the data using the callback provided by $class, $function, $parameters 
     * 
     * @todo Rename
     * @todo Use anonym functions
     */

    public static function memcached($key, $class, $function, $parameters = array(), $time = MEMCACHED_TIME_DEFAULT) {
        $data = FALSE;

        $memcached = self::getInstance();
        if ($memcached) {
            $key = self::$prefix . $key;


            $data = $memcached->get($key);
            if ($data) {
                return $data;
            }
            /* if( $memcached->getResultCode() != Memcached::RES_NOTSTORED ){
              return $data;
              } */
        }
        $data = call_user_func_array(array($class, $function), $parameters);
        if ($data && $memcached) {
            $memcached->set($key, $data, $time); // or die ("Failed to save data at the server");
        }
        return $data;
    }

    /**
     * 
     * @param string $keys
     * @return boolean
     * 
     * @todo rename
     */
    public static function memcached_delete($keys) {
        $memcached = self::getInstance();
        if (!$memcached) {
            return FALSE;
        }
        if (is_array($keys)) {
            foreach ($keys as $k => $v) {
                $keys[$k] = self::$prefix . $v;

                $memcached->delete($keys[$k]);
            }
        } else {
            $key = self::$prefix . $keys;
            $memcached->delete($key);
        }
        //return $memcached->deleteMulti( $keys );
    }

    /**
     * todo rename
     * @return boolean
     */
    public static function memcached_deleteAll() {
        $memcached = self::getInstance();
        if (!$memcached) {
            return FALSE;
        }
        return $memcached->flush(1);
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone() {
        
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup() {
        
    }

}
