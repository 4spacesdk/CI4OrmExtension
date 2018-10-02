<?php namespace OrmExtension\DataMapper;

use ArrayIterator;
use Config\Database;
use OrmExtension\Data;
use OrmExtension\Extensions\Entity;
use OrmExtension\Extensions\Model;
use Traversable;

trait EntityTrait {

    abstract function getModel(): Model;

    public function exists() {
        return $this->id > 0;
    }

    // <editor-fold desc="Find">

    public function find($id = null) {
        $entity = $this->getModel()->find($id);
        foreach(get_object_vars($entity) as $name => $value)
            $this->{$name} = $value;
        //$entity = null;
    }

    // </editor-fold>

    // <editor-fold desc="Save (Insert/Update)">

    /**
     * @param Entity|null $related
     * @param string|null $relatedField
     */
    public function save($related = null, $relatedField = null) {
        if($related instanceof Entity) {
            if($related->exists()) {
                $this->saveRelation($related, $relatedField);
            }
        } else {
            $result = $this->getModel()->save($this);
            if(is_numeric($result))
                $this->id = $result;
        }
    }

    /**
     * @param Entity $related
     * @param string|null $relationName
     */
    public function saveRelation($related, $relationName = null) {
        if(!$this->exists() |! $related->exists()) return;

        if(!$relationName) $relationName = get_class($related->getModel());

        $thisModel = $this->getModel();
        $relatedModel = $related->getModel();

        $relation = $thisModel->getRelation($relationName);
        if(empty($relation)) return;
        $relation = $relation[0];

        $relationShipTable = $relation->getRelationShipTable();

        if($relationShipTable == $thisModel->getTableName()) {
            if(in_array($relation->getJoinOtherAs(), $thisModel->getTableFields())) {

                // Check if opposite relation is hasOne
                $opposite = $relatedModel->getRelation($relation->getOtherField());
                if(!empty($opposite) && $opposite[0]->getType() == RelationDef::HasOne)
                    $related->deleteRelation($relation->getOtherField(), $relation->getOtherField());

                $this->{$relation->getJoinOtherAs()} = $related->id;
                $this->save();
            }
        } else if($relationShipTable == $relatedModel->getTableName()) {
            if(in_array($relation->getJoinSelfAs(), $relatedModel->getTableFields())) {

                // Check if this relation is hasOne
                if($relation->getType() == RelationDef::HasOne)
                    $this->deleteRelation($related, $relationName);

                $related->{$relation->getJoinSelfAs()} = $this->id;
                $related->save();
            }
        } else {

            Database::connect()
                ->table($relationShipTable)
                ->insert([
                    $relation->getJoinSelfAs()  => $this->id,
                    $relation->getJoinOtherAs() => $related->id
                ]);

        }
    }

    public function resetStoredFields() {
        foreach($this->getModel()->getTableFields() as $field)
            $this->stored[$field] = $this->{$field};
    }

    // </editor-fold>

    // <editor-fold desc="Delete">

    /**
     * @param Entity|null $related
     */
    public function delete($related = null) {
        if($this->exists()) {
            if(is_null($related))
                $this->getModel()->delete($this->id);
            $this->deleteRelation($related);
        }
    }

    /**
     * @param Entity|string $related
     * @param string|null $relationName
     */
    public function deleteRelation($related, $relationName = null) {
        if(!$relationName) $relationName = get_class($related->getModel());

        $thisModel = $this->getModel();
        if(is_string($related))
            $relatedModel = new $related();
        else
            $relatedModel = $related->getModel();

        $relation = $thisModel->getRelation($relationName);
        if(empty($relation)) return;
        $relation = $relation[0];

        $relationShipTable = $relation->getRelationShipTable();

        if($relationShipTable == $thisModel->getTableName()) {
            if(in_array($relation->getJoinOtherAs(), $thisModel->getTableFields())) {
                $this->{$relation->getJoinOtherAs()} = null;
                $this->save();
            }
        } else if($relationShipTable == $relatedModel->getTableName()) {
            if(in_array($relation->getJoinSelfAs(), $relatedModel->getTableFields())) {
                if(is_string($related)) {
                    // TODO Handle updated and updated_by_id
                    Database::connect()
                        ->table($relationShipTable)
                        ->update(
                            [$relation->getJoinSelfAs() => 0],
                            [$relation->getJoinSelfAs() => $this->id]);
                } else {
                    $related->{$relation->getJoinSelfAs()} = null;
                    $related->save();
                }
            }
        } else {

            Database::connect()
                ->table($relationShipTable)
                ->delete([
                    $relation->getJoinSelfAs()  => $this->id,
                    $relation->getJoinOtherAs() => $related->id
                ]);

        }

        unset($this->{$relation->getSimpleName()});
        $this->resetStoredFields();
    }

    // </editor-fold>

    // <editor-fold desc="Print">

    public function allToArray() {
        $items = [];
        foreach($this as $item)
            $items[] = $item->toArray();
        return $items;
    }

    public function toArray(): array {
        $item = [];

        // Fields
        $fields = ModelDefinitionCache::getFieldData($this->getSimpleName());
        foreach($fields as $fieldData) {
            $field = $fieldData->name;
            switch($fieldData->type) {
                case 'int':
                    $item[$field] = (int)$this->{$field};
                    break;
                case 'float':
                case 'double':
                case 'decimal':
                    $item[$field] = (double)$this->{$field};
                    break;
                case 'tinyint':
                    $item[$field] = (bool)$this->{$field};
                    break;
                case 'varchar':
                case 'text':
                case 'time':
                    $item[$field] = (string)$this->{$field};
                    break;
                case 'datetime':
                    $item[$field] = (string)strtotime($this->{$field});
                    break;
                default:
                    $item[$field] = $this->{$field};
            }
        }

        // Relations
        /** @var RelationDef[] $relations */
        $relations = ModelDefinitionCache::getRelations($this->getSimpleName());
        foreach($relations as $relation) {
            $field = $relation->getSimpleName();
            switch($relation->getType()) {
                case RelationDef::HasOne:
                    if(isset($this->{$field}) && $this->{$field}->exists())
                        $item[$field] = $this->{$field}->toArray();
                    break;
                case RelationDef::HasMany:
                    $field = plural($field);
                    if(isset($this->{$field}) && $this->{$field}->exists())
                        $item[$field] = $this->{$field}->allToArray();
                    break;
            }
        }


        return $item;
    }

    // </editor-fold>

    // <editor-fold desc="Entity as an array">

    public function count() {
        return isset($this->all) ? count($this->all) : 1;
    }

    public function add($item) {
        if(!isset($this->all)) $this->all = [];
        $this->all[] = $item;
    }

    /**
     * @return ArrayIterator|Traversable|Entity[]
     */
    public function getIterator() {
        return new ArrayIterator(isset($this->all) ? $this->all : []);
    }

    // </editor-fold>

}