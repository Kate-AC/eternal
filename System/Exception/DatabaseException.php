<?php

/**
 * データベースに関する例外クラス
 */

namespace System\Exception;

class DatabaseException extends AbstractException
{
    /**
     * @var string
     */
    protected $errorType = 'データベースに関する例外';

    /**
     * コンストラクタ
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
