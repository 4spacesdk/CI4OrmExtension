<?php
/** @var ModelItem $model */

use OrmExtension\ModelParser\ModelItem; ?>
/**
 * Created by ModelParser
 * Date: <?=date('d-m-Y')?>.
 * Time: <?=date('H:i')?>.
 */
using System;
using <?=config('OrmExtension')->xamarinModelsNamespace?>.Definitions;

namespace <?=config('OrmExtension')->xamarinModelsNamespace?>
{
    public class <?=$model->name?> : <?=$model->name?>Definition
    {

    }
}
