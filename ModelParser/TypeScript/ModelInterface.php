<?php
/** @var ModelItem $model */

use OrmExtension\ModelParser\ModelItem; ?>
/**
 * Created by ModelParser
 * Date: <?=date('d-m-Y')?>.
 * Time: <?=date('H:i')?>.
 */
<?php
// Import
$imported = [];
foreach($model->properties as $property) : ?>
<?php if(!$property->isSimpleType && !in_array($property->typeScriptType, $imported) && $property->typeScriptType != $model->name) :
        $imported[] = $property->typeScriptType; ?>
import {<?=$property->typeScriptType?>Interface} from "./<?=$property->typeScriptType?>Interface";
<?php endif ?>
<?php endforeach
?>

export interface <?=$model->name?>Interface {
<?php foreach($model->properties as $property) : ?>
    <?=$property->name?>?: <?=$property->typeScriptType?><?=$property->isSimpleType?'':'Interface'?><?=$property->isMany?"[]":""?>;
<?php endforeach ?>

}
