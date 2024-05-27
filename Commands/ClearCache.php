<?php namespace OrmExtension\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use OrmExtension\DataMapper\ModelDefinitionCache;

/**
 * Class ClearCache
 * @package RestExtension\Commands
 */
class ClearCache extends BaseCommand {

    public $group           = 'OrmExtension';
    public $name            = 'orm:clear:cache';
    public $description     = 'Clear cache';
    protected $usage        = 'orm:clear:cache';
    protected $arguments    = [

    ];
    protected $options      = [

    ];

    /**
     * Actually execute a command.
     * This has to be over-ridden in any concrete implementation.
     *
     * @param array $params
     * @throws \ReflectionException
     */
    public function run(array $params) {
        ModelDefinitionCache::getInstance()->clearCache();
    }

}
