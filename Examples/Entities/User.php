<?php namespace OrmExtension\Examples\Entities;

use OrmExtension\Extensions\Entity;

/**
 * Class User
 * @package OrmExtension\Examples\Entities
 * @property int $id
 * @property string $name
 * @property int $color_id
 * @property Color $color
 * @property int $user_detail_id
 * @property UserDetail $user_detail
 *
 * Many
 * @property Role $roles
 */
class User extends Entity {

    /**
     * @return \ArrayIterator|Entity[]|\Traversable|User[]
     */
    public function getIterator() {
        return parent::getIterator();
    }
}