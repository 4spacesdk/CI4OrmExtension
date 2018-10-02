<?php namespace OrmExtension\DataMapper;

use OrmExtension\Data;
use OrmExtension\Extensions\Model;

trait QueryBuilder {

    abstract protected function _getModel(): Model;

    // <editor-fold desc="Include Related">

    /**
     * @param string|array $relationName
     * @return Model
     */
    public function includeRelated($relationName): Model {
        $parent = $this->_getModel();
        $relations = $this->getRelation($relationName);

        // Handle deep relations
        $last = $this;
        $table = null;
        $prefix = '';
        foreach($relations as $relation) {
            $table = $last->addRelatedTable($relation, $prefix, $table);
            $prefix .= plural($relation->getSimpleName()).'_';

            // Prepare next
            $builder = $last->_getBuilder();
            $selecting = $last->isSelecting();
            $relatedTablesAdded =& $last->relatedTablesAdded;
            $last = $relation->getRelationClass();
            $last->_setBuilder($builder);
            $last->setSelecting($selecting);
            $last->relatedTablesAdded =& $relatedTablesAdded;

            $parent->addIncludedRelation($prefix, $relation);
            $this->selectIncludedRelated($parent, $relation, $table, $prefix);
        }

        return $parent;
    }

    /**
     * @param Model $parent
     * @param RelationDef $relation
     * @param string $table
     * @param string $prefix
     */
    private function selectIncludedRelated($parent, $relation, $table, $prefix) {
        $selection = [];

        $relationClassName = $relation->getClass();
        /** @var Model $related */
        $related = new $relationClassName();

        $fields = $related->getTableFields();
        foreach($fields as $field) {
            $new_field = $prefix . $field;

            // Prevent collisions
            if(in_array($new_field, $parent->getTableFields())) continue;

            $selection[] = "{$table}.{$field} AS {$new_field}";
        }

        $this->select(implode(', ',$selection));
    }

    // </editor-fold>

    // <editor-fold desc="Where">

    public function whereRelated($relationName, $field, $value, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->where("{$table}.{$field}", $value, $escape, false);
        return $model;
    }

    public function whereInRelated($relationName, $field, $value, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->whereIn("{$table}.{$field}", $value, $escape, false);
        return $model;
    }

    public function whereNotInRelated($relationName, $field, $value, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->whereNotIn("{$table}.{$field}", $value, $escape, false);
        return $model;
    }

    public function whereBetweenRelated($relationName, $field, $min, $max, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->whereBetween("{$table}.{$field}", $min, $max, $escape, false);
        return $model;
    }

