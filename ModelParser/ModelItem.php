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

    /**
     * @param $path
     * @return bool|ModelItem
     */
    public static function parse($path) {
        $item = new ModelItem();
        $item->path = $path;

        $isEntity = true;
        try {
            $rc = new \ReflectionClass("\App\Entities\\{$path}");
            try {
                $item->isResource = $rc->implementsInterface('\RestExtension\ResourceEntityInterface');
            } catch(Exception $e) {

            }
        } catch(\Exception $e) {
            try {
                $rc = new \ReflectionClass("\App\Interfaces\\{$path}");
            } catch(\Exception $e) {
                return false;
            }
            $isEntity = false;
        }
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
        }

        return $item;
    }

    /**
     * @return bool|ApiItem
     */
    public function getApiItem() {
        $entityName = "\App\Entities\\{$this->path}";
        /** @var Entity $entity */
        $entity = new $entityName();
        try {
            return ApiItem::parse($entity->getResourcePath());
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
