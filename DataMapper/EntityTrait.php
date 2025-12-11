<?php namespace OrmExtension\DataMapper;

use ArrayIterator;
use Config\Database;
use DateTime;
use DateTimeZone;
use OrmExtension\Extensions\Entity;
use OrmExtension\Extensions\Model;
use OrmExtension\Interfaces\OrmEventsInterface;
use Traversable;
use Config\OrmExtension;

trait EntityTrait {

    abstract function _getModel(): Model;

    public function exists() {
        $primaryKey = $this->_getModel()->getPrimaryKey();
        return !empty($this->{$primaryKey}) || (!is_null($this->all) && count($this->all));
    }

    // <editor-fold desc="Find">

    public function find($id = null) {
        $entity = $this->_getModel()->find($id);
        foreach(get_object_vars($entity) as $name => $value)
            $this->{$name} = $value;
        return $entity;
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
                foreach ($related as $relatedItem) {
                    $this->saveRelation($relatedItem, $relatedField);
                }
            }
        } else {
            $result = $this->_getModel()->save($this);
            if(!is_bool($result))
                $this->{$this->_getModel()->getPrimaryKey()} = $result;
        }
    }

    public function insert() {
        $result = $this->_getModel()->insert($this);
        if(!is_bool($result))
            $this->{$this->_getModel()->getPrimaryKey()} = $result;
    }

    /**
     * @param Entity $related
     * @param string|null $relationName
     */
    public function saveRelation($related, $relationName = null) {
        if(!$this->exists() |! $related->exists()) return;

        if(!$relationName) $relationName = get_class($related->_getModel());

        $thisModel = $this->_getModel();
        $relatedModel = $related->_getModel();

        $relation = $thisModel->getRelation($relationName);
        if(empty($relation)) return;
        $relation = $relation[0];

        $relationShipTable = $relation->getRelationShipTable();

        if($relationShipTable == $thisModel->getTableName()) {
            if(in_array($relation->getJoinOtherAs(), $thisModel->getTableFields())) {

                // Check if opposite relation is hasOne
                $opposite = $relatedModel->getRelation($relation->getOtherField());
                if(!empty($opposite) && $opposite[0]->getType() == RelationDef::HasOne) {
                    $related->deleteRelation($this, $relation->getOtherField());
                }

                $this->{$relation->getJoinOtherAs()} = $related->{$relatedModel->getPrimaryKey()};
                $this->save();
            }
        } else if($relationShipTable == $relatedModel->getTableName()) {
            if(in_array($relation->getJoinSelfAs(), $relatedModel->getTableFields())) {

                // Check if this relation is hasOne
                if($relation->getType() == RelationDef::HasOne) {
                    $this->deleteRelation($related, $relationName);
                }

                $related->{$relation->getJoinSelfAs()} = $this->{$thisModel->getPrimaryKey()};
                $related->save();
            }
        } else {

            Database::connect()
                ->table($relationShipTable)
                ->insert([
                    $relation->getJoinSelfAs()  => $this->{$thisModel->getPrimaryKey()},
                    $relation->getJoinOtherAs() => $related->{$relatedModel->getPrimaryKey()}
                ]);

        }

        if($thisModel instanceof OrmEventsInterface && $this instanceof Entity && $related instanceof Entity) {
            $thisModel->postAddRelation($this, $related);
        }
    }

    // </editor-fold>

    // <editor-fold desc="Delete">

    public function deleteAll() {
        /** @var Entity $item */
        foreach($this as $item) $item->delete();
    }

    /**
     * @param Entity|null $related
     */
    public function delete($related = null) {
        if($this->exists()) {
            if(is_null($related)) {

                $thisModel = $this->_getModel();
                if(in_array('deletion_id', $this->_getModel()->getTableFields())) {
                    foreach(OrmExtension::$entityNamespace as $entityNamespace) {
                        $name = $entityNamespace . 'Deletion';
                        if(class_exists($name)) {
                            /** @var Entity $deletion */
                            $deletion = new $name();
                            $deletion->save();
                            $this->deletion_id = $deletion->{$deletion->_getModel()->getPrimaryKey()};
                            $this->save();
                            break;
                        }
                    }
                } else
                    $thisModel->delete($this->{$thisModel->getPrimaryKey()});

                if($thisModel instanceof OrmEventsInterface && $this instanceof Entity)
                    $thisModel->postDelete($this);

            } else {
                $this->deleteRelation($related);
            }
        }
    }

    /**
     * @param Entity|string $related
     * @param string|null $relationName
     */
    public function deleteRelation($related, $relationName = null) {
        if(!$relationName) $relationName = get_class($related->_getModel());

        $thisModel = $this->_getModel();
        if(is_string($related))
            $relatedModel = new $related();
        else
            $relatedModel = $related->_getModel();

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
                            [$relation->getJoinSelfAs() => $this->{$thisModel->getPrimaryKey()}]);
                } else {
                    $related->{$relation->getJoinSelfAs()} = null;
                    $related->save();
                }
            }
        } else {

            Database::connect()
                ->table($relationShipTable)
                ->delete([
                    $relation->getJoinSelfAs()  => $this->{$thisModel->getPrimaryKey()},
                    $relation->getJoinOtherAs() => $related->{$relatedModel->getPrimaryKey()}
                ]);

        }

        unset($this->{$relation->getSimpleName()});
        //$this->resetStoredFields();

        if($thisModel instanceof OrmEventsInterface && $this instanceof Entity && $related instanceof Entity) {
            $thisModel->postDeleteRelation($this, $related);
        }
    }

    // </editor-fold>

    // <editor-fold desc="Print">

    public function allToArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false, ?array $fieldsFilter = null) {
        $items = [];
        foreach($this as $item)
            $items[] = $item->toArray($onlyChanged, $cast, $recursive, $fieldsFilter);
        return $items;
    }

    public function allToArrayWithFields(?array $fieldsFilter = null, bool $onlyChanged = false, bool $cast = true, bool $recursive = false) {
        return $this->allToArray($onlyChanged, $cast, $recursive, $fieldsFilter);
    }

    public function toArray(bool $onlyChanged = false, bool $cast = true, bool $recursive = false, ?array $fieldsFilter = null): array {
        $item = [];

        // Fields
        $fields = ModelDefinitionCache::getFieldData($this->getSimpleName());
        foreach($fields as $fieldData) {
            $fieldName = $fieldData->name;

            if(in_array($fieldName, $this->hiddenFields)) {
                continue;
            }

            if ($fieldsFilter != null && !in_array($fieldName, $fieldsFilter)) {
                continue;
            }

            $field = $this->{$fieldName};
            if (is_string($field)) {
                switch($fieldData->type) {
                    case 'bigint':
                    case 'int':
                        $item[$fieldName] = is_null($this->{$fieldName}) ? null : (int)$this->{$fieldName};
                        break;
                    case 'float':
                    case 'double':
                    case 'decimal':
                        $item[$fieldName] = (double)$this->{$fieldName};
                        break;
                    case 'tinyint':
                        $item[$fieldName] = (bool)$this->{$fieldName};
                        break;
                    case 'varchar':
                    case 'text':
                    case 'time':
                        $item[$fieldName] = (string)$this->{$fieldName};
                        break;
                    case 'datetime':
                        if($this->{$fieldName} != null && $this->{$fieldName} != "0000-00-00 00:00:00") {
                            $item[$fieldName] = (string)strtotime($this->{$fieldName});
                            try {
                                $foo = new DateTime($this->{$fieldName}, new DateTimeZone("Europe/Copenhagen"));
                                $foo->setTimeZone(new DateTimeZone("UTC"));
                                $item[$fieldName] = $foo->format('c');
                            } catch(\Exception $e) {

                            }
                        } else $item[$fieldName] = null;
                        break;
                    default:
                        $item[$fieldName] = $this->{$fieldName};
                }
            } else {
                $item[$fieldName] = $this->{$fieldName};
            }
        }

        // Relations
        /** @var RelationDef[] $relations */
        $relations = ModelDefinitionCache::getRelations($this->getSimpleName());
        foreach($relations as $relation) {
            $fieldName = $relation->getSimpleName();
            switch($relation->getType()) {
                case RelationDef::HasOne:
                    if(isset($this->{$fieldName}) && $this->{$fieldName}->exists())
                        $item[$fieldName] = $this->{$fieldName}->toArray();
                    break;
                case RelationDef::HasMany:
                    $fieldName = plural($fieldName);
                    if(isset($this->{$fieldName}) && $this->{$fieldName}->exists())
                        $item[$fieldName] = $this->{$fieldName}->allToArray();
                    break;
            }
        }


        return $item;
    }

    // </editor-fold>

    // <editor-fold desc="Entity as an array">

    public function count() {
        return !is_null($this->all) ? count($this->all) : ($this->exists() ? 1 : 0);
    }

    public function add($item) {
        if(is_null($this->all)) $this->all = [];
        $this->all[] = $item;
        $this->idMap = null;
    }

    public function remove($item) {
        if(is_null($this->all)) $this->all = [];
        if(($key = array_search($item, $this->all)) !== false) {
            unset($this->all[$key]);
        }
        $this->idMap = null;
    }

    public function removeById($id) {
        $item = $this->getById($id);
        if($item) $this->remove($item);
    }

    private $idMap = null; // Initialized when needed
    private function initIdMap() {
        $this->idMap = [];
        $primaryKey = $this->_getModel()->getPrimaryKey();
        foreach($this as $item) $this->idMap[$item->{$primaryKey}] = $item;
    }
    public function getById($id) {
        if(is_null($this->idMap)) $this->initIdMap();
        return isset($this->idMap[$id]) ? $this->idMap[$id] : null;
    }

    public function hasId($id) {
        if(is_null($this->idMap)) $this->initIdMap();
        return isset($this->idMap[$id]);
    }

    public function clear() {
        $this->all = [];
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator {
        return new ArrayIterator(!is_null($this->all) ? $this->all : ($this->exists() ? [$this] : []));
    }

    // </editor-fold>


    public function getTableFields() {
        return $this->_getModel()->getTableFields();
    }

    public function resetStoredFields() {
        foreach($this->getTableFields() as $field) {
            $this->stored[$field] = $this->{$field};
        }
    }

}
