<?php namespace OrmExtension\ModelParser;
use Config\Services;
use DebugTool\Data;

/**
 * Created by PhpStorm.
 * User: martin
 * Date: 25/11/2018
 * Time: 12.15
 */
class ModelParser {

    /** @var ModelItem[] */
    public $models;

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

    /**
     * The models' OpenAPI 3.0 schemas, by name, for components.schemas. Unlike generateSwagger(), a
     * relation is a `$ref`, so with $references the schemas are those named and every schema they
     * refer to, in turn - and a name no model or static schema has is an object of that name, so
     * no `$ref` points at nothing.
     *
     * The static schemas of APPPATH/Schemas, and of its $scope folder, are put in as they are. A
     * model's own schema is used over a static one by its name, unless $overrideWithStatic.
     *
     * @param string[]|null $references
     * @param string|null $scope
     * @param bool $overrideWithStatic
     * @return array
     */
    public function generateOpenApi($references = null, $scope = null, $overrideWithStatic = false) {
        $pool = self::staticSchemas($scope);
        foreach($this->models as $model) {
            if($overrideWithStatic && isset($pool[$model->name])) continue;
            $pool[$model->name] = $model->toOpenApi();
        }
        return OpenApi::closure($pool, $references);
    }

    /**
     * The static schemas of APPPATH/Schemas and of its $scope folder, by name - the file name
     * without .json. One of the scope's is used over one of the same name in APPPATH/Schemas.
     *
     * @param string|null $scope
     * @return array
     */
    public static function staticSchemas($scope = null) {
        $schemas = [];
        $paths = $scope ? [self::$staticPath, self::$staticPath . '/' . $scope] : [self::$staticPath];
        foreach($paths as $path) {
            foreach(self::loadStatics($path) as $file) {
                $schemas[substr($file, 0, -5)] = json_decode(file_get_contents($path . '/' . $file));
            }
        }
        return $schemas;
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
     * @param string $model a full class name
     * @return ModelItem
     */
    private static function parseModels($model) {
        return ModelItem::parse($model);
    }

    /**
     * The entities to export: those of App\Entities, and of every namespace in
     * Config\OrmExtension::$exportNamespace when it says more. With $includeInterfaces the
     * interfaces of App\Interfaces, or of Config\OrmExtension::$exportInterfaceNamespace.
     *
     * Not $entityNamespace: RestExtension adds its own log entities to that, which no app exports.
     *
     * @return string[] full class names
     */
    private static function loadModels($includeInterfaces = false) {
        $models = Namespaces::classes(self::namespaces('exportNamespace', 'App\\Entities'));
        if ($includeInterfaces) {
            $models = array_merge($models, Namespaces::classes(self::namespaces('exportInterfaceNamespace', 'App\\Interfaces')));
        }
        return $models;
    }

    /**
     * @return string[]
     */
    private static function namespaces($property, $default) {
        $config = '\\Config\\OrmExtension';
        $namespaces = class_exists($config) && property_exists($config, $property) ? $config::$$property : $default;
        return is_array($namespaces) ? $namespaces : [$namespaces];
    }

    private static function loadStatics($path = null) {
        $path = $path ?? self::$staticPath;
        $schemas = [];
        if(is_dir($path)) {
            $files = scandir($path);
            foreach($files as $file) {
                if($file[0] != '_' && substr($file, -4) == 'json') {
                    $schemas[] = $file;
                }
            }
        }
        return $schemas;
    }
}
