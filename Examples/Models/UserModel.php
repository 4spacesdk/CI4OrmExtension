<?php namespace OrmExtension\Examples\Models;

use OrmExtension\Examples\Entities\User;
use OrmExtension\Extensions\Model;

/**
 * Class UserModel
 * @package OrmExtension\Examples\Models
 */
class UserModel extends Model {

    public $hasOne = [
        ColorModel::class,
        UserDetailModel::class
    ];

    public $hasMany = [
        RoleModel::class
    ];

    /**
     * @param null $id
     * @return User
     */
    public function find($id = null) {
        return parent::find($id);
    }
}