<?php

/**
 * MyWidgetTable $widget
 */
?>

<table>
    <thead>
    <tr>
        <?php foreach ($widget->fields ?? [] as $headTitle) { ?>
            <th><?= $headTitle ?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
        <?php foreach($widget->data ?? [] as $item) { ?>
        <tr> 
            <?php foreach($widget->fields ?? [] as $field) { ?>
                <td><?= $item[$field] ?? '' ?></td>
            <?php } ?>  
        </tr>
        <?php } ?>
    </tbody>
</table>
