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
     * @var    string
     */
    protected $escapeChar = '`';

}
