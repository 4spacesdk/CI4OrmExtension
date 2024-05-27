<?php
/** @var \OrmExtension\ModelParser\ModelItem $model */
?>
/**
 * Created by ModelParser
 */
using System.Collections.Generic;
using Newtonsoft.Json;
using <?=config('OrmExtension')->xamarinBaseModelNamespace?>;

namespace <?=config('OrmExtension')->xamarinModelsNamespace?>.Definitions
{

    [JsonObject(ItemNullValueHandling = NullValueHandling.Ignore)]
    public class <?=$model->name?>Definition : BaseModel
    {
<?php foreach($model->properties as $property) : ?>

        [JsonProperty("<?=$property->name?>")]
<?php if($property->isMany) { ?>
        public List<<?=$property->xamarinType?>> <?=ucfirst($property->getCamelName())?> { get; set; }
<?php } else { ?>
        public <?=$property->xamarinType?> <?=ucfirst($property->getCamelName())?> { get; set; }
<?php } ?>
<?php endforeach ?>

    }
}
