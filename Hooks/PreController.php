<?php namespace OrmExtension\Hooks;

class PreController {

    public static function execute() {
        helper('inflector');
    }

}