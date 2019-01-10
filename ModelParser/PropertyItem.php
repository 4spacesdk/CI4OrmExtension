<?php namespace OrmExtension\ModelParser;
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 25/11/2018
 * Time: 12.20
 *
 * @property string $name
 * @property string $type
 * @property string $comment
 * @property bool $isSimpleType
 * @property bool $isMany
 */
class PropertyItem {

    public $comment = null;
    public $isSimpleType = true;

    public function __construct($name = "", $type = "", $isSimpleType = true, $isMany = false) {
        $this->name = $name;
        $this->type = $type;
        $this->isSimpleType = $isSimpleType;
        $this->isMany = $isMany;
    }

    public static function validate($line) {
        if(strpos($line, '@property') === false) return false;
        $line = substr($line, strlen(' * @property '));
        $parts = explode(' ', $line);
        if(count($parts) < 2) return false;
        return $parts;
    }

    public static function parse($line, $isMany) {
        $parts = PropertyItem::validate($line);
        if(!$parts) return false;

        $type = array_shift($parts);
        $item = new PropertyItem();
        $item->name = substr(array_shift($parts), 1);
        $item->isMany = $isMany;

        if(count($parts))
            $item->comment = implode(' ', $parts);

        switch($type) {
            case 'int':
            case 'double':
                $item->type = 'number';
                break;
            case 'string|double':
                $item->type = 'string';
                break;
            case 'boolean':
            case 'bool':
                $item->type = 'boolean';
                break;
            case 'string':
                $item->type = 'string';
                break;
            default:
                $item->type = $type;
                $item->isSimpleType = false;
                break;
        }

        return $item;
    }

    public function toSwagger() {
        $item = [];

        if($this->isSimpleType)
            $item['type'] = $this->type;
        else
            $item['type'] = "{$this->type}"; //"#/components/schemas/{$this->type}";

        return $item;
    }

}