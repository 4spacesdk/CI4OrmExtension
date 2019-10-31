<?php
/** @var \OrmExtension\ModelParser\ModelItem $model */
?>
/**
 * Created by ModelParser
 */
using System.Collections.Generic;
using Newtonsoft.Json;

namespace <?=\CodeIgniter\Config\Config::get('OrmExtension')->xamarinModelsNamespace?>.Definitions
{

    [JsonObject(ItemNullValueHandling = NullValueHandling.Ignore)]
    public class <?=$model->name?>Definition : BaseModel
    {
<?php foreach($model->properties as $property) : ?>

        [JsonProperty("<?=$property->name?>")]
<?php if($property->isMany) { ?>
        public List<<?=$property->typeScriptType?>> <?=ucfirst($property->getCamelName())?> { get; set; }
<?php } else { ?>
        public <?=$property->typeScriptType?> <?=ucfirst($property->getCamelName())?> { get; set; }
<?php } ?>
<?php endforeach ?>

    }
}
