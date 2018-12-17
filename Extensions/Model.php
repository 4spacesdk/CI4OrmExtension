<?php namespace OrmExtension\Extensions;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use Config\OrmExtension;
use DebugTool\Data;
use OrmExtension\DataMapper\ModelDefinitionCache;
use OrmExtension\DataMapper\QueryBuilder;
use OrmExtension\DataMapper\ResultBuilder;
use OrmExtension\Interfaces\OrmEventsInterface;

/**
 * Class Model
 * @package OrmExtension\Extensions
 *
 * CodeIgniter Model Stuff
 * @property string $returnType
 * @property string $table
 * @property string[] $allowedFields
 * @property bool $useTimestamps
 * @property array $afterFind
 * @property array $beforeUpdate
 * @property array $beforeInsert
 * @property string $createdField
 * @property string $updatedField
 * @property bool $useSoftDeletes
 * @property string $deletedField
 *
 * Orm Extension
 * @property array $hasOne
 * @property array $hasMany
 *
 * @mixin \OrmExtension\Extensions\Database\BaseBuilder
 */
class Model extends \CodeIgniter\Model {

    public function __construct(ConnectionInterface $db = null, ValidationInterface $validation = null) {
        $this->setCodeIgniterModelStuff();
        parent::__construct($db, $validation);
    }

    // <editor-fold desc="QueryBuilder">

    use QueryBuilder;

    public $hasOne = [];
    public $hasMany = [];

    protected function _getModel(): Model {
        return $this;
    }

    protected $builder;
    protected function _getBuilder(): BaseBuilder {
        return $this->builder;
    }

    public function _setBuilder(BaseBuilder $builder) {
        $this->builder = $builder;
    }

    private function appendTable(&$key) {
        $key = "{$this->getTableName()}.{$key}";
    }

    // <editor-fold desc="Select">

    private $selecting = false;
    public function isSelecting() {
        return $this->selecting;
    }
    public function setSelecting($selecting) {
        $this->selecting = $selecting;
    }

    /**
     * @param string $select
     * @param null $escape
     * @return BaseBuilder|Model
     */
    public function select($select = '*', $escape = null) {
        $this->selecting = true;
        return parent::select($select, $escape);
    }

    // </editor-fold>

    // <editor-fold desc="Where">

