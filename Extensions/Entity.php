<?php namespace OrmExtension\Extensions;
use Config\OrmExtension;
use DebugTool\Data;
use IteratorAggregate;
use OrmExtension\DataMapper\EntityTrait;
use OrmExtension\DataMapper\QueryBuilder;
use OrmExtension\DataMapper\QueryBuilderInterface;

/**
 * Class Entity
 * @package OrmExtension\Extensions
 * @property array $all
 * @property array $stored
 * @property array $hiddenFields
 *
 * @property int $id
 */
class Entity extends \CodeIgniter\Entity implements IteratorAggregate {
    use EntityTrait;

    public $id;
    public $stored = [];
    public $hiddenFields = [];

    /**
     * Takes an array of key/value pairs and sets them as
     * class properties, using any `setCamelCasedProperty()` methods
     * that may or may not exist.
     *
     * @param array $data
     */
    public function fill(array $data) {
        $fields = $this->getTableFields();
        foreach($data as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));

            if(method_exists($this, $method)) {
                $this->$method($value);
            } else { // Does not require property to exist, properties are managed by OrmExtension from database columns
                if(in_array($key, $fields)) // Does require field to exists
                    $this->$key = $value;
            }
        }
    }

    private function getSimpleName() {
        return substr(strrchr(get_class($this), '\\'), 1);
    }

    private $_model;

    /**
     * @return Model|QueryBuilderInterface
     */
    public function getModel() {
        if(!$this->_model) {
            $name = OrmExtension::$modelNamespace . $this->getSimpleName() . 'Model';
            $this->_model = new $name();
        }
        return $this->_model;
    }

    /**
     * Override System/Entity, cause of error "Undefined variable: result"
     * @param string $key
     * @return mixed|null
     */
    public function __get(string $key) {

        // Check for relation
        foreach($this->getModel()->getRelations() as $relation) {
            if($relation->getSimpleName() == singular($key)) {
                $className = $relation->getEntityName();
                $this->{$key} = new $className();
                /** @var Entity $entity */
                $entity = $this->{$key};
                $entity->getModel()->whereRelated($relation->getOtherField(), 'id', $this->id);
                return $entity;
            }
        }

        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return \CodeIgniter\Entity|Entity
     */
    public function __set(string $key, $value = null) {
        return parent::__set($key, $value);
    }

    /**
     * @return Entity
     */
    public function first() {
        return isset($this->all) ? reset($this->all) : $this;
    }

}