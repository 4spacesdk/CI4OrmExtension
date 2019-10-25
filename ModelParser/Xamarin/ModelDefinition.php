<?php
/** @var \OrmExtension\ModelParser\ModelItem $model */
?>
/**
 * Created by ModelParser
 */
using Newtonsoft.Json;

namespace <?=\CodeIgniter\Config\Config::get('OrmExtension')->xamarinModelsNamespace?>.Definitions
{

    [JsonObject(ItemNullValueHandling = NullValueHandling.Ignore)]
    public class <?=$model->name?>Definition : BaseModel
    {
<?php foreach($model->properties as $property) : ?>

        [JsonProperty("<?=$property->name?>")]
        public <?=$property->typeScriptType?><?=$property->isMany?"[]":""?> <?=ucfirst($property->getCamelName())?> { get; set; }
<?php endforeach ?>

    }
}
