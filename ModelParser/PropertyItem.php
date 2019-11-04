<?php namespace OrmExtension\ModelParser;
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 25/11/2018
 * Time: 12.20
 *
 * @property string $name
 * @property string $typeScriptType
 * @property string $xamarinType
 * @property string $comment
 * @property bool $isSimpleType
 * @property bool $isMany
 */
class PropertyItem {

    public $comment = null;
    public $isSimpleType = true;

    public function __construct($name = "", $type = "", $isSimpleType = true, $isMany = false) {
        $this->name = $name;
        $this->typeScriptType = $type;
        $this->isSimpleType = $isSimpleType;
        $this->isMany = $isMany;
        $this->setType($type);
    }

    public static function validate($line) {
        if(strpos($line, '@property') === false) return false;
        $line = substr($line, strlen(' * @property '));
        $parts = explode(' ', $line);
        if(count($parts) < 2) return false;
        return $parts;
    }

    public static function parse($line, $isMany = false) {
        $parts = PropertyItem::validate($line);
        if(!$parts) return false;

        $type = array_shift($parts);
        $item = new PropertyItem();
        $item->name = substr(array_shift($parts), 1);
        $item->isMany = $isMany;

        if(count($parts))
            $item->comment = implode(' ', $parts);

        $item->setType($type);

        return $item;
    }

    public function setType($type) {
        switch($type) {
            case 'int':
            case 'double':
                $this->typeScriptType = 'number';
                break;
            case 'string|double':
                $this->typeScriptType = 'string';
                break;
            case 'boolean':
            case 'bool':
                $this->typeScriptType = 'boolean';
                break;
            case 'string':
                $this->typeScriptType = 'string';
                break;
            case 'int[]':
                $this->typeScriptType = 'number[]';
                break;
            default:
                $this->typeScriptType = $type;
                $this->isSimpleType = false;
                break;
        }
        // Xamarin
        switch($type) {
            case 'string|double':
                $this->xamarinType = 'string';
                break;
            case 'boolean':
            case 'bool':
                $this->xamarinType = 'bool';
                break;
            default:
                $this->xamarinType = $type;
                break;
        }
    }

    public function toSwagger() {
        $item = [];

        if($this->isSimpleType)
            $item['type'] = $this->typeScriptType;
        else
            $item['type'] = "{$this->typeScriptType}"; //"#/components/schemas/{$this->type}";

        return $item;
    }

    public function getCamelName() {
        return camelize($this->name);
    }

}
