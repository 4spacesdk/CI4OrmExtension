<?php
/** @var \OrmExtension\ModelParser\ModelItem[] $models */

foreach($models as $model) { ?>
export {<?=$model->name?> as <?=$model->name?>} from "./<?=$model->name?>";
<?php } ?>