    /**
     * @param mixed $key
     * @param null $value
     * @param null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function where($key, $value = null, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::where($key, $value, $escape);
    }

    /**
     * @param $key
     * @param int $min
     * @param int $max
     * @param null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function whereBetween($key, $min = 0, $max = 0, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::where("`$key` BETWEEN {$min} AND {$max}", null, $escape);
    }

    /**
     * @param $key
     * @param int $min
     * @param int $max
     * @param bool $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function whereNotBetween($key, $min = 0, $max = 0, $escape = false, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::where("$key NOT BETWEEN {$min} AND {$max}", null, $escape);
    }

    /**
     * @param null $key
     * @param null $values
     * @param null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function whereIn($key = null, $values = null, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        if($values instanceof Entity) {
            $ids = [];
            foreach($values as $value) $ids[] = $value->id;
            $values = $ids;
        }
        return parent::whereIn($key, $values, $escape);
    }

    /**
     * @param null $key
     * @param null $values
     * @param null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function whereNotIn($key = null, $values = null, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::whereNotIn($key, $values, $escape);
    }

    // </editor-fold>

    // <editor-fold desc="Or Where">

    /**
     * @param mixed $key
     * @param null $value
     * @param null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orWhere($key, $value = null, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::orWhere($key, $value, $escape);
    }

    /**
     * @param $key
     * @param int $min
     * @param int $max
     * @param null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orWhereBetween($key, $min = 0, $max = 0, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::orWhere("`$key` BETWEEN {$min} AND {$max}", null, $escape);
    }

    /**
     * @param $key
     * @param int $min
     * @param int $max
     * @param bool $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orWhereNotBetween($key, $min = 0, $max = 0, $escape = false, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::orWhere("$key NOT BETWEEN {$min} AND {$max}", null, $escape);
    }

    /**
     * @param null $key
     * @param null $values
     * @param null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orWhereIn($key = null, $values = null, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::orWhereIn($key, $values, $escape);
    }

    /**
     * @param null $key
     * @param null $values
     * @param null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orWhereNotIn($key = null, $values = null, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::orWhereNotIn($key, $values, $escape);
    }


    // </editor-fold>

    // <editor-fold desc="Like">

    /**
     * @param mixed $field
     * @param string $match
     * @param string $side
     * @param null $escape
     * @param bool $insensitiveSearch
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function like($field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false, $appendTable = true) {
        if($appendTable) $this->appendTable($field);
        return parent::like($field, $match, $side, $escape, $insensitiveSearch);
    }

    /**
     * @param mixed $field
     * @param string $match
     * @param string $side
     * @param null $escape
     * @param bool $insensitiveSearch
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function notLike($field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false, $appendTable = true) {
        if($appendTable) $this->appendTable($field);
        return parent::notLike($field, $match, $side, $escape, $insensitiveSearch);
    }

    /**
     * @param mixed $field
     * @param string $match
     * @param string $side
     * @param null $escape
     * @param bool $insensitiveSearch
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orLike($field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false, $appendTable = true) {
        if($appendTable) $this->appendTable($field);
        return parent::orLike($field, $match, $side, $escape, $insensitiveSearch);
    }

    /**
     * @param mixed $field
     * @param string $match
     * @param string $side
     * @param null $escape
     * @param bool $insensitiveSearch
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orNotLike($field, $match = '', $side = 'both', $escape = null, $insensitiveSearch = false, $appendTable = true) {
        if($appendTable) $this->appendTable($field);
        return parent::orNotLike($field, $match, $side, $escape, $insensitiveSearch);
    }

    // </editor-fold>

    // <editor-fold desc="Group by, Having, Order By">

    /**
     * @param string $by
     * @param bool|null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function groupBy($by, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($by);
        return parent::groupBy($by, $escape);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool|null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function having($key, $value = null, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($by);
        return parent::having($key, $value, $escape);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool|null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orHaving($key, $value = null, $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($by);
        return parent::orHaving($key, $value, $escape);
    }

    /**
     * @param string $orderby
     * @param string $direction
     * @param bool|null $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orderBy($orderby, $direction = '', $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($by);
        return parent::orderBy($orderby, $direction, $escape);
    }

    // </editor-fold>

    // <editor-fold desc="Groups">

    /**
     * @param string $not
     * @param string $type
     * @return BaseBuilder|Model
     */
    public function groupStart($not = '', $type = 'AND ') {
        return parent::groupStart($not, $type);
    }

    /**
     * @return BaseBuilder|Model
     */
    public function groupEnd() {
        return parent::groupEnd();
    }

    // </editor-fold>

    /**
     * @param null $id
     * @return array|object|null|Entity
     */
    public function find($id = null) {
        $result = parent::find($id);
        // Clear
        $this->setSelecting(false);
        return $result;
    }

    public function countAllResults($reset = true, $test = false) {
        if($this->tempUseSoftDeletes === true) { // CI4 Bug..
            parent::where($this->deletedField, 0);
        }

        return parent::countAllResults($reset, $test);
    }

    // </editor-fold>


    // <editor-fold desc="ResultBuilder">

    use ResultBuilder;

    /**
     * Called after find
     * @param array $data
     * @return mixed
     */
    protected function handleResult(array $data) {
        if(empty($data['data'])) {
            $data['data'] = new $this->returnType();
            return $data;
        }
        $result = $data['data'];

        if($result instanceof Entity)
            $result = [$result];

        $this->arrangeIncludedRelations($result);

        // Convert from array to single Entity
        if(is_array($result) && count($result) > 0) {
            $first = clone $result[0];
            foreach($result as $item) $first->add($item);
            $result = $first;
        } else {
            $result = new $this->returnType();
        }

        $data['data'] = $result;
        return $data;
    }

