<?php

/**
 * ベースとなるコントローラー
 */

namespace System\Core\Route;

use System\Core\Di\Container;
use System\Core\Extend\ExtendProtocol;
use System\Core\Extend\Module\RenderModule;
use System\Exception\ControllerException;

class BaseController
{
	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var ExtendProtocol
	 */
	protected $extendProtocol;

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var string[]
	 */
	protected $_variableList = [];

	/**
	 * @var string
	 */
	protected $_useClassForView = [];

	/**
	 * @var boolean
	 */
	protected $_useJsonResponse = false;

	/**
	 * @var string
	 */
	protected $_useTemplate = null;

	/**
	 * @var string
	 */
	protected $_useMethod = null;

	/**
	 * 必要なクラスをセットする
	 *
	 * @param Container      $container
	 * @param ExtendProtocol $extendProtocol
	 * @param Request        $request
	 */
	final public function _initialize(
		Container $container,
		ExtendProtocol $extendProtocol,
		Request $request
	) {
		$this->container      = $container;
		$this->extendProtocol = $extendProtocol;
		$this->request        = $request;
	}

	/**
	 * ルーティング先のメソッドを実行する
	 */
	final public function _doMethod()
	{
		$method = $this->request->getControllerMethod();
		$this->_checkMethodExist($this, $method);
		$this->_useMethod = $method;
		$this->$method();
	}

	/**
	 * メソッドの存在を確認する
	 *
	 * @param object $class
	 * @param string $method
	 * @throw ControllerException
	 */
	private function _checkMethodExist($class, $method)
	{
		if (!method_exists($class, $method)) {
			throw new ControllerException(sprintf(
				'存在しないメソッドを参照した (Controller: %s, Method: %s)',
				get_class($class),
				$method
			)); 
		}
	}

	/**
	 * レンダリングする
	 * 
	 * @param string $template
	 * @throw ControllerException
	 */
	final protected function render($template)
	{
		if (true === $this->_useJsonResponse) {
			throw new ControllerException(sprintf(
				'JsonResponseを使用する場合にビューは指定できない (Controller: %s, Method: %s)',
				get_class($this),
				$this->_useMethod
			)); 
		}

		$template = sprintf('%s%s.%s', TEMPLATE_DIR, $template, TEMPLATE_EXTENSION);

		$this->_useTemplate = $template;
		$this->_existTemplate($template);
		$this->_includeTemplate($template);
	}

	/**
	 * テンプレートの存在確認を行う
	 *
	 * @param string $template
	 */
	private function _existTemplate($template)
	{
		if (!file_exists($template)) {
			throw new ControllerException(sprintf(
				'存在しないテンプレートを指定した (Controller: %s, Method: %s, Template: %s)',
				get_class($this),
				$this->_useMethod,
				$template
			)); 
		}
	}

	/**
	 * テンプレートをインクルードする
	 *
	 * @param string $template
	 * @return boolean
	 */
	private function _includeTemplate($template)
	{
		foreach ($this->_useClassForView as $shortName => $namespace) {
			RenderModule::get()->setClassForView($shortName, $namespace);
		}

		foreach ($this->_variableList as $variable => $value) {
			${$variable} = $value;
		}

		$includedFileList = get_included_files();
		if (!in_array($template, $includedFileList, true)) {
			$this->extendProtocol->setModule(RenderModule::get())->start();
			include_once(sprintf('%s://%s', ExtendProtocol::PROTOCOL, $template));
			$this->extendProtocol->end();
		}
	}

	/**
	 * 変数をビューにセットする
	 *
	 * @param string $valiable
	 * @param mixed  $value
	 */
	final protected function set($variable = null, $value = null)
	{
		if (is_null($variable)) {
			throw new ControllerException(sprintf(
				'第一引数に文字列がセットされていない (Controller: %s, Method: set)', get_class($this)
			)); 
		}

		if (!is_string($variable)) {
			throw new ControllerException(sprintf(
				'第一引数が文字列ではない (Controller: %s, Method: set)', get_class($this)
			)); 
		}

		if (is_null($value)) {
			$list = explode('\\', $variable);
			$this->_useClassForView[end($list)] = str_replace('\\', '\\\\', $variable);
		}

		$this->_variableList[$variable] = $value;
	}

	/**
	 * リダイレクトする
	 *
	 * @param string $to
	 */
	protected function redirect($to = '/')
	{
		$url = sprintf('Location: //%s%s', $this->request->server('SERVER_NAME'), $to);
		header($url);
		exit;
	}

	/**
	 * ルーティング前に実行する処理をオーバーライドして記述
	 */
	public function before()
	{
	}

	/**
	 * ルーティング後に実行する処理をオーバーライドして記述
	 */
	public function after()
	{
	}

	/**
	 * ビューに描画せずにJSONを返すようにする
	 */
	final protected function useJsonResponse()
	{
		$this->_useJsonResponse = true;
	}

	/**
	 * テンプレートを使用する必要性があるか確認する
	 * 
	 * @throws ControllerException
	 */
	final public function _checkNeedTemplate()
	{
		if (is_null($this->_useTemplate) && false === $this->_useJsonResponse) {
			throw new ControllerException(sprintf(
				'ビューが指定されていない (Controller: %s, Method: %s)',
				get_class($this),
				$this->_useMethod
			)); 
		}
	}

	/**
	 * JsonResponseの場合にJson形式にして返す
	 */
	final public function _responseJsonWhenUseJsonResponse()
	{
		if (true === $this->_useJsonResponse) {
			header("Content-Type: application/json; charset=UTF-8");
			echo json_encode($this->_variableList);
		}
	}
}

