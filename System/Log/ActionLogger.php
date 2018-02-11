<?php

/**
 * 行動ログクラス
 */

namespace System\Log;

class ActionLogger extends AbstractLogger
{
	/**
	 * ログに書き出す
	 *
	 * @param string $message
	 * @param int    $level
	 */
	public function write($message, $path = ACTION_LOG_FILE)
	{
		$logFile = sprintf('%s.log', $path);
		$formattedMessage = sprintf('[%s] %s', (new \DateTime())->format('Y-m-d H:i:s'), $message);
		error_log($formattedMessage . PHP_EOL, parent::OUTPUT_TO_FILE, $logFile);
	}
}
