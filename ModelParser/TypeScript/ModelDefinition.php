<?php
/** @var \OrmExtension\ModelParser\ModelItem $model */
?>
/**
 * Created by ModelParser
 * Date: <?=date('d-m-Y')?>.
 * Time: <?=date('H:i')?>.
 */
<?php
// Import
$imported = [];
foreach($model->properties as $property) : ?>
<?php if(!$property->isSimpleType && !in_array($property->type, $imported)) :
        $imported[] = $property->type; ?>
import {<?=$property->type?>, <?=$property->type?>Interface} from "../<?=$property->type?>";
<?php endif ?>
<?php endforeach
?>

export interface <?=$model->name?>DefinitionInterface {
<?php foreach($model->properties as $property) : ?>
    <?=$property->name?>?: <?=$property->type?><?=$property->isSimpleType?'':'Interface'?><?=$property->isMany?"[]":""?>;
<?php endforeach ?>
}

export class <?=$model->name?>Definition implements <?=$model->name?>DefinitionInterface {
<?php foreach($model->properties as $property) : ?>
    <?=$property->name?>?: <?=$property->type?><?=$property->isSimpleType?'':''?><?=$property->isMany?"[]":""?>;
<?php endforeach ?>

    constructor(json?: any) {
        if(!json) return;
<?php foreach($model->properties as $property) : ?>
        if(json.<?=$property->name?> != null) {
<?php if($property->isMany): ?>
            this.<?=$property->name?> = [];
            for(let i of json.<?=$property->name?>) {
                this.<?=$property->name?>.push(new <?=$property->type?>(i));
            }
<?php else: ?>
<?php if($property->isSimpleType): ?>
            this.<?=$property->name?> = json.<?=$property->name?>;
<?php else: ?>
            this.<?=$property->name?> = new <?=$property->type?>(json.<?=$property->name?>);
<?php endif ?>
<?php endif ?>
        }
<?php endforeach ?>
    }

}