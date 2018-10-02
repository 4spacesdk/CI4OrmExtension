# CodeIgniter 4 OrmExtension

## What is CodeIgniter 4 OrmExtension?
OrmExtension is an Object Relational Mapper written in PHP for CodeIgniter 4. 
It is designed to map your Database tables into easy to work with objects, fully aware of the relationships between each other.

## Installation
Step 1)
`composer require 4spacesdk/ci4ormextension`

Step 2)
Create new file `application/Config/OrmExtension.php` and add this content
```
<?php namespace Config;
class OrmExtension {
    public static $modelNamespace = 'App\Models\\';
    public static $entityNamespace = 'App\Entities\\';
}
```
Update the namespace to fit your project.

Step 3)
Add this to your `application/Config/Events.php` file
``Events::on('pre_system', [\OrmExtension\Hooks\PreController::class, 'execute']);``

## Usage
