<?php
/** @var ModelItem $model */

use OrmExtension\ModelParser\ModelItem; ?>
/**
 * Created by ModelParser
 * Date: <?=date('d-m-Y')?>.
 * Time: <?=date('H:i')?>.
 */
import {<?=$model->name?>Definition, <?=$model->name?>DefinitionInterface} from './definitions/<?=$model->name?>Definition';

export interface <?=$model->name?>Interface extends <?=$model->name?>DefinitionInterface {

}

export class <?=$model->name?> extends <?=$model->name?>Definition implements <?=$model->name?>Interface {

    constructor(json?: any) {
        super(json);
    }

}