    public function whereNotBetweenRelated($relationName, $field, $min, $max, $escape = false): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->whereNotBetween("{$table}.{$field}", $min, $max, $escape, false);
        return $model;
    }

    public function orWhereRelated($relationName, $field, $value, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->orWhere("{$table}.{$field}", $value, $escape, false);
        return $model;
    }

    public function orWhereInRelated($relationName, $field, $value, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->orWhereIn("{$table}.{$field}", $value, $escape, false);
        return $model;
    }

    public function orWhereNotInRelated($relationName, $field, $value, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->orWhereNotIn("{$table}.{$field}", $value, $escape, false);
        return $model;
    }

    // </editor-fold>

    // <editor-fold desc="Like">

    public function likeRelated($relationName, $field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->like("{$table}.{$field}", $match, $side, $escape, $insensitiveSearch, false);
        return $model;
    }

    public function notLikeRelated($relationName, $field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->notLike("{$table}.{$field}", $match, $side, $escape, $insensitiveSearch, false);
        return $model;
    }

    public function orLikeRelated($relationName, $field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->orLike("{$table}.{$field}", $match, $side, $escape, $insensitiveSearch, false);
        return $model;
    }

    public function orNotLikeRelated($relationName, $field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->orNotLike("{$table}.{$field}", $match, $side, $escape, $insensitiveSearch, false);
        return $model;
    }

    // </editor-fold>

    // <editor-fold desc="Sub Query">

    /**
     * @param Model $query
     * @param string $alias
     * @return Model
     */
    public function selectSubQuery($query, $alias) {
        $model = $this->_getModel();
        $query = $this->parseSubQuery($query);
        $model->select("{$query} AS {$alias}");
        return $model;
    }

    /**
     * @param Model $query
     * @param null $value
     * @return Model
     */
    public function whereSubQuery($query, $value = null) {
        $model = $this->_getModel();
        $field = $this->parseSubQuery($query);
        $model->where($field, $value, null, false);
        return $model;
    }

    /**
     * @param Model $query
     * @return mixed
     */
    protected function parseSubQuery($query) {
        $model = $this->_getModel();

        $query->bindReplace('${parent}', $this->getTableName());

        $sql = $query->get(0, 0, true);
        $sql = "({$sql})";

        //Data::sql($sql);

        $this->bindMerging($sql, $query->getBinds());

        $tableName = $model->db->protectIdentifiers($model->getTableName());
        $tableNameThisQuote = preg_quote($model->getTableName());
        $tableNameQuote = preg_quote($tableName);
        $tablePattern = "(?:{$tableNameThisQuote}|{$tableNameQuote}|\({$tableNameQuote}\))";

        $fieldName = $model->db->protectIdentifiers('__field__');
        $fieldName = str_replace('__field__', '[-\w]+', preg_quote($fieldName));
        $fieldPattern = "([-\w]+|{$fieldName})";

        // Pattern ends up being [^_](table|`table`).(field|`field`)
        $pattern = "/([^_:]){$tablePattern}\.{$fieldPattern}/i";

        // Replacement ends up being `table_subquery`.`$1`
        $tableSubQueryName = $model->db->protectIdentifiers($model->getTableName() . '_subquery');
        $replacement = "$1{$tableSubQueryName}.$2";
        $sql = preg_replace($pattern, $replacement, $sql);

        // Replace all "table table" aliases
        $pattern = "/{$tablePattern} {$tablePattern} /i";
        $replacement = "{$tableName} {$tableSubQueryName} ";
        $sql = preg_replace($pattern, $replacement, $sql);

        // Replace "FROM table" for self relationships
        $pattern = "/FROM {$tablePattern}([,\\s])/i";
        $replacement = "FROM {$tableName} $tableSubQueryName$1";
        $sql = preg_replace($pattern, $replacement, $sql);
        $sql = str_replace("\n", "\n\t", $sql);

        //Data::sql($sql);

        return str_replace('${parent}', $this->getTableName(), $sql);
    }

    // </editor-fold>

    // <editor-fold desc="Group by, Having, Order By">

    public function groupByRelated($relationName, $by, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->groupBy("{$table}.{$by}", $escape, false);
        return $model;
    }

    public function havingRelated($relationName, $key, $value, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->having("{$table}.{$key}", $value, $escape, false);
        return $model;
    }

    public function orHavingRelated($relationName, $key, $value, $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->orHaving("{$table}.{$key}", $value, $escape, false);
        return $model;
    }

    public function orderByRelated($relationName, $orderby, $direction = '', $escape = null): Model {
        $table = $this->handleWhereRelated($relationName);
        $model = $this->_getModel();
        $model->orderBy("{$table}.{$orderby}", $direction, $escape, false);
        return $model;
    }


    // </editor-fold>


    // <editor-fold desc="Fields">

    public function getTableFields() {
        $entityName = $this->_getModel()->getEntityName();
        $fields = ModelDefinitionCache::getFields($entityName);
        return $fields;
    }

    // </editor-fold>

    // <editor-fold desc="Relations">

    private function handleWhereRelated($relationName) {
        $relations = $this->getRelation($relationName);

        // Handle deep relations
        $last = $this;
        $table = null;
        /** @var RelationDef $relation */
        $relation = null;
        $prefix = '';
        foreach($relations as $relation) {
            $table = $last->addRelatedTable($relation, $prefix, $table);
            $prefix .= plural($relation->getSimpleName()).'_';

            // Prepare next
            $builder = $last->_getBuilder();
            $selecting = $last->isSelecting();
            $relatedTablesAdded =& $last->relatedTablesAdded;
            $last = $relation->getRelationClass();
            $last->_setBuilder($builder);
            $last->setSelecting($selecting);
            $last->relatedTablesAdded =& $relatedTablesAdded;
        }

        return $table;
    }

    private $relatedTablesAdded = [];

    /**
     * @param RelationDef $relation
     * @param string $prefix
     * @param string $this_table
     * @return string
     */
    public function addRelatedTable(RelationDef $relation, $prefix = '', $this_table = null) {
        if(!$this_table) $this_table = $this->getTableName();
        //Data::debug("QueryBuilder::addRelatedTable", 'Name='.$relation->getSimpleName(), 'Prefix='.$prefix, 'Table='.$this_table);

        $related = $relation->getRelationClass();
        $relationShipTable = $relation->getRelationShipTable();

        // If no selects, select this table
        if(!$this->isSelecting()) $this->select($this->getTableName(). '.*');


        if(($relation->getClass() == $relation->getName()) && ($this->getTableName() != $related->getTableName())) {
            $prefixedParentTable = $prefix . $related->getTableName();
            $prefixedRelatedTable = $prefix . $relationShipTable;
        } else { // Used when relation is custom named
            $prefixedParentTable = $prefix . plural($relation->getSimpleName()) . '_' . $related->getTableName();
            $prefixedRelatedTable = $prefix . plural($relation->getSimpleName()) . '_' . $relationShipTable;
        }


        if($relationShipTable == $this->getTableName() && in_array($relation->getJoinOtherAs(), $this->getTableFields())) {

            if(!in_array($prefixedParentTable, $this->relatedTablesAdded)) {
                $cond = "{$prefixedParentTable}.id = {$this_table}.{$relation->getJoinOtherAs()}";
                $this->join("{$related->getTableName()} {$prefixedParentTable}", $cond, 'LEFT OUTER');

                $this->relatedTablesAdded[] = $prefixedParentTable;
            }

        } else if($relationShipTable == $related->getTableName() && in_array($relation->getJoinSelfAs(), $related->getTableFields())) {

            if(!in_array($prefixedParentTable, $this->relatedTablesAdded)) {
                $cond = "{$this_table}.id = {$prefixedParentTable}.{$relation->getJoinSelfAs()}";
                $this->join("{$related->getTableName()} {$prefixedParentTable}", $cond, 'LEFT OUTER');

                $this->relatedTablesAdded[] = $prefixedParentTable;
            }

        } else {

            if(!in_array($prefixedRelatedTable, $this->relatedTablesAdded)) {
                $cond = "{$this_table}.id = {$prefixedRelatedTable}.{$relation->getJoinSelfAs()}";
                $this->join("{$relationShipTable} {$prefixedRelatedTable}", $cond, 'LEFT OUTER');

                $this->relatedTablesAdded[] = $prefixedRelatedTable;
            }

            if(!in_array($prefixedParentTable, $this->relatedTablesAdded)) {
                $cond = "{$prefixedParentTable}.id = {$prefixedRelatedTable}.{$relation->getJoinOtherAs()}";
                $this->join("{$related->getTableName()} {$prefixedParentTable}", $cond, 'LEFT OUTER');

                $this->relatedTablesAdded[] = $prefixedParentTable;
            }

        }

        return $prefixedParentTable;
    }

    /**
     * @param string|array $name
     * @return RelationDef[]
     */
    public function getRelation($name) {
        // Handle deep relations
        if(is_array($name)) {
            $last = $this;
            $result = [];
            foreach($name as $ref) {
                $relations = $last->getRelation($ref);
                if(count($relations) == 0) {
                    // TODO
                    //throw new \Exception("Failed to find relation $name for " . get_class($this));
                }
                $relation = $relations[0];
                $last = $relation->getRelationClass();
                $result[] = $relation;
            }
            return $result;
        }

        foreach($this->getRelations() as $relation) {
            if($relation->getName() == $name)
                return [$relation];
        }

        // TODO
        //throw new \Exception("Failed to find relation $name for " . get_class($this));
    }

    /**
     * @return RelationDef[]
     */
    public function getRelations() {
        $entityName = $this->_getModel()->getEntityName();
        $relations = ModelDefinitionCache::getRelations($entityName);
        return $relations;
    }

    // </editor-fold>

}