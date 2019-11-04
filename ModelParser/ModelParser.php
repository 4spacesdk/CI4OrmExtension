<?php namespace OrmExtension\ModelParser;
use Config\Services;
use DebugTool\Data;

/**
 * Created by PhpStorm.
 * User: martin
 * Date: 25/11/2018
 * Time: 12.15
 *
 * @property ModelItem[] $models
 */
class ModelParser {

    private static $staticPath = APPPATH. 'Schemas';
    private static $interfacesPath = APPPATH. 'Interfaces';

    /**
     * @param bool $includeInterfaces
     * @return ModelParser
     */
    public static function run($includeInterfaces = false) {
        $parser = new ModelParser();
        /** @var ModelItem[] $models */
        $models = [];
        foreach(ModelParser::loadModels($includeInterfaces) as $model) {
            $modelItem = ModelParser::parseModels($model);
            if($modelItem)
                $models[] = $modelItem;
        }
        Data::debug("Found ".count($models)." models");
        $parser->models = $models;
        return $parser;
    }

    public function generateSwagger($overrideWithStatic = false, $schemaReferences = null, $scope = null) {
        $json = [];

        // Append Static Schemas (From Schemas folder)
        $schemas = self::loadStatics();
        foreach($schemas as $schema) {
            $schemaName = substr($schema, 0, -5);
            $json[$schemaName] = json_decode(file_get_contents(self::$staticPath . '/' . $schema));
        }

        // Append schemaReferences recursively
//        $list = [];
//        foreach($schemaReferences as $schemaReference) {
//            $this->appendSchemaReferencesRecursively($schemaReference, $list);
//        }
//        $schemaReferences = $list;

        if($scope) {
            self::$staticPath = self::$staticPath.'/'.$scope;
            $schemas = self::loadStatics();
            foreach($schemas as $schema) {
                $schemaName = substr($schema, 0, -5);
                $json[$schemaName] = json_decode(file_get_contents(self::$staticPath . '/' . $schema));
            }
        }

        foreach($this->models as $model) {
            if($schemaReferences && !in_array($model->name, $schemaReferences)) continue;

            $staticName = $model->name.'.json';
            if($overrideWithStatic && in_array($staticName, $schemas)) {
                $schema = str_replace("\n", '', file_get_contents(self::$staticPath . '/' . $staticName));
                $jsonSchema = json_decode($schema);
                $json[$model->name] = $jsonSchema ? $jsonSchema : $schema;
            } else
                $json[$model->name] = $model->toSwagger();
        }
        return $json;
    }

    private function appendSchemaReferencesRecursively($name, &$list) {
        if(in_array($name, $list)) return;
        $list[] = $name;

        $modelItem = ModelItem::parse($name);
        foreach($modelItem->properties as $property) {
            if(!$property->isSimpleType)
                $this->appendSchemaReferencesRecursively($property->typeScriptType, $list);
        }
    }

    public function generateTypeScript($debug = false) {
        if(!file_exists(WRITEPATH.'tmp/models/definitions/')) mkdir(WRITEPATH.'tmp/models/definitions/', 0777, true);

        $renderer = Services::renderer(__DIR__.'/TypeScript', null, false);

        foreach($this->models as $model) {
            Data::debug($model->name.' with '.count($model->properties).' properties');

            // Definition
            $content = $renderer->setData(['model' => $model], 'raw')->render('ModelDefinition', ['debug' => false], null);
            if($debug) echo $content;
            else
                file_put_contents(WRITEPATH.'tmp/models/definitions/'.$model->name.'Definition.ts', $content);

            // Model
            $content = $renderer->setData(['model' => $model], 'raw')->render('Model', ['debug' => false], null);
            if($debug) echo $content;
            else
                file_put_contents(WRITEPATH.'tmp/models/'.$model->name.'.ts', $content);

        }

        $content = $renderer->setData(['models' => $this->models], 'raw')->render('Index', ['debug' => false], null);
        file_put_contents(WRITEPATH.'tmp/models/index.ts', $content);
    }

    public function generateXamarin($debug = false) {
        if(!file_exists(WRITEPATH.'tmp/xamarin/models/Definitions/')) mkdir(WRITEPATH.'tmp/xamarin/models/Definitions/', 0777, true);

        $renderer = Services::renderer(__DIR__.'/Xamarin', null, false);

        foreach($this->models as $model) {
            Data::debug($model->name.' with '.count($model->properties).' properties');

            // Definition
            $content = $renderer->setData(['model' => $model], 'raw')->render('ModelDefinition', ['debug' => false], null);
            if($debug) echo $content;
            else
                file_put_contents(WRITEPATH.'tmp/xamarin/models/Definitions/'.$model->name.'Definition.cs', $content);

            // Model
            $content = $renderer->setData(['model' => $model], 'raw')->render('Model', ['debug' => false], null);
            if($debug) echo $content;
            else
                file_put_contents(WRITEPATH.'tmp/xamarin/models/'.$model->name.'.cs', $content);

        }
    }


    /**
     * @param $model
     * @return ModelItem
     */
    private static function parseModels($model) {
        return ModelItem::parse(substr($model, 0, -4));
    }

    private static function loadModels($includeInterfaces = false) {
        $files = scandir(APPPATH. 'Entities');
        if($includeInterfaces && is_dir(APPPATH. 'Interfaces')) {
            $files = array_merge($files, scandir(APPPATH . 'Interfaces'));
        }

        $models = [];
        foreach($files as $file) {
            if($file[0] != '_' && substr($file, -3) == 'php') {
                $models[] = $file;
            }
        }
        return $models;
    }

    private static function loadStatics() {
        $schemas = [];
        if(is_dir(self::$staticPath)) {
            $files = scandir(self::$staticPath);
            foreach($files as $file) {
                if($file[0] != '_' && substr($file, -4) == 'json') {
                    $schemas[] = $file;
                }
            }
        }
        return $schemas;
    }
}
