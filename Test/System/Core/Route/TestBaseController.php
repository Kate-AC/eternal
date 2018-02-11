<?php

/**
 * BaseControllerのテスト
 */

namespace Test\System\Core\Route;

use System\Core\Di\Container;
use System\Core\Extend\ExtendProtocol;
use System\Core\Extend\Module\RenderModule;
use System\Core\Route\Request;
use System\Exception\ControllerException;
use Test\Mock;
use Test\TestHelper;

use Test\TestController;

class TestBaseController extends TestHelper
{
	/**
	 * @var testController
	 */
	private $testController;

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * コンストラクタ
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * 共通の部品を作成する
	 */
	private function common()
	{
		$method = 'testAction';
		$mock = Mock::m('System\Core\Route\Request')
			->_setMethod('getControllerMethod')
			->_setArgs()
			->_setReturn($method)
			->e();
		$mock->_setMethod('server')
			->_setArgs('SERVER_NAME')
			->_setReturn('localhost')
			->e();

		$this->testController = Mock::m('Test\TestController');
		$this->testController->request = $mock;
	}

	/**
	 * _initialize
	 */
	public function _initializeTest()
	{
		$testController = new TestController();
		$extendProtocol = new ExtendProtocol();
		$request        = new Request();
		$testController->_initialize($this->container, $extendProtocol, $request);
		$reflection = new \ReflectionClass($testController);
		$property = $reflection->getProperty('container');
		$property->setAccessible(true);

		$this->compareValue($this->container, $property->getValue($testController), 'Container');

		$property = $reflection->getProperty('request');
		$property->setAccessible(true);

		$this->compareValue($request, $property->getValue($testController), 'Request');
	}

	/**
	 * _doMethod
	 */
	public function _doMethodTest()
	{
		$method = 'testAction';

		$testController = Mock::m('Test\TestController');
		$testController->_setMethod('_checkMethodExist')
			->_setArgs($testController, $method)
			->_setReturn(null)
			->e();

		$testController->request = Mock::m('System\Core\Route\Request')
			->_setMethod('getControllerMethod')
			->_setArgs()
			->_setReturn($method)
			->e();

		$this->compareValue(null, $testController->_doMethod());
		$this->compareValue('success', $testController->result, 'メソッド実行');
		$this->compareValue('testAction', $testController->_useMethod, '使用メソッド取得');
	}

	/**
	 * _checkMethodExist
	 */
	public function _checkMethodExistTest()
	{
		$class  = 'Test\TestController';
		$method = new \ReflectionMethod($class, '_checkMethodExist');
		$method->setAccessible(true);
		$this->compareValue(null, $method->invoke(new $class(), $class, 'testAction'));

		try {
			$method->invoke(new $class(), new $class(), 'notExistFunction');
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (ControllerException $e) {
			$this->compareException('存在しないメソッドを参照した', $e, '存在しないアクションを指定した場合');
		}
	}

	/**
	 * render
	 */
	public function renderTest()
	{
		$this->common();
		$fileExistController = Mock::m('Test\FileExistController');

		$templateName = 'hoge';
		$fileExistController->render($templateName);
		$this->compareValue(
			sprintf('%s%s.%s', TEMPLATE_DIR, $templateName, TEMPLATE_EXTENSION),
			$fileExistController->_useTemplate
		);

		$fileExistController->useJsonResponse();

		try {
			$fileExistController->render('hoge');
		} catch (ControllerException $e) {
			$this->compareException('JsonResponseを使用する場合にビューは指定できない', $e, 'JsonResponse時にビューを指定した');
			return;
		}
		$this->throwError('例外が発生すべき箇所で発生していない');
	}

	/**
	 * _existTemplate
	 */
	public function _existTemplateTest()
	{
		$this->common();
		$this->compareValue(null, $this->testController->_existTemplate(str_replace('extend://', '', __FILE__)), '存在するファイルを指定した場合');

		try {
			$this->testController->_existTemplate('hoge');
		} catch (ControllerException $e) {
			$this->compareException('存在しないテンプレートを指定した', $e, '存在しないファイルを指定した場合');
			return;
		}
		$this->throwError('例外が発生すべき箇所で発生していない');
	}

	/**
	 * _includeTemplate
	 */
	public function _includeTemplateTest()
	{
		$this->common();
		$this->testController->_useClassForView = ['Hoge', 'Test\Hoge'];
		$this->testController->_variableList    = ['aaa', 1];

		$this->testController->extendProtocol = new ExtendProtocol();
		$this->compareValue(null, $this->testController->_includeTemplate(__FILE__), '存在するファイルを指定した場合');

		set_error_handler(function($severity, $message, $file, $line) {
			throw new \Exception('存在しないテンプレートをインクルードしようとした');
		});
		try {
			$this->testController->_includeTemplate('hoge');
		} catch (\Exception $e) {
			$this->compareException('存在しないテンプレートをインクルードしようとした', $e, '存在しないファイルを指定した場合');
			restore_error_handler();
			return;
		}
		restore_error_handler();
		$this->throwError('例外が発生すべき箇所で発生していない');
	}

	/**
	 * redirect
	 */
	public function redirectTest()
	{
		$this->common();
		$this->compareValue(null, $this->testController->redirect());
	}

	/**
	 * bafore
	 */
	public function beforeTest()
	{
		//省略
	}

	/**
	 * after
	 */
	public function afterTest()
	{
		//省略
	}

	/**
	 * useJsonResponse
	 */
	public function useJsonResponseTest()
	{
		$this->common();
		$this->testController->useJsonResponse();
		$this->compareValue(true, $this->testController->_useJsonResponse);
	}

	/**
	 * _checkNeedTemplate
	 */
	public function _checkNeedTemplateTest()
	{
		$this->common();
		$this->testController->useJsonResponse();
		$this->testController->_useTemplate = 'hoge';
		$this->compareValue(null, $this->testController->_checkNeedTemplate(), 'テンプレートが必要な場合');

		$this->testController->_useTemplate     = null;
		$this->testController->_useJsonResponse = false;
		try {
			$this->testController->_checkNeedTemplate();
			$this->throwError('例外が発生すべき箇所で発生していない');
		} catch (ControllerException $e) {
			$this->compareException('ビューが指定されていない', $e, 'テンプレートが不要な場合');
		}
	}

	/**
	 * _responseJsonWhenUseJsonResponse
	 */
	public function _responseJsonWhenUseJsonResponseTest()
	{
		$this->common();
		$this->compareValue(null, $this->testController->_responseJsonWhenUseJsonResponse(), 'JsonResponseが不要な場合');
		//省略
	}
}

namespace Test;

use System\Core\Route\BaseController;

class FileExistController extends BaseController
{
	protected function _existTemplate($template)
	{
		return true;
	}

	protected function _includeTemplate($template)
	{
		return true;
	}
}

namespace Test;

use System\Core\Route\BaseController;

class TestController extends BaseController
{
	private $result;

	public function testAction()
	{
		$this->result = 'success';
	}

	public function testRedirect($to = '/')
	{
		$this->redirect($to);
	}
}

namespace System\Core\Route;

function header()
{
	return null;
}
