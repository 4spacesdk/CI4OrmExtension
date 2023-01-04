<?php namespace OrmExtension\Extensions\Database;

class BaseBuilder extends \CodeIgniter\Database\BaseBuilder {

    public function getBindKeyCount(): array {
        return $this->bindsKeyCount;
    }

    /**
     * @param mixed $key
     * @param null $value
     * @param bool $escape
     * @return \CodeIgniter\Database\BaseBuilder|BaseBuilder
     */
    public function where($key, $value = null, bool $escape = null) {
        return parent::where($key, $value, $escape);
    }

    /**
     * @param string $sql
     * @param array $bindKeyCount
     * @param array $binds
     * @return string
     */
    public function bindMerging($sql, $bindKeyCount, $binds) {
        $this->bindsKeyCount = array_merge($this->bindsKeyCount, $bindKeyCount);
        foreach($binds as $key => [$value, $escape]) {
            $newKey = $this->setBind($key, $value, $escape);
            $sql = str_replace(":$key:", ":$newKey:", $sql);
        }
        return $sql;
    }

    /**
     * @param string $search
     * @param string $replace
     * @return string
     */
    public function bindReplace($search, $replace) {
        foreach($this->getBinds() as $key => [$value, $escape]) {
            $this->binds[$key] = [str_replace($search, $replace, $value), $escape];
        }
    }

    public function compileSelect_(): string {
        return parent::compileSelect();
    }

}
