<?php
/** @var ModelItem $model */

use OrmExtension\ModelParser\ModelItem; ?>
/**
 * Created by ModelParser
 * Date: <?=date('d-m-Y')?>.
 * Time: <?=date('H:i')?>.
 */
import {<?=$model->name?>Definition} from './definitions/<?=$model->name?>Definition';

export class <?=$model->name?> extends <?=$model->name?>Definition {

    constructor(json?: any) {
        super(json);
    }

}
