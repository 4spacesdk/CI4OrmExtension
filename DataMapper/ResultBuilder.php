<?php namespace OrmExtension\DataMapper;

use DebugTool\Data;
use OrmExtension\Extensions\Entity;
use OrmExtension\Extensions\Model;

/**
 * Trait ResultBuilder
 * @package DebugTool\DataMapper
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

    protected function hasIncludedRelation(string $fullName): bool {
        return isset($this->includedRelations[$fullName]);
    }

    /**
     * @param Entity[] $result
     */
    protected function arrangeIncludedRelations(&$result) {
        //Data::debug(get_class($this), "arrangeIncludedRelations for", count($result), 'entities with', count($this->includedRelations), 'relations');

        $relations = $this->includedRelations;
        ksort($relations);

        foreach($result as $row) {
            //$row->resetStoredFields(); // TODO Brug CI's

            foreach($relations as $relationPrefix => $relation) {
                $fullName = str_replace('/', '_', $relationPrefix);

                // Deep relation
                $current = $row;
                $deepRelations = explode('/', trim($relationPrefix, '/'));
                array_pop($deepRelations);
                foreach($deepRelations as $prefix) {
                    if($relation->getType() == RelationDef::HasOne) {
                        $current = $current->{singular($prefix)};
                    } else {
                        $current = $current->{$prefix};
                    }
                }

                $entityName = $relation->getEntityName();
                /** @var Entity $entity */
                $entity = new $entityName();

                $attributes = [];
                foreach($relation->getRelationClass()->getTableFields() as $field) {
                    $fieldName = "{$fullName}{$field}";
                    if(isset($row->{$fieldName})) {
                        $attributes[$field] = $row->{$fieldName};
                    }
                }
                $entity->setAttributes($attributes);
                if(!$entity->exists()) continue;
                //$entity->resetStoredFields();

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

            }

        }

    }

}
