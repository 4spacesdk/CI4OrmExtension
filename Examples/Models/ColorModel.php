<?php namespace OrmExtension\Examples\Models;

use OrmExtension\Extensions\Model;

class ColorModel extends Model {

    public $hasMany = [
        UserModel::class
    ];

}