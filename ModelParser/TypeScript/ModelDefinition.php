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
<?php if(!$property->isSimpleType && !in_array($property->type, $imported)) :
        $imported[] = $property->type; ?>
import {<?=$property->type?>, <?=$property->type?>Interface} from '../<?=$property->type?>';
<?php endif ?>
<?php endforeach
?>
import {BaseModel} from '../BaseModel';
<?if($model->isResource && $model->getApiItem()) { ?>
import {Api} from '../../http/Api/Api';
<?} ?>

export interface <?=$model->name?>DefinitionInterface {
<?php foreach($model->properties as $property) : ?>
    <?=$property->name?>?: <?=$property->type?><?=$property->isSimpleType?'':'Interface'?><?=$property->isMany?"[]":""?>;
<?php endforeach ?>
}

export class <?=$model->name?>Definition extends BaseModel implements <?=$model->name?>DefinitionInterface {
<?php foreach($model->properties as $property) : ?>
    <?=$property->name?>?: <?=$property->type?><?=$property->isSimpleType?'':''?><?=$property->isMany?"[]":""?>;
<?php endforeach ?>

    constructor(data?: any) {
        super();
        this.populate(data);
    }

    public populate(data?: any, patch: boolean = false) {
        if (!patch) {
<?php foreach($model->properties as $property) : ?>
            delete this.<?=$property->name?>;
<?php endforeach ?>
        }

        if (!data) return;
<?php foreach($model->properties as $property) : ?>
        if (data.<?=$property->name?> != null) {
<?php if($property->isMany): ?>
            this.<?=$property->name?> = data.<?=$property->name?>.map((i: any) => new <?=$property->type?>(i));
<?php else: ?>
<?php if($property->isSimpleType): ?>
            this.<?=$property->name?> = data.<?=$property->name?>;
<?php else: ?>
            this.<?=$property->name?> = new <?=$property->type?>(data.<?=$property->name?>);
<?php endif ?>
<?php endif ?>
        }
<?php endforeach ?>
    }
<?if($model->isResource && $model->getApiItem()) { ?>
<?=$model->getApiItem()->generateTypeScriptModelFunctions()?>
<? } ?>

}
