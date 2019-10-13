<?php

/**
 * システムの例外クラス
 */

namespace System\Exception;

class SystemException extends AbstractException
{
    /**
     * @var string
     */
    protected $errorType = 'システムの例外';

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
