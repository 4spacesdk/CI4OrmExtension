<?php namespace OrmExtension\Examples\Models;

use OrmExtension\Examples\Entities\UserDetail;
use OrmExtension\Extensions\Model;

/**
 * Class UserDetailModel
 * @package OrmExtension\Examples\Models
 */
class UserDetailModel extends Model {

    public $hasOne = [
        UserModel::class
    ];

    public $hasMany = [

    ];

    /**
     * @param null $id
     * @return array|object|UserDetail|\OrmExtension\Extensions\Entity
     */
    public function find($id = null) {
        return parent::find($id);
    }
}