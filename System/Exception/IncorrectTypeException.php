<?php

/**
 * 異なる型の例外クラス
 */

namespace System\Exception;

class IncorrectTypeException extends AbstractException
{
	/**
	 * @var string
	 */
	protected $errorType = '型の不一致';

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
