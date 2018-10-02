<?php namespace OrmExtension;
use Config\Database;
use OrmExtension\Extensions\Entity;


/**
 * Class Data
 * @property array $data
 */
class Data {
    protected static $instance = null;
    /**
     * @return Data
     */
    protected static function getInstance() {
        if(!isset(static::$instance)) {
            static::$instance = new Data();
        }
        return static::$instance;
    }

    private $data = [];

    public static function set($key, $value) {
        $instance = static::getInstance();
        $instance->data[$key] = $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public static function has($key) {
        $instance = static::getInstance();
        return isset($instance->controller->data[$key]);
    }
    public static function lastQuery() {
        Data::sql(Database::connect()->showLastQuery());
    }
    public static function sql($sql) {
        Data::debug(str_replace("\n", " ", str_replace("\t", " ", $sql)));
    }
    public static function debug($info = 'test') {
        $func_args = func_get_args();
        if(count($func_args) > 1) $info = implode(' ', $func_args);
        $instance = static::getInstance();

        if(!isset($instance->data['debug'])) $instance->data['debug'] = array();
        $time = explode(" ",microtime());
        $time = date("H:i:s", $time[1]).substr((string)$time[0],1,4);
        if(is_object($info) && $info instanceof Entity && $info->count())
            $instance->data['debug'][] = [
                $time => $info->allToArray()
            ];
        else if(is_object($info) && $info instanceof Entity)
            $instance->data['debug'][] = [
                $time => $info->toArray()
            ];
        else if(is_object($info))
            $instance->data['debug'][] = [
                $time => $info
            ];
        else if(is_array($info))
            $instance->data['debug'][] = [
                $time => $info
            ];
        else
            $instance->data['debug'][] = "$time: $info";
    }
    public static function getData() {
        $instance = static::getInstance();
        return $instance->data;
    }
    public static function get($key) {
        $instance = static::getInstance();
        return $instance->data[$key];
    }
}