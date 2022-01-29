<?php

// to run this code: 
// php 1.php 0, 2, 4, 4
//           ^  ^  ^  ^
//  start.y -+  |  |  |
//  start.x - - +  |  |
//  targt.y - - -  +  |
//  targt.x - - - - - +
//
// have fun;
    
if (!isset($argv[3]) || !isset($argv[4])) {
    echo "error: target coordinats is required \n";
}

$map = [
    ['_', '_', '_', '_', '_'],
    ['x', 'x', '_', 'x', '_'],
    ['_', '_', 'x', '_', '_'],
    ['x', 'x', '_', '_', 'x'],
    ['_', '_', '_', '_', '_'],
];  

var_dump(
    pathExists(
        $map,
        [(int)$argv[1], (int)$argv[2]],
        [(int)$argv[3], (int)$argv[4]]
    )
);

die;


function pathExists(array $map, array $itemStart, array $itemTarget)
{
    if (empty($map)) {
        echo "error: map is required \n";
        return;
    }

    $mapChecked = [];
    $itemStart = [
        'y' => $itemStart[0] ?? 0,
        'x' => $itemStart[1] ?? 0,
    ];

    $itemTarget = [
        'y' => $itemTarget[0],
        'x' => $itemTarget[1],
    ];

    $itemResult = move(
        $itemStart,
        $itemStart,
        $itemTarget,
        $map,
        $mapChecked
    );

    $pathExist = $itemResult['x'] === $itemTarget['x']
        && $itemResult['y'] === $itemTarget['y'];

    return ['pathExist' => $pathExist];
}

function move(
    array $itemCurrent,
    array &$itemStart,
    array &$itemTarget,
    array &$map,
    array &$mapChecked
) {
    $mapChecked[$itemCurrent['y']][$itemCurrent['x']] = 1;

    if (isTargetFound($itemCurrent, $itemTarget)) {
        return $itemCurrent;
    }

    $itemMoveUp = [
        'y' => $itemCurrent['y'] - 1,
        'x' => $itemCurrent['x'],
    ];
    if (moveIsValid($itemMoveUp, $map, $mapChecked)) {
        $itemResult = move($itemMoveUp,$itemStart,$itemTarget,$map,$mapChecked);
        if (isTargetFound($itemResult, $itemTarget)) {
            return $itemResult;
        }
    }

    $itemMoveRight = [
        'y' => $itemCurrent['y'],
        'x' => $itemCurrent['x'] + 1,
    ];
    if (moveIsValid($itemMoveRight, $map, $mapChecked)) {
        $itemResult = move($itemMoveRight, $itemStart, $itemTarget, $map, $mapChecked);
        if (isTargetFound($itemResult, $itemTarget)) {
            return $itemResult;
        }
    }

    $itemMoveDown = [
        'y' => $itemCurrent['y'] + 1,
        'x' => $itemCurrent['x'],
    ];
    if (moveIsValid($itemMoveDown, $map, $mapChecked)) {
        $itemResult = move($itemMoveDown, $itemStart, $itemTarget, $map, $mapChecked);
        if (isTargetFound($itemResult, $itemTarget)) {
            return $itemResult;
        }
    }

    $itemMoveLeft = [
        'y' => $itemCurrent['y'],
        'x' => $itemCurrent['x'] - 1,
    ];
    if (moveIsValid($itemMoveLeft, $map, $mapChecked)) {
        $itemResult = move($itemMoveLeft, $itemStart, $itemTarget, $map, $mapChecked);
        if (isTargetFound($itemResult, $itemTarget)) {
            return $itemResult;
        }
    }

    return $itemCurrent;
}

function moveIsValid($item, array &$map, array &$mapChecked)
{
    if (empty($map[$item['y']][$item['x']])) {
        // block is not exist
        return false;
    }

    if ($map[$item['y']][$item['x']] !== '_') {
        // block is not free to go
        return false;
    }

    if (!empty($mapChecked[$item['y']][$item['x']])) {
        // block has already been visited
        return false;
    }

    return true;
}

function isTargetFound(array &$item, array &$itemTarget)
{
    return $item['x'] === $itemTarget['x']
        && $item['y'] === $itemTarget['y'];
}
