<?php namespace OrmExtension\Examples\Controllers;

use CodeIgniter\Controller;
use OrmExtension\Data;
use OrmExtension\DataMapper\ModelDefinitionCache;
use OrmExtension\Examples\Entities\User;
use OrmExtension\Examples\Models\ColorModel;
use OrmExtension\Examples\Models\UserModel;
use OrmExtension\Examples\Setup;

class Like extends Controller {

    public function __construct() {
        ModelDefinitionCache::getInstance()->cache->clean();
        Setup::run();
    }

    public function simple() {
        $model = new UserModel();
        $user = $model
            ->like('name', 'green')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function not() {
        $model = new UserModel();
        $user = $model
            ->notLike('name', 'green')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or() {
        $model = new UserModel();
        $user = $model
            ->like('name', 'red')
            ->orLike('name', 'green')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or_not() {
        $model = new UserModel();
        $user = $model
            ->like('name', 'red')
            ->orNotLike('name', 'green')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function related() {
        $model = new UserModel();
        $user = $model
            ->likeRelated(ColorModel::class, 'name', 'green')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function not_related() {
        $model = new UserModel();
        $user = $model
            ->notLikeRelated(ColorModel::class, 'name', 'green')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or_related() {
        $model = new UserModel();
        $user = $model
            ->likeRelated(ColorModel::class, 'name', 'red')
            ->orLikeRelated(ColorModel::class, 'name', 'green')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or_not_related() {
        $model = new UserModel();
        $user = $model
            ->likeRelated(ColorModel::class, 'name', 'red')
            ->orNotLikeRelated(ColorModel::class, 'name', 'green')
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

}