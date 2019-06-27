<?php namespace OrmExtension\Hooks;

use Config\OrmExtension;

class PreController {

    public static function execute() {
        helper('inflector');

        if(!is_array(OrmExtension::$modelNamespace))
            OrmExtension::$modelNamespace = [OrmExtension::$modelNamespace];
        if(!is_array(OrmExtension::$entityNamespace))
            OrmExtension::$entityNamespace = [OrmExtension::$entityNamespace];

        include_once(__DIR__ . '/../debug_helper.php');
    }

}