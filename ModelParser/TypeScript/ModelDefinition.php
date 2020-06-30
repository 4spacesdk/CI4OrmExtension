<?php
/** @var \OrmExtension\ModelParser\ModelItem $model */
?>
/**
 * Created by ModelParser
 */
<?php
// Import
$imported = [];
foreach($model->properties as $property) : ?>
<?php if(!$property->isSimpleType && !in_array($property->typeScriptType, $imported)) :
        $imported[] = $property->typeScriptType; ?>
import {<?=$property->typeScriptType?>} from '../<?=$property->typeScriptType?>';
<?php endif ?>
<?php endforeach
?>
import {BaseModel} from '../BaseModel';
<?php if($model->isResource && $model->getApiItem()) { ?>
import {Api} from '../../http/Api/Api';
<?php } ?>

export class <?=$model->name?>Definition extends BaseModel {
<?php foreach($model->properties as $property) : ?>
    <?=$property->name?>?: <?=$property->typeScriptType?><?=$property->isSimpleType?'':''?><?=$property->isMany?"[]":""?>;
<?php endforeach ?>

    constructor(data?: any) {
        super();
        this.populate(data);
    }

    public populate(data?: any, patch = false) {
        if (!patch) {
<?php foreach($model->properties as $property) : ?>
            delete this.<?=$property->name?>;
<?php endforeach ?>
        }

        if (!data) return;
<?php foreach($model->properties as $property) : ?>
        if (data.<?=$property->name?> != null) {
<?php if($property->isMany): ?>
            this.<?=$property->name?> = data.<?=$property->name?>.map((i: any) => new <?=$property->typeScriptType?>(i));
<?php else: ?>
<?php if($property->isSimpleType): ?>
            this.<?=$property->name?> = data.<?=$property->name?>;
<?php else: ?>
            this.<?=$property->name?> = new <?=$property->typeScriptType?>(data.<?=$property->name?>);
<?php endif ?>
<?php endif ?>
        }
<?php endforeach ?>
    }
<?php if($model->isResource && $model->getApiItem()) { ?>
<?=$model->getApiItem()->generateTypeScriptModelFunctions()?>
<?php } ?>

}
