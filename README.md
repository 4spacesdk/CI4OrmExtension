# CodeIgniter 4 OrmExtension

## What is CodeIgniter 4 OrmExtension?
OrmExtension is an Object Relational Mapper written in PHP for CodeIgniter 4. 
It is designed to map your Database tables into easy to work with objects, fully aware of the relationships between each other.
OrmExtension is based on the same idea as the original [WanWizard DataMapper](https://datamapper.wanwizard.eu/) for CodeIgniter 2. But totally rewritten to fit CodeIgniter 4.


## Installation
Step 1)

`composer require 4spacesdk/ci4ormextension`

Step 2)

Create new file `application/Config/OrmExtension.php` and add this content
```php
<?php namespace Config;
class OrmExtension {
    public static $modelNamespace = 'App\Models\\';
    public static $entityNamespace = 'App\Entities\\';
}
```
Update the namespace to fit your project.

Step 3)

Add this line to your `application/Config/Events.php` file 
```php
Events::on('pre_system', [\OrmExtension\Hooks\PreController::class, 'execute']);
```

## Usage
Check the Examples folder for inspiration.

### Guidelines
Follow these guidelines and you will get the cleanest code. 
1. Entities should be named in singular form, ex. User, Role, UserType.
2. Models must be named after their corresponding entity and appended `Model`, ex. UserModel, RoleModel, UserTypeModel. 
3. Table names should be named after the entity in plural form, ex. users, roles, user_types. 
4. Join tables should be named after the relation names in plural form in alphabetical order, ex. roles_users. 

### Model
A basic CodeIgniter 4 model would typically look like this
```php
<?php namespace App\Models;
use App\Entities\User;
use CodeIgniter\Model;

class UserModel extends Model {

    protected $table = 'users';
    protected $returnType = User::class;
    protected $allowedFields = ['id', 'name'];

}
```
OrmExtension will do the work for you. Create your new models like this:
```php
<?php namespace App\Models;
use OrmExtension\Extensions\Model;

class UserModel extends Model {

}
```
If you follewed the guidelines, then OrmExtension will guest which table and entity are associated with the UserModel. You can however specify table and entity name by adding these methods in the UserModel class:
```php
public function getTableName() {
    return "custom_users";
}
public function getEntityName() {
    return "CustomUser";
}
```
OrmExtension doesn't really care about `$allowedFields`. It will submit a `DESCRIBE` statement and use every field in the table.

### Entity
A basic CodeIgniter 4 entity would typically look like this
```php
<?php namespace App\Entities;
use CodeIgniter\Entity;

class User extends Entity {
    public $id, $name;
}
```
OrmExtension doesn't really care about which public variables you specify. It will use the table fields when creating `INSERT` and `UPDATE` statements. So you can let them be or just remove them.
Create your new entities like this:
```php 
<?php namespace App\Entities;
use OrmExtension\Extensions\Entity;

class User extends Entity {

}
```

### Relations
Now to the fun part.
Every model can have two kind of relationships: `hasOne` and `hasMany`. 
A user can have one color and many roles. 
```php
<?php namespace App\Models;

use OrmExtension\Extensions\Model;

class UserModel extends Model {

    public $hasOne = [
        ColorModel::class,
    ];

    public $hasMany = [
        RoleModel::class
    ];

}
```
A role can have many users.
```php
<?php namespace App\Models;

use OrmExtension\Extensions\Model;

class RoleModel extends Model {

    public $hasMany = [
        UserModel::class
    ];

}
```
A color can have many users.
```php
<?php namespace App\Models;

use OrmExtension\Extensions\Model;

class ColorModel extends Model {

    public $hasMany = [
        UserModel::class
    ];

}
```
Schema for these models:
```sql
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `color_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) 

CREATE TABLE `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
)

CREATE TABLE `roles_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) 

CREATE TABLE `colors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(63) DEFAULT NULL,
  PRIMARY KEY (`id`)
) 
```
