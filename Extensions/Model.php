<?php namespace OrmExtension\Extensions;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Validation\ValidationInterface;
use Config\OrmExtension;
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
 * @property string $entityName
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
     * @param string|array $select
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function select($select = '*', bool $escape = null, $appendTable = true): Model {
        $this->selecting = true;
        if($appendTable) {
            if(strpos($select, '.') === false) {
                $selects = explode(',', $select);
                foreach($selects as &$select) {
                    $select = trim($select);
                    $this->appendTable($select);
                }
                $select = implode(', ', $selects);
            }
        }
        return parent::select($select, $escape);
    }

    // </editor-fold>

    // <editor-fold desc="Where">

    /**
     * @param mixed $key
     * @param mixed $value
     * @param boolean $escape
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function where($key, $value = null, bool $escape = null, $appendTable = true) {
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
        return parent::where("`$key` BETWEEN \"{$min}\" AND \"{$max}\"", null, $escape);
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
        return parent::where("$key NOT BETWEEN \"{$min}\" AND \"{$max}\"", null, $escape);
    }

    /**
     * @param string $key
     * @param mixed $values
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function whereIn(string $key = null, $values = null, bool $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        if($values instanceof Entity) {
            $ids = [];
            foreach($values as $value) $ids[] = $value->{$this->getPrimaryKey()};
            $values = $ids;
        }
        if(is_string($values)) $values = [$values];
        return parent::whereIn($key, $values, $escape);
    }

    /**
     * @param string $key
     * @param mixed $values
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function whereNotIn(string $key = null, $values = null, bool $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        if($values instanceof Entity) {
            $ids = [];
            foreach($values as $value) $ids[] = $value->{$this->getPrimaryKey()};
            $values = $ids;
        }
        return parent::whereNotIn($key, $values, $escape);
    }

    // </editor-fold>

    // <editor-fold desc="Or Where">

    /**
     * @param mixed $key
     * @param mixed $value
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function orWhere($key, $value = null, bool $escape = null, $appendTable = true) {
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
     * @param string $key
     * @param array $values
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function orWhereIn(string $key = null, array $values = null, bool $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::orWhereIn($key, $values, $escape);
    }

    /**
     * @param string $key
     * @param array $values
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function orWhereNotIn(string $key = null, array $values = null, bool $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::orWhereNotIn($key, $values, $escape);
    }


    // </editor-fold>

    // <editor-fold desc="Like">

    /**
     * @param mixed $field
     * @param string $match
     * @param string $side
     * @param boolean $escape
     * @param boolean $insensitiveSearch
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function like($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false, $appendTable = true) {
        if($appendTable) $this->appendTable($field);
        return parent::like($field, $match, $side, $escape, $insensitiveSearch);
    }

    /**
     * @param mixed $field
     * @param string $match
     * @param string $side
     * @param boolean $escape
     * @param boolean $insensitiveSearch
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function notLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false, $appendTable = true) {
        if($appendTable) $this->appendTable($field);
        return parent::notLike($field, $match, $side, $escape, $insensitiveSearch);
    }

    /**
     * @param mixed $field
     * @param string $match
     * @param string $side
     * @param boolean $escape
     * @param boolean $insensitiveSearch
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false, $appendTable = true) {
        if($appendTable) $this->appendTable($field);
        return parent::orLike($field, $match, $side, $escape, $insensitiveSearch);
    }

    /**
     * @param mixed $field
     * @param string $match
     * @param string $side
     * @param boolean $escape
     * @param boolean $insensitiveSearch
     * @param bool $appendTable
     * @return BaseBuilder|Model
     */
    public function orNotLike($field, string $match = '', string $side = 'both', bool $escape = null, bool $insensitiveSearch = false, $appendTable = true) {
        if($appendTable) $this->appendTable($field);
        return parent::orNotLike($field, $match, $side, $escape, $insensitiveSearch);
    }

    // </editor-fold>

    // <editor-fold desc="Group by, Having, Order By">

    /**
     * @param string $by
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function groupBy($by, bool $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($by);
        return parent::groupBy($by, $escape);
    }

    /**
     * @param string|array $key
     * @param mixed $value
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function having($key, $value = null, bool $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::having($key, $value, $escape);
    }

    /**
     * @param string|array $key
     * @param mixed $value
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function orHaving($key, $value = null, bool $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($key);
        return parent::orHaving($key, $value, $escape);
    }

    /**
     * @param string $orderBy
     * @param string $direction ASC, DESC, RANDOM or IS NULL
     * @param boolean $escape
     * @param boolean $appendTable
     * @return BaseBuilder|Model
     */
    public function orderBy(string $orderBy, string $direction = '', bool $escape = null, $appendTable = true) {
        if($appendTable) $this->appendTable($orderBy);

        if(is_null($direction) || $direction == 'null') {
            return $this->orderByHack($orderBy, 'IS NULL', $escape);
        } else if(in_array($direction, ['null asc', 'null desc'])) {
            [$_, $nullDirection] = explode(' ', $direction);
            return $this->orderByHack($orderBy, "IS NULL $nullDirection", $escape);
        }

        return parent::orderBy($orderBy, $direction, $escape);
    }

    private function orderByHack($orderby, $direction = '', $escape = null) {
        $direction = strtoupper(trim($direction));

        if(empty($orderby))
            return $this;
        else if ($direction !== '') {
            $direction = in_array($direction, ['IS NULL', 'IS NULL DESC', 'IS NULL ASC'], true) ? ' ' . $direction : '';
        }

        is_bool($escape) || $escape = $this->db->protectIdentifiers;

        if ($escape === false)
        {
            $qb_orderby[] = [
                'field'     => $orderby,
                'direction' => $direction,
                'escape'    => false,
            ];
        }
        else
        {
            $qb_orderby = [];
            foreach (explode(',', $orderby) as $field)
            {
                $qb_orderby[] = ($direction === '' &&
                    preg_match('/\s+(ASC|DESC)$/i', rtrim($field), $match, PREG_OFFSET_CAPTURE)) ? [
                    'field'     => ltrim(substr($field, 0, $match[0][1])),
                    'direction' => ' ' . $match[1][0],
                    'escape'    => true,
                ] : [
                    'field'     => trim($field),
                    'direction' => $direction,
                    'escape'    => true,
                ];
            }
        }

        $this->builder()->QBOrderBy = array_merge($this->builder()->QBOrderBy, $qb_orderby);

        return $this;
    }

    // </editor-fold>

    // <editor-fold desc="Groups">

    /**
     * @param string $not
     * @param string $type
     * @return BaseBuilder|Model
     */
    public function groupStart(string $not = '', string $type = 'AND ') {
        return parent::groupStart($not, $type);
    }

    /**
     * @return BaseBuilder|Model
     */
    public function orGroupStart() {
        return parent::orGroupStart();
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

        if($result instanceof Entity) {
            $result = [$result];
        }

        $this->arrangeIncludedRelations($result);

        // Convert from array to single Entity
        if(is_array($result) && count($result) > 0) {
            $first = clone $result[0];
            foreach($result as $item) {
                $first->add($item);
            }
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
     * @param bool $isNew
     * @return void
     */
    protected function prepareSave($entity, $isNew) {
        $this->entityToSave = $entity;

        if($isNew && $this->useTimestamps && in_array($this->createdField, $this->getTableFields())) {
            if(empty($entity->{$this->createdField}))
                $entity->{$this->createdField} = $this->setDate();
        }
        if(!$isNew && $this->useTimestamps && in_array($this->updatedField, $this->getTableFields())) {
            $entity->{$this->updatedField} = $this->setDate();
        }
    }

    /**
     * @param bool $result
     * @param Entity $entity
     * @param bool $isNew
     * @return mixed
     */
    protected function completeSave($result, $entity, $isNew) {
        if($result && empty($entity->{$this->getPrimaryKey()}))
            $entity->{$this->getPrimaryKey()} = $result;

        //$entity->resetStoredFields();
        $entity->syncOriginal();

        if($this instanceof OrmEventsInterface) {
            if($isNew)
                $this->postCreation($entity);
            else {
                // Clean up updateData, CI4 likes to put primaryKey in every update
                if(isset($this->updatedData[$this->getPrimaryKey()])) {
                    $updatedPrimaryKey = $this->updatedData[$this->getPrimaryKey()];
                    if($updatedPrimaryKey['old'] == $updatedPrimaryKey['old'])
                        unset($this->updatedData[$this->getPrimaryKey()]);
                }

                $this->postUpdate($entity, $this->updatedData);
            }
        }

        return $result;
    }

    /**
     * @param Entity $entity
     * @return bool
     * @throws \ReflectionException
     */
    public function save($entity): bool {
        $isNew = empty($entity->{$this->getPrimaryKey()}) || is_null($entity->{$this->getPrimaryKey()});
        $this->prepareSave($entity, $isNew);

        $result = $this->saveAndReturnId($entity);
        return $this->completeSave($result, $entity, $isNew);
    }

    /**
     * @param $data
     * @return int
     * @throws \ReflectionException
     */
    private function saveAndReturnId($data) {
        if(empty($data)) return true;

        if(is_object($data) && isset($data->{$this->primaryKey})) {
            try {
                parent::update($data->{$this->primaryKey}, $data);
            } catch (DataException $e) {
                if ($e->getMessage() == 'There is no data to update.') {
                    // Ignore empty update exceptions
                }
            }
            return $data->{$this->primaryKey};
        } elseif (is_array($data) && !empty($data[$this->primaryKey])) {
            try {
                parent::update($data[$this->primaryKey], $data);
            } catch (DataException $e) {
                if ($e->getMessage() == 'There is no data to update.') {
                    // Ignore empty update exceptions
                }
            }
            return $data[$this->primaryKey];
        } else {
            return parent::insert($data, true);
        }
    }

    /**
     * @param Entity $entity
     * @param bool $returnID
     * @return bool|int|string|void
     * @throws \ReflectionException
     */
    public function insert($entity = null, bool $returnID = true) {
        $this->prepareSave($entity, true);
        $result = parent::insert($entity, false);
        return $this->completeSave($result, $entity, true);
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
            $original = $this->entityToSave->getOriginal();
            foreach($fields as $field => $value) {
                $this->updatedData[$field] = [
                    'old'   => isset($original[$field]) ? $original[$field] : null,
                    'new'   => $fields[$field]
                ];
            }
            if(empty($fields)) { // Set the id field, CI dont like empty updates
                $fields[$this->getPrimaryKey()] = $original[$this->getPrimaryKey()];
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
            if(empty($fields)) { // Set the id field, CI dont like empty updates
                $fields[$this->getPrimaryKey()] = 0;
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
    protected $entityName   = null;

    private function setCodeIgniterModelStuff() {
        if (!isset($this->table)) {
            $this->table = $this->getTableName();
        }
        if (!isset($this->entityName)) {
            $this->entityName = $this->getEntityName();
        }

        if (!isset($this->returnType)) {
            foreach (OrmExtension::$entityNamespace as $entityNamespace) {
                $this->returnType = $entityNamespace . $this->getEntityName();
                if (class_exists($this->returnType)) {
                    break;
                }
            }
        }

        if (!isset($this->allowedFields) || count($this->allowedFields) == 0) {
            $this->allowedFields = ModelDefinitionCache::getFields($this->getEntityName(), $this->table);
        }

        $this->afterFind[] = 'handleResult';
        $this->beforeUpdate[] = 'modifyUpdateFields';
        $this->beforeInsert[] = 'modifyInsertFields';
        if(in_array('deletion_id', $this->allowedFields)) {
            $this->useSoftDeletes = true;
            $this->deletedField = 'deletion_id';
        }
    }

    public function getTableName() {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', plural($this->getEntityName())));
    }

    public function getEntityName() {
        $namespace = explode('\\', get_class($this));
        return substr(end($namespace), 0, -5);
    }

    public function getPrimaryKey() {
        return $this->primaryKey;
    }

    // </editor-fold>

}
