<?php namespace OrmExtension\Extensions;
use ArrayIterator;
use Config\OrmExtension;
use IteratorAggregate;
use OrmExtension\Data;
use OrmExtension\DataMapper\EntityTrait;
use Traversable;

/**
 * Class Entity
 * @package OrmExtension\Extensions
 * @property array $all
 * @property array $stored
 *
 * @property int $id
 */
class Entity extends \CodeIgniter\Entity implements IteratorAggregate {
    use EntityTrait;

    public $stored = [];

    private function getSimpleName() {
        return substr(strrchr(get_class($this), '\\'), 1);
    }

    private $_model;
    public function getModel(): Model {
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

}