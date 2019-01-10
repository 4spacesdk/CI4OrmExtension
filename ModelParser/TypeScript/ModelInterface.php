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
<?php if(!$property->isSimpleType && !in_array($property->type, $imported) && $property->type != $model->name) :
        $imported[] = $property->type; ?>
import {<?=$property->type?>Interface} from "./<?=$property->type?>Interface";
<?php endif ?>
<?php endforeach
?>

export interface <?=$model->name?>Interface {
<?php foreach($model->properties as $property) : ?>
    <?=$property->name?>?: <?=$property->type?><?=$property->isSimpleType?'':'Interface'?><?=$property->isMany?"[]":""?>;
<?php endforeach ?>

}