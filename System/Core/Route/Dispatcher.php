<?php

/**
 * アクセスを振り分ける
 */

namespace System\Core\Route;

use System\Core\Extend\ExtendProtocol;
use System\Core\Di\Container;
use System\Core\Route\Request;
use System\Log\SystemErrorLogger;

class Dispatcher
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var ExtendProtocol
	 */
	private $extendProtocol;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var SystemErrorLogger
	 */
	private $systemErrorLogger;

	/**
	 * コンストラクタ
	 *
	 * @param Container         $container
	 * @param ExtendProtocol    $extendProtocol
	 * @param Request           $request
	 * @param SystemErrorLogger $systemErrorLogger
	 */
	public function __construct(
		Container $container,
		ExtendProtocol $extendProtocol,
		Request $request,
		SystemErrorLogger $systemErrorLogger
	) {
		$this->container         = $container;
		$this->extendProtocol    = $extendProtocol;
		$this->request           = $request;
		$this->systemErrorLogger = $systemErrorLogger;
	}

	/**
	 * 要求されたコントローラーとビューファイルに振り分ける
	 */
	public function start()
	{
		try {
			if (true === USE_FIRST_PROCESS) {
				$firstProcess = $this->container->get(FIRST_PROCESS_CLASS);
				$firstProcess->execute();
			}

			$nameSpace  = $this->request->getControllerNameSpace();
			$controller = $this->container->get($nameSpace);

			$controller->_initialize(
				$this->container,
				$this->extendProtocol,
				$this->request
			);

			$controller->before();
			$controller->_doMethod();
			$controller->after();

			$controller->_checkNeedTemplate();
			$controller->_responseJsonWhenUseJsonResponse();
		} catch (\Exception $e) {
			if (true === USE_SYSTEM_ERROR_LOG_FILE) {
				$this->systemErrorLogger->write($e->getMessage());
			}

			if (true === USE_DEBUG_MODE) {
				var_dump($e->getMessage());
				exit;
			}
			include(TEMPLATE_DIR . 'not_found.php');
		}
	}
}
