<?php

/**
 * コントローラーの例外クラス
 */

namespace System\Exception;

class ControllerException extends AbstractException
{
    /**
     * @var string
     */
    protected $errorType = 'コントローラーの例外';

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