    // </editor-fold>



    // <editor-fold desc="Saving">

    /** @var Entity $entityToSave */
    private $entityToSave;
    /** @var array $updatedData */
    private $updatedData;

    /**
     * @param Entity $entity
     * @return bool
     */
    public function save($entity) {
        $this->entityToSave = $entity;
        $isNew = !$entity->id;

        if($isNew && $this->useTimestamps && in_array($this->createdField, $this->getTableFields())) {
            if(empty($entity->{$this->createdField}))
                $entity->{$this->createdField} = $this->setDate();
        }
        if(!$isNew && $this->useTimestamps && in_array($this->updatedField, $this->getTableFields())) {
            $entity->{$this->updatedField} = $this->setDate();
        }

        $result = parent::save($entity);
        if($result &! $entity->id)
            $entity->id = $result;

        $entity->resetStoredFields();

        if($this instanceof OrmEventsInterface) {
            if($isNew)
                $this->postCreation($entity);
            else
                $this->postUpdate($entity, $this->updatedData);
        }

        return $result;
    }

    public static function classToArray($data, string $dateFormat = 'datetime'): array {
        if($data instanceof Entity) {
            $properties = [];
            foreach($data->getModel()->getTableFields() as $field)
                $properties[$field] = $data->{$field};
            return $properties;
        } else
            return parent::classToArray($data, $dateFormat);
    }

    /**
     * Called before update
     * @param array $data
     * @return array
     */
    public function modifyUpdateFields($data) {
        $this->updatedData = [];
        if($this->entityToSave instanceof Entity) {
            $fields = $data['data'];
            foreach($fields as $field => $value) {
                if((string)$value === (string)$this->entityToSave->stored[$field])
                    unset($fields[$field]);
                else
                    $this->updatedData[$field] = [
                        'old'   => $this->entityToSave->stored[$field],
                        'new'   => $fields[$field]
                    ];
            }
            if(empty($fields)) {
                // Set the id field, CI dont like empty updates
                $fields['id'] = $this->entityToSave->stored['id'];
            }
            $data['data'] = $fields;
        }
        unset($this->entityToSave);
        return $data;
    }

    /**
     * Called before insert
     * @param array $data
     * @return array
     */
    public function modifyInsertFields($data) {
        if($this->entityToSave instanceof Entity) {
            $fields = $data['data'];
            foreach($fields as $field => $value) {
                if(is_null($value))
                    unset($fields[$field]);
            }
            if(empty($fields)) {
                // Set the id field, CI dont like empty updates
                $fields['id'] = $this->entityToSave->stored['id'];
            }
            $data['data'] = $fields;
        }
        unset($this->entityToSave);
        return $data;
    }

    // </editor-fold>


    // <editor-fold desc="Silly stuff">

    protected $table        = null;
    protected $returnType   = null;

    private function setCodeIgniterModelStuff() {
        $this->table = $this->getTableName();
        $this->returnType = OrmExtension::$entityNamespace . $this->getEntityName();
        $this->allowedFields = ModelDefinitionCache::getFields($this->getEntityName(), $this->table);
        $this->afterFind[] = 'handleResult';
        $this->beforeUpdate[] = 'modifyUpdateFields';
        $this->beforeInsert[] = 'modifyInsertFields';
        if(in_array('deletion_id', $this->allowedFields)) {
            $this->useSoftDeletes = true;
            $this->deletedField = $this->table.'.deletion_id';
        }
    }

    public function getTableName() {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', plural($this->getEntityName())));
    }

    public function getEntityName() {
        $namespace = explode('\\', get_class($this));
        return substr(end($namespace), 0, -5);
    }

    // </editor-fold>

}