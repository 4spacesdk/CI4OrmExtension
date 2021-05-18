<?php namespace OrmExtension\Interfaces;

use OrmExtension\Extensions\Entity;

/**
 * Created by PhpStorm.
 * User: martin
 * Date: 2018-12-09
 * Time: 11:01
 */
interface OrmEventsInterface {

    /**
     * @param Entity $entity
     */
    public function postCreation($entity);

    /**
     * @param Entity $entity
     * @param $data
     */
    public function postUpdate($entity, $data);

    /**
     * @param Entity $entity
     */
    public function postDelete($entity);

    /**
     * @param Entity $entity
     * @param Entity $relation
     */
    public function postAddRelation($entity, $relation);

    /**
     * @param Entity $entity
     * @param Entity $relation
     */
    public function postDeleteRelation($entity, $relation);

}
