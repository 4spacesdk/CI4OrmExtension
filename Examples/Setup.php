<?php namespace OrmExtension\Examples;

use Config\Database;
use OrmExtension\Examples\Entities\Color;
use OrmExtension\Examples\Entities\Role;
use OrmExtension\Examples\Entities\User;
use OrmExtension\Examples\Entities\UserDetail;

class Setup {

    public static function run() {
        Setup::addTables();
        Setup::addContent();
    }

    private static function addContent() {
        $admin =  new Role();
        $admin->name = 'admin';
        $admin->save();
        $employee =  new Role();
        $employee->name = 'employee';
        $employee->save();

        $green =  new Color();
        $green->name = 'green';
        $green->save();
        $blue =  new Color();
        $blue->name = 'blue';
        $blue->save();
        $red =  new Color();
        $red->name = 'red';
        $red->save();

        $detail1 = new UserDetail();
        $detail1->address = "User 1 street";
        $detail1->save();
        $detail2 = new UserDetail();
        $detail2->address = "User 2 street";
        $detail2->save();
        $detail3 = new UserDetail();
        $detail3->address = "User 3 street";
        $detail3->save();
        $detail4 = new UserDetail();
        $detail4->address = "User 4 street";
        $detail4->save();
        $detail5 = new UserDetail();
        $detail5->address = "User 5 street";
        $detail5->save();
        $detail6 = new UserDetail();
        $detail6->address = "User 6 street";
        $detail6->save();

        $user = new User();
        $user->name = "Green admin";
        $user->save();
        $user->save($green);
        $user->save($admin);
        $user->save($detail1);
        $detail1->save($user);
        $user = new User();
        $user->name = "Blue admin";
        $user->save();
        $user->save($blue);
        $user->save($admin);
        $user->save($detail2);
        $user = new User();
        $user->name = "Red admin";
        $user->save();
        $user->save($red);
        $user->save($admin);
        $user->save($detail3);
        $user = new User();
        $user->name = "Green employee";
        $user->save();
        $user->save($green);
        $user->save($employee);
        $user->save($detail4);
        $user = new User();
        $user->name = "Blue employee";
        $user->save();
        $user->save($blue);
        $user->save($employee);
        $user->save($detail5);
        $user = new User();
        $user->name = "Red employee";
        $user->save();
        $user->save($red);
        $user->save($employee);
        $user->save($detail6);
    }

    private static function addTables() {
        $forge = Database::forge();
        $forge->dropTable('users', true);
        $forge->dropTable('roles', true);
        $forge->dropTable('roles_users', true);
        $forge->dropTable('colors', true);
        $forge->dropTable('user_details', true);

        $forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'color_id' => [
                'type' => 'INT',
                'unsigned' => true
            ],
            'user_detail_id' => [
                'type' => 'INT',
                'unsigned' => true
            ]
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('users');


        $forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ]
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('roles');


        $forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'role_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ]
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('roles_users');


        $forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ]
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('colors');


        $forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'address' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ]
        ]);
        $forge->addPrimaryKey('id');
        $forge->createTable('user_details');
    }

}