<?php namespace OrmExtension\Extensions;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use Config\OrmExtension;
use OrmExtension\Data;
use OrmExtension\DataMapper\ModelDefinitionCache;
use OrmExtension\DataMapper\QueryBuilder;
use OrmExtension\DataMapper\ResultBuilder;

/**
 * Class Model
 * @package OrmExtension\Extensions
 *
 * CodeIgniter Model Stuff
 * @property string $returnType
 * @property string $table
 * @property string[] $allowedFields
 * @property array $afterFind
 * @property array $beforeUpdate
 * @property array $beforeInsert
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

    /**
     * @param Entity $entity
     * @return bool
     */
    public function save($entity) {
        $this->entityToSave = $entity;
        $result = parent::save($entity);
        $entity->resetStoredFields();
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
        if($this->entityToSave instanceof Entity) {
            $fields = $data['data'];
            foreach($fields as $field => $value) {
                if($value === $this->entityToSave->stored[$field])
                    unset($fields[$field]);
            }
            if(empty($fields)) {
                // TODO What to do, when no changes?
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
                // TODO What to do, when no changes?
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