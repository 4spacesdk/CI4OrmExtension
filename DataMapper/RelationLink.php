<?php namespace OrmExtension\DataMapper;

use OrmExtension\Extensions\Model;

/**
 * Which columns tie a model's rows to a relation's rows. `addRelatedTable()` joins by it, so
 * anything else that needs the same rows - a sub query, a list of keys - finds exactly the ones
 * the join does:
 *
 * - InBase: the model holds the key, `orders.buyer_workspace_id = workspaces.id`. A named
 *   relation, a self relation and a key that is not the id
 *   (`order_lines.product_no = products.product_no`) are all this.
 * - InRelated: the related table holds it, `workspaces.id = orders.buyer_workspace_id`.
 * - ThroughTable: a join table between them, `workspaces.id = users_workspaces.workspace_id`
 *   and `users_workspaces.user_id = users.id`.
 */
class RelationLink {

    const InBase = 'base';
    const InRelated = 'related';
    const ThroughTable = 'table';

    /** @var string */
    public $kind;

    /** @var string the column of the model's table */
    public $baseColumn;

    /** @var string the column of the related table: the key, or its primary key for ThroughTable */
    public $relatedColumn;

    /** @var string|null the join table, for ThroughTable */
    public $table = null;

    /** @var string|null the join table's column that holds the model's key */
    public $tableBaseColumn = null;

    /** @var string|null the join table's column that holds the related row's key */
    public $tableRelatedColumn = null;

    /**
     * @var bool whether the join table was found by guessing a column of the model's own, as
     * opposed to falling back to its primary key. Only the first kind of join leaves out a
     * deleted related row; kept as it was.
     */
    public $guessed = false;

    /**
     * @param Model $base the model the relation belongs to
     * @param RelationDef $relation
     * @return RelationLink|null null when neither table has the key its definition names
     */
    public static function of(Model $base, RelationDef $relation): ?RelationLink {
        $related = $relation->getRelationClass();
        $baseFields = $base->getTableFields();
        $relatedFields = $related->getTableFields();
        $relationShipTable = $relation->getRelationShipTable();

        $link = new RelationLink();

        if ($relationShipTable == $base->getTableName() && in_array($relation->getJoinOtherAs(), $baseFields)) {
            foreach ([$relation->getJoinSelfAs(), 'id'] as $column) {
                if (in_array($column, $relatedFields)) {
                    $link->kind = self::InBase;
                    $link->baseColumn = $relation->getJoinOtherAs();
                    $link->relatedColumn = $column;
                    return $link;
                }
            }
            return null;
        }

        if ($relationShipTable == $related->getTableName() && in_array($relation->getJoinSelfAs(), $relatedFields)) {
            foreach ([$relation->getJoinOtherAs(), 'id'] as $column) {
                if (in_array($column, $baseFields)) {
                    $link->kind = self::InRelated;
                    $link->baseColumn = $column;
                    $link->relatedColumn = $relation->getJoinSelfAs();
                    return $link;
                }
            }
            return null;
        }

        $link->kind = self::ThroughTable;
        $link->baseColumn = $base->getPrimaryKey();
        foreach ($relation->getJoinOtherAsGuess() as $column) {
            if (in_array($column, $baseFields)) {
                $link->baseColumn = $column;
                $link->guessed = true;
                break;
            }
        }
        $link->relatedColumn = $related->getPrimaryKey();
        $link->table = $relationShipTable;
        $link->tableBaseColumn = $relation->getJoinSelfAs();
        $link->tableRelatedColumn = $relation->getJoinOtherAs();
        return $link;
    }

}
