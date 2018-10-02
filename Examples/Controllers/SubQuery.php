<?php namespace OrmExtension\Examples\Controllers;

use CodeIgniter\Controller;
use OrmExtension\Data;
use OrmExtension\DataMapper\ModelDefinitionCache;
use OrmExtension\Examples\Entities\User;
use OrmExtension\Examples\Models\ColorModel;
use OrmExtension\Examples\Models\RoleModel;
use OrmExtension\Examples\Models\UserModel;
use OrmExtension\Examples\Setup;

class SubQuery extends Controller {

    public function __construct() {
        ModelDefinitionCache::getInstance()->cache->clean();
        Setup::run();
    }

    public function select() {
        $subQuery = (new RoleModel())
            ->select('COUNT(*) name')
            ->where('name', '${parent}.name', false);

        $model = new UserModel();
        $user = $model
            ->select('*')
            ->selectSubQuery($subQuery, 'admin_roles')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function where() {
        $selectQuery = (new RoleModel())
            ->select('COUNT(*)')
            ->whereRelated(UserModel::class, 'id', '${parent}.id', false);

        $whereQuery = (new RoleModel())
            ->select('COUNT(*) as count')
            ->whereRelated(UserModel::class, 'id', '${parent}.id', true)
            ->having('count >', '1');

        $model = new UserModel();
        $user = $model
            //->selectSubQuery($selectQuery, 'roles_count')
            ->whereSubQuery($whereQuery)
            ->find();
        //Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function kili() {

        $model = new UserModel();
        $user = $model
            ->where('color_id', 'users.id')
            ->find();


        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

}