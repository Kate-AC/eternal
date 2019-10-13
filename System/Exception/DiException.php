<?php

/**
 * Diに関するの例外クラス
 */

namespace System\Exception;

class DiException extends AbstractException
{
    /**
     * @ver string
     */
    protected $errorType = '依存解決時の例外';

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
