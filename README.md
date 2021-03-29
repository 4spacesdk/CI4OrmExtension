# CodeIgniter 4 OrmExtension

## What is CodeIgniter 4 OrmExtension?
OrmExtension is an Object Relational Mapper written in PHP for CodeIgniter 4. 
It is designed to map your Database tables into easy to work with objects, fully aware of the relationships between each other.
OrmExtension is based on the same idea as the original [WanWizard DataMapper](https://datamapper.wanwizard.eu/) for CodeIgniter 2. But totally rewritten to fit CodeIgniter 4.


## Installation
Step 1)

`composer require 4spacesdk/ci4ormextension`

Step 2)

Create new file `app/Config/OrmExtension.php` and add this content
```php
<?php namespace Config;
class OrmExtension {
    public static $modelNamespace = ['App\Models\\'];
    public static $entityNamespace = ['App\Entities\\'];

    /*
     * Provide Namespace for Xamarin models folder
     */
    public $xamarinModelsNamespace          = 'App.Models';
    public $xamarinBaseModelNamespace       = 'App.Models';
}
```
Update the namespace to fit your project. Use arrays if you have multiple namespaces for models and entities.

Step 3)

Add this line to your `app/Config/Events.php` file 
```php
Events::on('pre_system', [\OrmExtension\Hooks\PreController::class, 'execute']);
```

NB!

Remember to add composer to CodeIgniter. Check that `app/Config/Constants.php COMPOSER_PATH` is correct.   
Remember to add the `/writable/cache`-folder. Unless you will get performance decrease when having many models and relations.  

## Usage
Check the Examples folder for inspiration.


### Guidelines
1. Entities should be named in singular form, ex. User, Role, UserType.
2. Models must be named after their corresponding entity and appended `Model`, ex. UserModel, RoleModel, UserTypeModel. 
3. Table names should be named after the entity in plural form, ex. users, roles, user_types. 
4. Join tables should be named after the relation names in plural form in alphabetical order, ex. roles_users. 


### Model
A basic CodeIgniter 4 model would typically look like this:
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
If we followed the guidelines, then OrmExtension will guess which table and entity are associated with the UserModel. We can however specify table and entity name by adding these methods in the UserModel class:
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
A basic CodeIgniter 4 entity would typically look like this:
```php
<?php namespace App\Entities;
use CodeIgniter\Entity;

class User extends Entity {
    public $id, $name;
}
```
OrmExtension doesn't really care about which public variables we specify. It will use the table fields when creating `INSERT` and `UPDATE` statements. So we can let them be or just remove them.
Create new entities like this:
```php 
<?php namespace App\Entities;
use OrmExtension\Extensions\Entity;

class User extends Entity {

}
```

### Relations
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
The equivalent tables:
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
The three models covers four tables. Because the relationsship between user and role is many-to-many which results in a join table. We don't need to create a model for the join table. 

It is important to really understand what is going on here. We got three models covering three entities and relationships between them. 
We should always specify a model's relationsships. 


### Querying
When we got the models, entities and relationsships we can start using them for some clever programming!

#### Simple examples
We can work with relations without think about joining. 
Select users with the color named green:
```php
$userModel = new UserModel();
$users = $userModel
    ->whereRelated(ColorModel::class, 'name', 'green')
    ->find();
```
Select users with the role admin and color blue:
```php
$userModel = new UserModel();
$users = $userModel
    ->whereRelated(RoleModel::class, 'name', 'admin')
    ->whereRelated(ColorModel::class, 'name', 'blue')
    ->find();
```
Select all users and include their color:
```php
$userModel = new UserModel();
$users = $userModel
    ->includeRelated(ColorModel::class)
    ->find();
```
Select users with role admin and include their color:
```php
$userModel = new UserModel();
$users = $userModel
    ->includeRelated(ColorModel::class)
    ->whereRelated(RoleModel::class, 'name', 'admin')
    ->find(); 
```

