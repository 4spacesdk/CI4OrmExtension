<?php namespace OrmExtension\DataMapper;
use DebugTool\Data;
use Exception;
use OrmExtension\Extensions\Model;

/**
 * Class RelationDef
 * @package DebugTool\DataMapper
 */
class RelationDef {

    const HasOne = 1;
    const HasMany = 2;

    private $parentClass;
    private $name;
    private $class;
    private $otherField;
    private $joinSelfAs;
    private $joinOtherAs;
    private $joinTable;
    private $cascadeDelete = true;
    private $type;

    /**
     * RelationDef constructor.
     * @param Model $model
     * @param string $name
     * @param string|array $data
     * @param int $type
     */
    public function __construct($model, $name, $data, $type) {
        $this->setParentClass($model);
        $this->setType($type);
        if(is_string($data)) {
            $this->setName($data);
            $this->setClass($data);
        } else if(is_array($data)) {
            $this->setName($name);
            if(isset($data['class'])) {
                $this->setClass($data['class']);
            } else {
                $this->setClass($name);
            }
            if(isset($data['otherField'])) {
                $this->setOtherField($data['otherField']);
            }
            if(isset($data['joinSelfAs'])) {
                $this->setJoinSelfAs($data['joinSelfAs']);
            } else if($type == self::HasOne) {
                $this->setJoinSelfAs($name . '_id');
            }
            if(isset($data['joinOtherAs'])) {
                $this->setJoinOtherAs($data['joinOtherAs']);
            }
            if(isset($data['joinTable'])) {
                $this->setJoinTable($data['joinTable']);
            }
            if(isset($data['cascadeDelete'])) {
                $this->setCascadeDelete($data['cascadeDelete']);
            }
        }

        if(!isset($this->otherField)) {
            $this->setOtherField(get_class($model));
        }
        if(!isset($this->joinSelfAs)) {
            $this->setJoinSelfAs($this->getSimpleOtherField().'_id');
        }
        if(!isset($this->joinOtherAs)) {
            $this->setJoinOtherAs($this->getSimpleName().'_id');
        }

        if(!isset($this->joinTable)) {
            $relationClassName = $this->getClass();
            /** @var Model $relationClass */
            $relationClass = new $relationClassName();
            if(! $relationClass instanceof Model) {
                throw new Exception("Invalid relation {$this->getName()} for ".get_class($model));
            }
            $joins = [$model->getTableName(), $relationClass->getTableName()];
            sort($joins);
            $this->setJoinTable(strtolower(implode('_', $joins)));
        }
    }

    public function getRelationShipTable() {
        $parent = $this->getParent();
        $related = $this->getRelationClass();

        // See if the relationship is in parent table
        if(array_key_exists($this->getName(), $parent->hasOne)
            || in_array($this->getName(), $parent->hasOne)) {
            if(in_array($this->getJoinOtherAs(), $parent->getTableFields())) {
                return $parent->getTableName();
            }
        }

        if(array_key_exists($this->getOtherField(), $related->hasOne)
            || in_array($this->getOtherField(), $related->hasOne)) {
            if(in_array($this->getJoinSelfAs(), $related->getTableFields()))
                return $related->getTableName();
        }

        // No? Then it must be a join table
        return $this->getJoinTable();
    }

    private $relationClass;
    public function getRelationClass(): Model {
        if(is_null($this->relationClass)) {
            $className = $this->getClass();
            $this->relationClass = new $className();
        }
        return $this->relationClass;
    }

    public function getSimpleName() {
        $namespace = explode('\\', $this->getName());
        $trim = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', end($namespace)));
        if(strpos($trim, '_model') !== false)
            return substr($trim, 0, -6);
        else
            return $trim;
    }

    public function getEntityName() {
        return substr(str_replace('Models', 'Entities', $this->getClass()), 0, -5);
    }

    public function getSimpleOtherField() {
        $namespace = explode('\\', $this->getOtherField());
        $trim = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', end($namespace)));
        if(strpos($trim, '_model') !== false)
            return substr($trim, 0, -6);
        else
            return $trim;
    }

    // <editor-fold desc="Getter & setters">

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getClass(): string {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class): void {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getOtherField(): string {
        return $this->otherField;
    }

    /**
     * @param string $otherField
     */
    public function setOtherField(string $otherField): void {
        $this->otherField = $otherField;
    }

    /**
     * @return string
     */
    public function getJoinSelfAs(): string {
        return $this->joinSelfAs;
    }

    /**
     * @param string $joinSelfAs
     */
    public function setJoinSelfAs(string $joinSelfAs): void {
        $this->joinSelfAs = $joinSelfAs;
    }

    /**
     * @return string
     */
    public function getJoinOtherAs(): string {
        return $this->joinOtherAs;
    }

    /**
     * @param string $joinOtherAs
     */
    public function setJoinOtherAs(string $joinOtherAs): void {
        $this->joinOtherAs = $joinOtherAs;
    }

    /**
     * @return string
     */
    public function getJoinTable(): string {
        return $this->joinTable;
    }

    /**
     * @param string $joinTable
     */
    public function setJoinTable(string $joinTable): void {
        $this->joinTable = $joinTable;
    }

    /**
     * @return bool
     */
    public function isCascadeDelete(): bool {
        return $this->cascadeDelete;
    }

    /**
     * @param bool $cascadeDelete
     */
    public function setCascadeDelete(bool $cascadeDelete): void {
        $this->cascadeDelete = $cascadeDelete;
    }

    /**
     * @return int
     */
    public function getType(): int {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void {
        $this->type = $type;
    }

    /**
     * @return Model
     */
    public function getParent(): Model {
        return new $this->parentClass;
    }

    /**
     * @param Model $parent
     */
    public function setParentClass(Model $parent): void {
        $this->parentClass = get_class($parent);
    }

    // </editor-fold>
}
