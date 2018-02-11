<?php

/**
 * システムエラーを記録するログ
 */

namespace System\Log;

class SystemErrorLogger extends AbstractLogger
{
	/**
	 * ログに書き出す
	 *
	 * @param string $message
	 * @param int    $level
	 */
	public function write($message, $path = SYSTEM_ERROR_LOG_FILE)
	{
		$logFile = sprintf('%s.log', $path);
		$formattedMessage = sprintf('[%s] %s', (new \DateTime())->format('Y-m-d H:i:s'), $message);
		error_log($formattedMessage . PHP_EOL, parent::OUTPUT_TO_FILE, $logFile);
	}
}
