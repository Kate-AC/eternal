<?php

/**
 * デバッグ用関数
 */

/**
 * var_dump
 */
function v()
{
    $list = debug_backtrace();
    var_dump([
        'File' => $list[0]['file'],
        'Line' => $list[0]['line'],
        'Args' => func_get_args()
    ]);
}

/**
 * var_dump + exit
 */
function ve()
{
    $list = debug_backtrace();
    var_dump([
        'File' => $list[0]['file'],
        'Line' => $list[0]['line'],
        'Args' => func_get_args()
    ]);
    exit;
}

/**
 * メモリ使用量を調べる
 */
function mem()
{
    var_dump(sprintf('使用メモリ: %.5fMB, 最大使用メモリ: %.5fMB',
        memory_get_usage() / (1024 * 1024),
        memory_get_peak_usage() / (1024 * 1024)
    ));
    exit;
}
