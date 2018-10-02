<?php namespace OrmExtension\Examples\Controllers;

use CodeIgniter\Controller;
use OrmExtension\Data;
use OrmExtension\DataMapper\ModelDefinitionCache;
use OrmExtension\Examples\Entities\User;
use OrmExtension\Examples\Models\ColorModel;
use OrmExtension\Examples\Models\RoleModel;
use OrmExtension\Examples\Models\UserDetailModel;
use OrmExtension\Examples\Models\UserModel;
use OrmExtension\Examples\Setup;

class Related extends Controller {

    public function __construct() {
        ModelDefinitionCache::getInstance()->cache->clean();
        Setup::run();
    }

    public function simple() {
        $model = new UserModel();
        $model
            ->includeRelated(UserDetailModel::class)
            ->whereRelated(RoleModel::class, 'name', 'employee');
        $users = $model->find();
        Data::lastQuery();

        foreach($users as $user) {
            $user->color->find();
            $user->roles->find();
        }

        Data::set('user', $users->allToArray());

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function in() {
        $model = new UserModel();
        $user = $model
            ->whereInRelated(RoleModel::class, 'name', ['admin', 'employee'])
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function not_in() {
        $model = new UserModel();
        $user = $model
            ->whereNotInRelated(RoleModel::class, 'name', ['admin', 'employee'])
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or() {
        $model = new UserModel();
        $user = $model
            ->whereRelated(ColorModel::class, 'name', 'green')
            ->orWhereRelated(ColorModel::class, 'name', 'blue')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or_in() {
        $model = new UserModel();
        $user = $model
            ->whereRelated(ColorModel::class, 'name', 'green')
            ->orWhereInRelated(ColorModel::class, 'name', ['blue', 'red'])
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or_not_in() {
        $model = new UserModel();
        $user = $model
            ->whereRelated(ColorModel::class, 'name', 'green')
            ->orWhereNotInRelated(ColorModel::class, 'name', ['blue'])
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function between() {
        $model = new UserModel();
        $user = $model
            ->whereBetweenRelated(ColorModel::class, 'id', 2, 3)
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function not_between() {
        $model = new UserModel();
        $user = $model
            ->whereNotBetweenRelated(ColorModel::class, 'id', 2, 3)
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

}