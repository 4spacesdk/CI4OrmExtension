<?php namespace OrmExtension\DataMapper;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Config\Services;
use Config\Cache;
use Config\Database;
use Config\OrmExtension;
use DebugTool\Data;
use OrmExtension\Extensions\Model;

/**
 * Class ModelDefinitionCache
 * @package DebugTool\DataMapper
 * @property Cache $config
 * @property Cache|CacheInterface $cache
 */
class ModelDefinitionCache {

    /** @var ModelDefinitionCache $instance */
    private static $instance;
    public static function getInstance() {
        if(static::$instance == null) {
            static::$instance = new ModelDefinitionCache();
        }
        if(!isset(self::$instance->cache)) {
            static::$instance->init();
        }
        return static::$instance;
    }

    private static $directory = 'OrmExtension';
    private $config;

    public function init() {
        $this->config            = new Cache();
        $this->config->storePath .= self::$directory;
        if (!is_dir($this->config->storePath)) {
            mkdir($this->config->storePath);
        }
        $this->cache = Services::cache($this->config, false);
    }


    public static function setFields($entity, $fields) {
        static::setData($entity.'_fields', $fields);
    }

    public static function getFields($entity, $tableName = null) {
        $fields = static::getData($entity.'_fields');
        if(is_null($fields)) {
            $fieldData = ModelDefinitionCache::getFieldData($entity, $tableName);
            $fields = [];
            if($fieldData) {
                foreach($fieldData as $field) $fields[] = $field->name;
            }
            ModelDefinitionCache::setFields($entity, $fields);
        }
        return $fields;
    }

    public static function setFieldData($entity, $fields) {
        static::setData($entity.'_field_data', $fields);
    }

    public static function getFieldData($entity, $tableName = null) {
        $fieldData = static::getData($entity.'_field_data');
        if(is_null($fieldData)) {
            if(is_null($tableName)) {

                foreach(OrmExtension::$modelNamespace as $modelNamespace) {
                    $modelName = $modelNamespace . $entity . 'Model';
                    if(class_exists($modelName)) {
                        /** @var Model $model */
                        $model = new $modelName();
                        $tableName = $model->getTableName();
                    }
                }
            }

            $db = Database::connect();
            try {
                $fieldData = $db->getFieldData($tableName);
                ModelDefinitionCache::setFieldData($entity, $fieldData);
            } catch(\Exception $e) {
                // Ignore table doesn't exist
                if($e->getCode() != 1146) {
                    throw $e;
                }
            }
        }
        return $fieldData;
    }

    public static function setRelations($entity, $relations) {
        static::setData($entity.'_relations', $relations);
    }

    /**
     * @param $entity
     * @return RelationDef[]
     * @throws \Exception
     */
    public static function getRelations($entity) {
        $relations = static::getData($entity.'_relations');
        if (!$relations) {
            foreach (OrmExtension::$modelNamespace as $modelNamespace) {
                $modelName = $modelNamespace . $entity . 'Model';
                if (class_exists($modelName)) {
                    /** @var Model $model */
                    $model = new $modelName();
                    break;
                }
            }
            $relations = [];
            foreach ($model->hasOne as $name => $hasOne) {
                $relations[] = new RelationDef($model, $name, $hasOne, RelationDef::HasOne);
            }
            foreach ($model->hasMany as $name => $hasMany) {
                $relations[] = new RelationDef($model, $name, $hasMany, RelationDef::HasMany);
            }
            ModelDefinitionCache::setRelations($entity, $relations);
        }
        return $relations;
    }





    private static function setData($name, $data, $ttl = YEAR) {
        $instance = ModelDefinitionCache::getInstance();
        if (isset($instance->cache)) {
            $instance->cache->save($name, $data, $ttl);

            // Change file permissions so other users can read and write
            $cacheInfo = $instance->cache->getCacheInfo();
            if (isset($cacheInfo[$name])) {
                chmod($cacheInfo[$name]['server_path'], 0775);
            }
        }
    }

    private $memcache = [];
    private static function getData($name) {
        try {
            $instance = ModelDefinitionCache::getInstance();
            if(!isset($instance->memcache[$name])) {
                $data = $instance->cache->get($name);
                if($data) $instance->memcache[$name] = $data;
                return $data;
            } else {
                return $instance->memcache[$name];
            }
        } catch(\Exception $e) {
            return null;
        }
    }

    public function clearCache($rmDir = false) {
        $this->memcache = [];
        $this->cache->clean();
        if ($rmDir && is_dir($this->config->storePath)) {
            rmdir($this->config->storePath);
        }
    }


}
