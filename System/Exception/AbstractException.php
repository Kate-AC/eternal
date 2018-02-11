<?php

/**
 * 独自例外クラス
 */

namespace System\Exception;

abstract class AbstractException extends \Exception
{
	/**
	 * コンストラクタ
	 *
	 * @param string $message
	 */ 
	 public function __construct($message)
	{
		$traceList = array_reverse($this->getTrace());

		$str  = "";
		$str .= sprintf('errorType: %s,', $this->errorType) . PHP_EOL;
		$str .= sprintf('message: %s,', $message) . PHP_EOL;
		$str .= sprintf('file: %s,', $this->getFile()) . PHP_EOL;
		$str .= sprintf('line: %s,', $this->getLine()) . PHP_EOL;

		foreach ($traceList as $i => $trace) {
			$str .= sprintf('[%s]', $i) . PHP_EOL;
			if (isset($trace['file'])) {
				$str .= sprintf('file: %s,', $trace['file']) . PHP_EOL;
			}
			$str .= sprintf('function: %s,', $trace['function']) . PHP_EOL;
			$strTrace = "";
			foreach ($trace['args'] as $j => $t) {
				if (is_object($t)) {
					$t = 'Object';
				}

				if (is_array($t)) {
					$t = 'Array';
				}

				$strTrace .= sprintf('%s', $t);
				if ($j < count($trace['args']) - 1) {
					$strTrace .= ', ';
				}
			}
			$str .= sprintf('args: (%s)', $strTrace) . PHP_EOL;
		}

		parent::__construct($str);
	}

	/**
	 * エラータイプの文言を返す
	 *
	 * @return string
	 */
	public function getErrorType()
	{
		return $this->errorType;
	}
}
