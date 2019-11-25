<?php namespace OrmExtension\Extensions\Database\MySQLi;

use OrmExtension\Extensions\Database\BaseBuilder;

/**
 * Builder for MySQLi
 */
class Builder extends BaseBuilder
{

    /**
     * Identifier escape character
     *
     * @var string
     */
    protected $escapeChar = '`';

    /**
     * FROM tables
     *
     * Groups tables in FROM clauses if needed, so there is no confusion
     * about operator precedence.
     *
     * Note: This is only used (and overridden) by MySQL.
     *
     * @return string
     */
    protected function _fromTables(): string
    {
        if (! empty($this->QBJoin) && count($this->QBFrom) > 1)
        {
            return '(' . implode(', ', $this->QBFrom) . ')';
        }

        return implode(', ', $this->QBFrom);
    }
}
