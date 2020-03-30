<?php namespace OrmExtension\Migration;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\Config;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use Config\Database;
use Config\Logger;
use DebugTool\Data;
use OrmExtension\DataMapper\ModelDefinitionCache;

/**
 * Class Table
 * @package OrmExtension\Migration
 * @property string $name
 * @property BaseConnection $db
 * @property Forge $forge
 * @property array $dbGroup
 */
class Table {

    public static function init($name, $group = null) {
        /** @var Database $config */
        $config = config('Database');
        if(!$group) {
            $group = $config->defaultGroup;
        }

        $table = new Table();
        $table->dbGroup = $config->{$group};
        $table->name = $name;
        $table->db = Database::connect($group);
        $table->forge = Database::forge($group);
        return $table;
    }

    public function create($primaryKeyName = 'id', $primaryKeyType = ColumnTypes::INT, $autoIncrement = true) {
        $sql = "CREATE TABLE IF NOT EXISTS `$this->name` (
                  `{$primaryKeyName}` {$primaryKeyType} ".($autoIncrement ? 'AUTO_INCREMENT' : '').",
                  PRIMARY KEY (`{$primaryKeyName}`)
                ) ENGINE=InnoDB ".($autoIncrement ? 'AUTO_INCREMENT=1' : '')
            ." CHARACTER SET {$this->dbGroup['charset']} COLLATE {$this->dbGroup['DBCollat']};";
        $this->db->query($sql);
        return $this;
    }

    public function hasColumn($name) {
        $db = $this->db->getDatabase();
        $sql = "SELECT count(*) as count FROM information_schema.columns 
                WHERE `table_schema` = '{$db}' 
                AND `table_name` = '{$this->name}'
                AND `column_name` = '{$name}';";
        $result = $this->db->query($sql);
        return $result->getResultArray()[0]['count'];
    }

    public function column($name, $type, $default = null) {
        if(!$this->hasColumn($name)) {
            $sql = "ALTER TABLE `{$this->name}` ADD `{$name}` {$type}";
            if($default) {
                if(is_string($default)) $default = "\"$default\"";
                $sql .= " DEFAULT {$default}";
            }
            $sql .= ";";
            $this->db->query($sql);

            ModelDefinitionCache::getInstance()->clearCache();
        }
        return $this;
    }

    public function dropTable() {
        $sql = "DROP TABLE IF EXISTS `{$this->name}`";
        $this->db->query($sql);
        return $this;
    }

    public function truncate() {
        if($this->db->resetDataCache()->tableExists($this->name)) {
            $this->db->query("TRUNCATE {$this->name}");
        }
    }

    public function dropColumn($name) {
        if($this->hasColumn($name)) {
            $sql = "ALTER TABLE {$this->name} DROP COLUMN {$name};";
            $this->db->query($sql);
        }
        return $this;
    }

    public function timestamps() {
        return $this
            ->column('created', ColumnTypes::DATETIME)
            ->column('updated', ColumnTypes::DATETIME);
    }

    public function softDelete() {
        return $this
            ->column('deletion_id', ColumnTypes::INT)
            ->addIndex('deletion_id');
    }

    public function createdUpdatedBy() {
        return $this
            ->column('created_by_id', ColumnTypes::INT)
            ->column('updated_by_id', ColumnTypes::INT);
    }

    public function addIndex($names) {
        $args = func_get_args();
        $name = $args[0];

        $indexes = [];
        if(count($args) > 1)
            $indexes = array_slice($args, 1);

        if(count($indexes) > 0)
            $indexes = implode(', ', $indexes);
        else
            $indexes = "`$name`";

        if($this->hasIndex($name)) {
            //Data::debug("Index $name already exists in {$this->name}");
        } else {
            $this->db->query("ALTER TABLE `{$this->name}` ADD INDEX `$name` ($indexes)");
            //Data::debug("Index $name added to {$this->name}");
        }

        return $this;
    }

    public function hasIndex($name) {
        if(!$this->hasColumn($name))
            Data::debug(get_class($this), "column", $name, 'does not exist on table', $this->name);
        $sql = "SELECT TABLE_SCHEMA, COUNT(1) IndexIsThere FROM INFORMATION_SCHEMA.STATISTICS
                WHERE table_schema=DATABASE() AND table_name='{$this->name}' AND index_name='$name';";
        $result = $this->db->query($sql);
        $data = null;
        foreach($result->getResult() as $row) {
            if(strcmp($row->TABLE_SCHEMA, $this->db->getDatabase()) === 0)
                $data = $row;
        }
        return isset($data) ? $data->IndexIsThere : false;
    }
}
