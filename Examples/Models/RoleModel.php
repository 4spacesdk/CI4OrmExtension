<?php namespace OrmExtension\Examples\Models;

use OrmExtension\Extensions\Model;

class RoleModel extends Model {

    public $hasMany = [
        UserModel::class
    ];

}