#### Result
The return from `find()` has been changed. `Find` will always return a entity class related to the calling model. It will never be null or an array of entities. This is a good think - because now we have some consistent to work with. The entity is traversable, so we can use it in a loop!
Check these examples:
```php
$userModel = new UserModel();
$user = $userModel
    ->where('id', 1)
    ->find(); 
echo json_encode($user->toArray());
```
```json
{
    "id": 1,
    "name": "Martin"
}
```

```php
$userModel = new UserModel();
$users = $userModel->find(); 
echo json_encode($users->allToArray());
```
```json
[
    {
        "id": 1,
        "name": "Martin"
    },
    {
        "id": 2,
        "name": "Kevin"
    }
]
```

`toArray()` returns an array with one user's properties. `allToArray()` returns an array of multiple user's properties. These methods are great for json encoding. 


### Working with Entities
Relations can be accessed as magic properties. 
This will echo null, because the color is an empty entity. It has not yet been retrieved from the database.
```php
$userModel = new UserModel();
$users = $userModel->find(); 
foreach($users as $user) {
    echo $user->color->name;
}
```

We can retrieve the color with an include:
```php
$userModel = new UserModel();
$users = $userModel
    ->includeRelated(ColorModel::class)
    ->find(); 
foreach($users as $user) {
    echo $user->color->name;
}
```
This will echo the actual color name, because OrmExtension has prefetched the color from the `find`.

We can also retrieve the color afterwards:
```php
$userModel = new UserModel();
$users = $userModel->find(); 
foreach($users as $user) {
    $user->color->find();
    echo $user->color->name; 
}
```

A user can have multiple roles and we want to access only the role named admin. For this we have to access the model from the entity to do a `where`.
```php
$userModel = new UserModel();
$user = $userModel->find(1); 
$role = $user->roles->_getModel()
    ->where('name', 'admin')
    ->find();
echo $role->name; // "admin"
```


### Deep relations
For this purpose we will look at another example. Let's say an `user` has `books` and `books` has `color`. A `book` is shared between many users but can only have one color. A color can be shared between many books.
```php
class UserModel {
    public $hasMany = [
        BookModel::class
    ];
}
class BookModel {
    public $hasOne = [
        ColorModel::class
    ],
    public $hasMany = [
        UserModel::class
    ];
}
class ColorModel {
    public $hasMany = [
        BookModel::class
    ];
}
```

We want to select all users with green books.
```php
$userModel = new UserModel();
$users = $userModel
    ->whereRelated([BookModel::class, ColorModel::class], 'name', 'green')
    ->find();
```
To access deep relations, simply put them in an array. 


### Soft deletion
OrmExtension provides an extended soft deletion. Create a model and entity for `Deletion`.  
**Entity**
```php
<?php namespace App\Entities;
use OrmExtension\Extensions\Entity;

/**
 * Class Deletion
 * @package App\Entities
 * @property int $id
 * @property int $created_by_id
 * @property string|double $created
 */
class Deletion extends Entity {

}
```
**Model**
```php
<?php namespace App\Models;
use OrmExtension\Extensions\Model;

/**
 * Class DeletionModel
 * @package App\Models
 */
class DeletionModel extends Model {

    public $hasOne = [
        
    ];

    public $hasMany = [
        
    ];

}
```
Add a field called `deletion_id` to the models you want to soft delete. OrmExtension will look for this entity at deletion. 
Insert a `Deletion` and save the relation as `deletion_id` on the deleted entity. This is useful if you want to log who and when the entity was deleted.
You can overwrite `save()` on `Deletion`-entity and add the desired data. Ex. `user_id/created_by_id`, `ip_address`, `created_at`.  


### Model Parser
You can use the model parser to generate Swagger documentation and TypeScript models.
```php
$parser = ModelParser::run();
$schemes = $parser->generateSwagger();
```
Attach `$schemes` to swagger components and you have all your models documented.
```php
$parser = ModelParser::run();
$parser->generateTypeScript();
```
This will generate typescript models as classes and interfaces. Find the files under `writeable/tmp`.

