<?php namespace OrmExtension\DataMapper;

use OrmExtension\Data;
use OrmExtension\Extensions\Entity;
use OrmExtension\Extensions\Model;

/**
 * Trait ResultBuilder
 * @package OrmExtension\DataMapper
 * @property RelationDef[] $includedRelations
 */
trait ResultBuilder {

    protected $includedRelations = [];

    /**
     * @param string $fullName
     * @param RelationDef $relation
     */
    protected function addIncludedRelation($fullName, $relation) {
        $this->includedRelations[$fullName] = $relation;
    }

    /**
     * @param Entity[] $result
     */
    protected function arrangeIncludedRelations(&$result) {
        foreach($result as $row) {

            $row->resetStoredFields();

            $current = $row;
            foreach($this->includedRelations as $fullName => $relation) {

                $entityName = $relation->getEntityName();
                /** @var Entity $entity */
                $entity = new $entityName();

                foreach($relation->getRelationClass()->getTableFields() as $field) {
                    $fieldName = "{$fullName}{$field}";
                    if(isset($row->{$fieldName})) {
                        $entity->{$field} = $row->{$fieldName};
                    }
                }
                if(!$entity->exists()) continue;
                $entity->resetStoredFields();

                $relationName = $relation->getSimpleName();
                switch($relation->getType()) {
                    case RelationDef::HasOne:
                        $current->{$relationName} = $entity;
                        break;
                    case RelationDef::HasMany:
                        $relationName = plural($relationName);
                        if(!isset($current->{$relationName})) {
                            $current->{$relationName} = clone $entity;
                            $current->{$relationName}->all = [];
                        }
                        $current->{$relationName}->all[] = $entity;
                        break;
                }

                $current = $entity;
            }

        }
    }

}