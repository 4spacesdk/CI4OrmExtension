<?php namespace OrmExtension\ModelParser;
use Jobby\Exception;
use RestExtension\ApiParser\ApiItem;
use RestExtension\Core\Entity;

/**
 * Created by PhpStorm.
 * User: martin
 * Date: 25/11/2018
 * Time: 12.18
 *
 * @property string $path
 * @property string $name
 * @property PropertyItem[] $properties
 * @property boolean $isResource
 */
class ModelItem {

    public $properties = [];

    /** @var string the entity's or interface's full class name */
    public $className;

    /**
     * @param $path
     * @return bool|ModelItem
     */
    public static function parse($path) {
        $item = new ModelItem();

        // A full class name, from any entity or interface namespace; a short name is the app's
        $classes = class_exists($path) || interface_exists($path) ? [$path] : ["\\App\\Entities\\{$path}", "\\App\\Interfaces\\{$path}"];
        $rc = null;
        foreach ($classes as $class) {
            if (class_exists($class) || interface_exists($class)) {
                $rc = new \ReflectionClass($class);
                break;
            }
        }
        if ($rc === null) {
            return false;
        }
        $isEntity = !$rc->isInterface();
        if ($isEntity) {
            $item->isResource = $rc->implementsInterface('\\RestExtension\\ResourceEntityInterface');
        }
        $item->className = $rc->getName();
        $item->path = $rc->getShortName();
        $item->name = substr($rc->getName(), strrpos($rc->getName(), '\\') + 1);

        $comments = $rc->getDocComment();
        $lines = explode("\n", $comments);
        $isMany = false;
        foreach($lines as $line) {
            if(strpos($line, 'Many') !== false) $isMany = true;
            if(strpos($line, 'OTF') !== false) $isMany = false;
            $property = PropertyItem::parse($line, $isMany);
            if($property)
                $item->properties[] = $property;
        }

        // Append static properties
        if($isEntity) {
            $item->properties[] = new PropertyItem('id', 'int', true, false);
            $item->properties[] = new PropertyItem('created', 'string', true, false);
            $item->properties[] = new PropertyItem('updated', 'string', true, false);
            $item->properties[] = new PropertyItem('created_by_id', 'int', true, false);
            $item->properties[] = new PropertyItem('created_by', 'User', false, false);
            $item->properties[] = new PropertyItem('updated_by_id', 'int', true, false);
            $item->properties[] = new PropertyItem('updated_by', 'User', false, false);
            $item->properties[] = new PropertyItem('deletion_id', 'int', true, false);
            $item->properties[] = new PropertyItem('deletion', 'Deletion', false, false);
        }

        return $item;
    }

    /**
     * @return bool|ApiItem
     */
    public function getApiItem() {
        $entityName = $this->className;
        /** @var Entity $entity */
        $entity = new $entityName();
        // The controller's class as it has always been put together: the namespace and the path
        // as they are, without a separator between them
        $config = config('RestExtension');
        $namespace = $config->apiControllerNamespace ?? '';
        $namespace = is_array($namespace) ? (string)reset($namespace) : (string)$namespace;
        try {
            return ApiItem::parse($namespace . $entity->getResourcePath());
        } catch(\ReflectionException $e) {
        }
        return false;
    }

    public function toSwagger() {
        $item = [
            'title'         => $this->name,
            'type'          => 'object',
            'properties'    => []
        ];
        foreach($this->properties as $property) {
            $item['properties'][$property->name] = $property->toSwagger();
        }
        return $item;
    }

}
