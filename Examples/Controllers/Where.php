<?php namespace OrmExtension\Examples\Controllers;

use CodeIgniter\Controller;
use OrmExtension\Data;
use OrmExtension\DataMapper\ModelDefinitionCache;
use OrmExtension\Examples\Entities\User;
use OrmExtension\Examples\Models\UserModel;
use OrmExtension\Examples\Setup;

class Where extends Controller {

    public function __construct() {
        ModelDefinitionCache::getInstance()->cache->clean();
        Setup::run();
    }

    public function simple() {
        $model = new UserModel();
        $user = $model
            ->where('id', 2)
            ->find();
        Data::set('user', $user->toArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function in() {
        $model = new UserModel();
        $user = $model
            ->whereIn('id', [2, 3])
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function not_in() {
        $model = new UserModel();
        $user = $model
            ->whereNotIn('id', [2, 3])
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or() {
        $model = new UserModel();
        $user = $model
            ->where('id', 2)
            ->orWhere('id', 3)
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or_in() {
        $model = new UserModel();
        $user = $model
            ->where('id', 2)
            ->orWhereIn('id', [3, 4])
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or_not_in() {
        $model = new UserModel();
        $user = $model
            ->where('id', 2)
            ->orWhereNotIn('id', [3, 4])
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function between() {
        $model = new UserModel();
        $user = $model
            ->whereBetween('id', 2, 4)
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function not_between() {
        $model = new UserModel();
        $user = $model
            ->whereNotBetween('id', 2, 4)
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or_between() {
        $model = new UserModel();
        $user = $model
            ->where('id', 1)
            ->orWhereBetween('id', 2, 4)
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function or_not_between() {
        $model = new UserModel();
        $user = $model
            ->where('id', 1)
            ->orWhereNotBetween('id', 2, 4)
            ->find();
        Data::set('user', $user->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

}