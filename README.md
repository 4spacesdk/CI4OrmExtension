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
A basic CodeIgniter 4 model would look something like this
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
OrmExtension will do the work for you. Create model will look like this instead:
```php
<?php namespace App\Models;
use OrmExtension\Extensions\Model;

class UserModel extends Model {

}
```

### Entity


