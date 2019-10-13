<?php

/**
 * 抽象Loggerクラス
 */

namespace System\Log;

abstract class AbstractLogger
{
    const OUTPUT_TO_FILE = 3;

    /**
     * ログに書き出す
     *
     * @param string $message
     * @param int    $level
     */
    abstract public function write($message, $path);
}
