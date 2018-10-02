<?php namespace OrmExtension\Examples\Controllers;

use CodeIgniter\Controller;
use OrmExtension\Data;
use OrmExtension\DataMapper\ModelDefinitionCache;
use OrmExtension\Examples\Entities\User;
use OrmExtension\Examples\Models\ColorModel;
use OrmExtension\Examples\Models\RoleModel;
use OrmExtension\Examples\Models\UserModel;
use OrmExtension\Examples\Setup;

class Crud extends Controller {

    public function __construct() {
        ModelDefinitionCache::getInstance()->cache->clean();
        Setup::run();
    }

    public function create() {
        $this->printBefore();

        // Create new user
        $user = new User();
        $user->name = "New user";
        $user->save();

        $this->printAfter();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function read() {
        // Find all users
        $users = (new UserModel())->findAll();
        Data::set('users', $users->all);

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function update() {
        $this->printBefore();

        // Find and Update user
        /** @var User $user */
        $user = (new UserModel())->find(2);
        $user->name = "Updated user (2)";
        $user->save();

        $this->printAfter();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function delete() {
        $this->printBefore();

        // Find and Delete user
        $user = (new UserModel())->find(2);
        $user->delete();

        $this->printAfter();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function save_relations() {
        $this->printBefore();

        // Insert user and relations
        $user = new User();
        $user->name = 'With all roles';
        $user->save();
        $roleModel = new RoleModel();
        $user->save($roleModel->find(1));
        $user->save($roleModel->find(2));
        $colorModel = new ColorModel();
        $user->save($colorModel->find(1));

        $this->printAfter();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function delete_relations() {
        //$this->printBefore();

        // Find and delete relations
        $user = (new UserModel())->find(1);
        $roleModel = new RoleModel();
        $user->delete($roleModel->find(1));
        $colorModel = new ColorModel();
        $user->delete($colorModel->find(1));
        Data::lastQuery();

        $this->printAfter();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function select_update() {
        //$this->printBefore();

        $userModel = new UserModel();
        $user = $userModel->find(1);
        $user->name = null;
        $userModel->save($user);
        Data::lastQuery();

        $this->printAfter();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function group_by() {
        $users = (new UserModel())
            ->groupByRelated(RoleModel::class, 'name')
            ->find();
        Data::set('by role', $users->allToArray());
        Data::lastQuery();
        $users = (new UserModel())
            ->groupByRelated(ColorModel::class, 'name')
            ->find();
        Data::set('by color', $users->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function having() {
        $users = (new UserModel())
            ->includeRelated(RoleModel::class)
            ->havingRelated(RoleModel::class, 'name', 'admin')
            ->find();
        Data::set('having admins', $users->allToArray());
        Data::lastQuery();
        $users = (new UserModel())
            ->includeRelated(ColorModel::class)
            ->havingRelated(ColorModel::class, 'name', 'green')
            ->find();
        Data::set('having green', $users->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }

    public function order_by() {
        $users = (new UserModel())
            ->orderByRelated(ColorModel::class, 'name')
            ->find();
        Data::set('order by color asc', $users->allToArray());
        Data::lastQuery();
        $users = (new UserModel())
            ->orderByRelated(ColorModel::class, 'name', 'desc')
            ->find();
        Data::set('order by color desc', $users->allToArray());
        Data::lastQuery();

        $this->response->setJSON(Data::getData());
        $this->response->send();
    }






    private function printBefore() {
        $users = (new UserModel())
            ->includeRelated(RoleModel::class)
            ->find();
        Data::set('users_before', $users->allToArray());
    }

    private function printAfter() {
        $users = (new UserModel())
            ->includeRelated(RoleModel::class)
            ->find();
        Data::set('users_after', $users->allToArray());
    }

}