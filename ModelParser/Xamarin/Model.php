<?php
/** @var ModelItem $model */

use OrmExtension\ModelParser\ModelItem; ?>
/**
 * Created by ModelParser
 * Date: <?=date('d-m-Y')?>.
 * Time: <?=date('H:i')?>.
 */
using System;
using <?=\CodeIgniter\Config\Config::get('OrmExtension')->xamarinModelsNamespace?>.Definitions;

namespace <?=\CodeIgniter\Config\Config::get('OrmExtension')->xamarinModelsNamespace?>
{
    public class <?=$model->name?> : <?=$model->name?>Definition
    {

    }
}
