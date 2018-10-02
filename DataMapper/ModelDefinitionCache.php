<?php namespace OrmExtension\DataMapper;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Config\Services;
use Config\Cache;
use Config\Database;
use Config\OrmExtension;
use OrmExtension\Extensions\Model;

/**
 * Class ModelDefinitionCache
 * @package OrmExtension\DataMapper
 * @property Cache $config
 * @property Cache|CacheInterface $cache
 */
class ModelDefinitionCache {

    /** @var ModelDefinitionCache $instance */
    private static $instance;
    public static function getInstance() {
        if(static::$instance == null) {
            static::$instance = new ModelDefinitionCache();
            static::$instance->init();
        }
        return static::$instance;
    }

    private static $directory = 'OrmExtension';
    private $config;

    public function init() {
        $this->config            = new Cache();
        $this->config->storePath .= self::$directory;
        $this->cache = Services::cache($this->config);
    }


    public static function setFields($entity, $fields) {
        static::setData($entity.'_fields', $fields);
    }

    public static function getFields($entity, $tableName = null) {
        $fields = static::getData($entity.'_fields');
        if(!$fields) {
            $fieldData = ModelDefinitionCache::getFieldData($entity, $tableName);
            foreach($fieldData as $field) $fields[] = $field->name;
            ModelDefinitionCache::setFields($entity, $fields);
        }
        return $fields;
    }

    public static function setFieldData($entity, $fields) {
        static::setData($entity.'_field_data', $fields);
    }

    public static function getFieldData($entity, $tableName = null) {
        $fieldData = static::getData($entity.'_field_data');
        if(!$fieldData) {
            if(is_null($tableName)) {
                $modelName = OrmExtension::$modelNamespace . $entity . 'Model';
                /** @var Model $model */
                $model = new $modelName();
                $tableName = $model->getTableName();
            }
            $db = Database::connect();
            $fieldData = $db->getFieldData($tableName);
            ModelDefinitionCache::setFieldData($entity, $fieldData);
        }
        return $fieldData;
    }

    public static function setRelations($entity, $relations) {
        static::setData($entity.'_relations', $relations);
    }

    public static function getRelations($entity) {
        $relations = static::getData($entity.'_relations');
        if(!$relations) {
            $modelName = OrmExtension::$modelNamespace . $entity . 'Model';
            /** @var Model $model */
            $model = new $modelName();
            $relations = [];
            foreach($model->hasOne as $name => $hasOne)
                $relations[] = new RelationDef($model, $name, $hasOne, RelationDef::HasOne);
            foreach($model->hasMany as $name => $hasMany)
                $relations[] = new RelationDef($model, $name, $hasMany, RelationDef::HasMany);
            ModelDefinitionCache::setRelations($entity, $relations);
        }
        return $relations;
    }





    private static function setData($name, $data, $ttl = 3600) {
        $instance = ModelDefinitionCache::getInstance();
        $instance->cache->save($name, $data, $ttl);
    }

    private static function getData($name) {
        $instance = ModelDefinitionCache::getInstance();
        return $instance->cache->get($name);
    }


}