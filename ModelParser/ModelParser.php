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

    /**
     * @throws \ReflectionException
     */
    public static function run() {
        $parser = new ModelParser();
        /** @var ModelItem[] $models */
        $models = [];
        foreach(ModelParser::loadModels() as $model) {
            $models[] = ModelParser::parseModels($model);
        }
        Data::debug("Found ".count($models)." models");
        $parser->models = $models;
        return $parser;
    }

    public function generateSwagger() {
        $json = [];
        foreach($this->models as $model) {
            $json[$model->name] = $model->toSwagger();
        }
        return $json;
    }

    public function generateTypeScript() {
        if(!file_exists(WRITEPATH.'tmp/models/definitions/')) mkdir(WRITEPATH.'tmp/models/definitions/', 0777, true);

        $renderer = Services::renderer(__DIR__.'/TypeScript', null, false);

        foreach($this->models as $model) {
            Data::debug($model->name.' with '.count($model->properties).' properties');

            // Definition
            //$content = view('ModelParser/TypeScript/ModelDefinition', ['model' => $model], ['debug' => false]);
            $content = $renderer->setData(['model' => $model], 'raw')->render('ModelDefinition', ['debug' => false], null);
            file_put_contents(WRITEPATH.'tmp/models/definitions/'.$model->name.'Definition.ts', $content);

            // Model
            //$content = view('ModelParser/TypeScript/Model', ['model' => $model], ['debug' => false]);
            $content = $renderer->setData(['model' => $model], 'raw')->render('Model', ['debug' => false], null);
            file_put_contents(WRITEPATH.'tmp/models/'.$model->name.'.ts', $content);

        }
    }


    /**
     * @param $model
     * @return ModelItem
     * @throws \ReflectionException
     */
    private static function parseModels($model) {
        return ModelItem::parse(substr($model, 0, -4));
    }

    private static function loadModels() {
        $files = scandir(APPPATH. 'Entities');
        $models = [];
        foreach($files as $file) {
            if($file[0] != '_' && substr($file, -3) == 'php') {
                $models[] = $file;
            }
        }
        return $models;
    }
}